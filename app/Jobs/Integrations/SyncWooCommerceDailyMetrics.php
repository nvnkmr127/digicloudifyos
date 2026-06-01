<?php

namespace App\Jobs\Integrations;

use App\Models\ClientChannelConnection;
use App\Models\IntegrationSyncRun;
use App\Models\WooCommerceDailyMetric;
use App\Services\Integrations\IntegrationAlertService;
use App\Services\UrlEgressPolicy;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SyncWooCommerceDailyMetrics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(
        public string $organizationId,
        public string $clientId,
        public ?string $date = null
    ) {
        $this->onQueue('intelligence');
    }

    public function handle(IntegrationAlertService $alertService): void
    {
        $date = $this->date ?? now()->subDay()->toDateString();

        $connection = ClientChannelConnection::where('organization_id', $this->organizationId)
            ->where('client_id', $this->clientId)
            ->where('channel_type', 'woocommerce')
            ->active()
            ->with('credential')
            ->first();

        if (! $connection || ! $connection->credential) {
            return;
        }

        $payload = $connection->credential->payload ?? [];
        $storeUrl = isset($payload['store_url']) ? (string) $payload['store_url'] : null;
        $consumerKey = isset($payload['consumer_key']) ? (string) $payload['consumer_key'] : null;
        $consumerSecret = isset($payload['consumer_secret']) ? (string) $payload['consumer_secret'] : null;

        if (! $storeUrl || ! $consumerKey || ! $consumerSecret) {
            return;
        }

        $storeUrl = rtrim($storeUrl, '/');
        $storeUrl = app(UrlEgressPolicy::class)->assertAllowed($storeUrl);

        $run = IntegrationSyncRun::updateOrCreate(
            [
                'organization_id' => $this->organizationId,
                'client_id' => $this->clientId,
                'channel_type' => 'woocommerce',
                'run_date' => $date,
            ],
            [
                'client_channel_connection_id' => $connection->id,
                'status' => 'running',
                'attempt' => 0,
                'started_at' => now(),
                'finished_at' => null,
                'next_retry_at' => null,
                'error_message' => null,
            ]
        );

        $run->increment('attempt');

        try {
            $start = Carbon::parse($date)->startOfDay()->toDateString();
            $end = Carbon::parse($date)->endOfDay()->toDateString();

            $report = Http::timeout(30)
                ->retry(2, 200)
                ->withOptions(['allow_redirects' => false])
                ->get("{$storeUrl}/wp-json/wc/v3/reports/sales", [
                    'date_min' => $start,
                    'date_max' => $end,
                    'consumer_key' => $consumerKey,
                    'consumer_secret' => $consumerSecret,
                ]);

            if ($report->failed()) {
                throw new \RuntimeException('WooCommerce sales report failed.');
            }

            $data = $report->json();
            $row = is_array($data) ? ($data[0] ?? []) : [];

            $ordersCount = (int) ($row['total_orders'] ?? 0);
            $grossSales = (float) ($row['total_sales'] ?? 0);
            $netSales = (float) ($row['net_sales'] ?? $grossSales);
            $currency = $row['currency'] ?? config('services.woocommerce.default_currency', 'USD');

            WooCommerceDailyMetric::updateOrCreate(
                [
                    'organization_id' => $this->organizationId,
                    'client_id' => $this->clientId,
                    'metric_date' => $date,
                    'store_url' => $storeUrl,
                ],
                [
                    'client_channel_connection_id' => $connection->id,
                    'currency_code' => is_string($currency) ? $currency : null,
                    'orders_count' => $ordersCount,
                    'gross_sales' => $grossSales,
                    'net_sales' => $netSales,
                    'refunds' => 0,
                    'raw_data' => [
                        'report' => $row,
                    ],
                ]
            );

            $connection->update([
                'last_synced_at' => now(),
                'last_sync_status' => 'success',
                'account_id' => $storeUrl,
                'account_name' => $storeUrl,
                'metadata' => array_merge($connection->metadata ?? [], [
                    'store_url' => $storeUrl,
                ]),
            ]);

            $run->update([
                'status' => 'success',
                'finished_at' => now(),
                'error_message' => null,
                'metrics' => [
                    'orders_count' => $ordersCount,
                    'net_sales' => $netSales,
                ],
            ]);
        } catch (\Throwable $e) {
            $connection->update([
                'last_sync_status' => 'failed',
            ]);

            $run->update([
                'status' => 'failed',
                'finished_at' => now(),
                'error_message' => $e->getMessage(),
                'next_retry_at' => now()->addMinutes(15),
            ]);

            if ($this->attempts() >= $this->tries) {
                $alertService->notifySyncFailure(
                    $this->organizationId,
                    $this->clientId,
                    'woocommerce',
                    $date,
                    $e->getMessage()
                );
            }

            throw $e;
        }
    }
}
