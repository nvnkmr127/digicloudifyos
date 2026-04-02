<?php

namespace App\Jobs\Integrations;

use App\Models\ClientChannelConnection;
use App\Models\IntegrationSyncRun;
use App\Models\ShopifyDailyMetric;
use App\Services\Integrations\IntegrationAlertService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SyncShopifyDailyMetrics implements ShouldQueue
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
            ->where('channel_type', 'shopify')
            ->active()
            ->with('credential')
            ->first();

        if (! $connection || ! $connection->credential || ! $connection->credential->access_token) {
            return;
        }

        $shop = $connection->credential->payload['shop'] ?? $connection->account_id;
        if (! is_string($shop) || $shop === '') {
            return;
        }

        $run = IntegrationSyncRun::updateOrCreate(
            [
                'organization_id' => $this->organizationId,
                'client_id' => $this->clientId,
                'channel_type' => 'shopify',
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
            $start = Carbon::parse($date)->startOfDay()->toIso8601String();
            $end = Carbon::parse($date)->endOfDay()->toIso8601String();

            $headers = [
                'X-Shopify-Access-Token' => $connection->credential->access_token,
                'Accept' => 'application/json',
            ];

            $countResponse = Http::withHeaders($headers)->get("https://{$shop}/admin/api/2024-01/orders/count.json", [
                'status' => 'any',
                'created_at_min' => $start,
                'created_at_max' => $end,
            ]);

            if ($countResponse->failed()) {
                throw new \RuntimeException('Shopify orders count failed.');
            }

            $ordersCount = (int) ($countResponse->json()['count'] ?? 0);

            $ordersResponse = Http::withHeaders($headers)->get("https://{$shop}/admin/api/2024-01/orders.json", [
                'status' => 'any',
                'created_at_min' => $start,
                'created_at_max' => $end,
                'limit' => 250,
                'fields' => 'id,total_price,currency,customer',
            ]);

            if ($ordersResponse->failed()) {
                throw new \RuntimeException('Shopify orders list failed.');
            }

            $orders = $ordersResponse->json()['orders'] ?? [];

            $grossSales = 0.0;
            $customers = [];
            $currency = null;

            foreach ($orders as $order) {
                $grossSales += (float) ($order['total_price'] ?? 0);
                $currency = $currency ?? ($order['currency'] ?? null);
                $customerId = $order['customer']['id'] ?? null;
                if ($customerId) {
                    $customers[(string) $customerId] = true;
                }
            }

            $customersCount = count($customers);

            ShopifyDailyMetric::updateOrCreate(
                [
                    'organization_id' => $this->organizationId,
                    'client_id' => $this->clientId,
                    'metric_date' => $date,
                    'shop_domain' => $shop,
                ],
                [
                    'client_channel_connection_id' => $connection->id,
                    'currency_code' => is_string($currency) ? $currency : null,
                    'orders_count' => $ordersCount,
                    'customers_count' => $customersCount,
                    'gross_sales' => $grossSales,
                    'net_sales' => $grossSales,
                    'refunds' => 0,
                    'raw_data' => [
                        'orders_loaded' => is_array($orders) ? count($orders) : 0,
                    ],
                ]
            );

            $connection->update([
                'last_synced_at' => now(),
                'last_sync_status' => 'success',
                'account_id' => $shop,
                'account_name' => $shop,
            ]);

            $run->update([
                'status' => 'success',
                'finished_at' => now(),
                'error_message' => null,
                'metrics' => [
                    'orders_count' => $ordersCount,
                    'gross_sales' => $grossSales,
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
                    'shopify',
                    $date,
                    $e->getMessage()
                );
            }

            throw $e;
        }
    }
}
