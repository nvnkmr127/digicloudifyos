<?php

namespace App\Jobs\Integrations;

use App\Models\AmazonSpDailyMetric;
use App\Models\ClientChannelConnection;
use App\Models\IntegrationSyncRun;
use App\Services\Integrations\AmazonLwaTokenService;
use App\Services\Integrations\AmazonSpApiClient;
use App\Services\Integrations\IntegrationAlertService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncAmazonSpDailyMetrics implements ShouldQueue
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

    public function handle(
        AmazonLwaTokenService $tokenService,
        AmazonSpApiClient $client,
        IntegrationAlertService $alertService
    ): void {
        $date = $this->date ?? now()->subDay()->toDateString();

        $connection = ClientChannelConnection::where('organization_id', $this->organizationId)
            ->where('client_id', $this->clientId)
            ->where('channel_type', 'amazon')
            ->active()
            ->with('credential')
            ->first();

        if (! $connection || ! $connection->credential) {
            return;
        }

        $payload = $connection->credential->payload ?? [];
        $accessKeyId = isset($payload['aws_access_key_id']) ? (string) $payload['aws_access_key_id'] : null;
        $secretAccessKey = isset($payload['aws_secret_access_key']) ? (string) $payload['aws_secret_access_key'] : null;
        $region = isset($payload['aws_region']) ? (string) $payload['aws_region'] : config('services.amazon_sp_api.aws_region', 'us-east-1');
        $endpoint = isset($payload['endpoint']) ? (string) $payload['endpoint'] : config('services.amazon_sp_api.endpoint', 'https://sellingpartnerapi-na.amazon.com');
        $marketplaceId = isset($payload['marketplace_id']) ? (string) $payload['marketplace_id'] : config('services.amazon_sp_api.marketplace_id', 'ATVPDKIKX0DER');
        $sellerId = isset($payload['seller_id']) ? (string) $payload['seller_id'] : null;

        if (! $accessKeyId || ! $secretAccessKey || ! $marketplaceId) {
            return;
        }

        $run = IntegrationSyncRun::updateOrCreate(
            [
                'organization_id' => $this->organizationId,
                'client_id' => $this->clientId,
                'channel_type' => 'amazon',
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
            $lwaAccessToken = $tokenService->getValidAccessToken($connection->credential);

            $createdAfter = Carbon::parse($date)->startOfDay()->toIso8601String();
            $createdBefore = Carbon::parse($date)->endOfDay()->toIso8601String();

            $ordersCount = 0;
            $grossSales = 0.0;
            $currency = null;
            $pagesFetched = 0;
            $recordsFetched = 0;
            $truncated = false;

            $nextToken = null;
            $maxPages = 20;

            do {
                $query = [
                    'MarketplaceIds' => $marketplaceId,
                ];

                if ($nextToken) {
                    $query['NextToken'] = $nextToken;
                } else {
                    $query['CreatedAfter'] = $createdAfter;
                    $query['CreatedBefore'] = $createdBefore;
                }

                $resp = $client->get(
                    $endpoint,
                    '/orders/v0/orders',
                    $query,
                    $lwaAccessToken,
                    $accessKeyId,
                    $secretAccessKey,
                    $region
                );

                $payload = $resp['payload'] ?? [];
                $orders = $payload['Orders'] ?? [];
                $nextToken = $payload['NextToken'] ?? null;

                $pagesFetched++;

                if (is_array($orders)) {
                    foreach ($orders as $order) {
                        if (! is_array($order)) {
                            continue;
                        }
                        $ordersCount++;
                        $recordsFetched++;

                        $total = $order['OrderTotal'] ?? null;
                        if (is_array($total)) {
                            $amount = isset($total['Amount']) ? (float) $total['Amount'] : 0.0;
                            $currency = $currency ?? ($total['CurrencyCode'] ?? null);
                            $grossSales += $amount;
                        }
                    }
                }

                if ($pagesFetched >= $maxPages && $nextToken) {
                    $truncated = true;
                    break;
                }
            } while ($nextToken);

            AmazonSpDailyMetric::updateOrCreate(
                [
                    'organization_id' => $this->organizationId,
                    'client_id' => $this->clientId,
                    'metric_date' => $date,
                    'seller_id' => $sellerId,
                    'marketplace_id' => $marketplaceId,
                ],
                [
                    'client_channel_connection_id' => $connection->id,
                    'currency_code' => is_string($currency) ? $currency : null,
                    'orders_count' => $ordersCount,
                    'gross_sales' => $grossSales,
                    'net_sales' => $grossSales,
                    'pages_fetched' => $pagesFetched,
                    'records_fetched' => $recordsFetched,
                    'truncated' => $truncated,
                    'raw_data' => [
                        'endpoint' => $endpoint,
                        'region' => $region,
                        'max_pages' => $maxPages,
                    ],
                ]
            );

            $connection->update([
                'last_synced_at' => now(),
                'last_sync_status' => 'success',
                'account_id' => $sellerId ?: $connection->account_id,
                'account_name' => $sellerId ?: $connection->account_name,
            ]);

            $run->update([
                'status' => 'success',
                'finished_at' => now(),
                'error_message' => null,
                'metrics' => [
                    'orders_count' => $ordersCount,
                    'gross_sales' => $grossSales,
                    'truncated' => $truncated,
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
                    'amazon',
                    $date,
                    $e->getMessage()
                );
            }

            throw $e;
        }
    }
}
