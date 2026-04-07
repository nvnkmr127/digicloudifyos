<?php

namespace App\Jobs\Integrations;

use App\Models\ClientChannelConnection;
use App\Models\GoogleSearchConsoleDailyMetric;
use App\Models\IntegrationSyncRun;
use App\Services\Integrations\GoogleTokenService;
use App\Services\Integrations\IntegrationAlertService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SyncSearchConsoleDailyMetrics implements ShouldQueue
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

    public function handle(GoogleTokenService $tokenService, IntegrationAlertService $alertService): void
    {
        $date = $this->date ?? now()->subDay()->toDateString();

        $connection = ClientChannelConnection::where('organization_id', $this->organizationId)
            ->where('client_id', $this->clientId)
            ->where('channel_type', 'google_search_console')
            ->active()
            ->with('credential')
            ->first();

        if (! $connection || ! $connection->credential) {
            return;
        }

        $run = IntegrationSyncRun::updateOrCreate(
            [
                'organization_id' => $this->organizationId,
                'client_id' => $this->clientId,
                'channel_type' => 'google_search_console',
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
            $accessToken = $tokenService->getValidAccessToken($connection->credential);
            $siteUrl = $this->resolveSiteUrl($connection, $accessToken);

            if (! $siteUrl) {
                throw new \RuntimeException('No Search Console site available.');
            }

            $query = Http::withToken($accessToken)
                ->timeout(30)
                ->retry(2, 200)
                ->post('https://www.googleapis.com/webmasters/v3/sites/'.rawurlencode($siteUrl).'/searchAnalytics/query', [
                'startDate' => $date,
                'endDate' => $date,
            ]);

            if ($query->failed()) {
                throw new \RuntimeException('Search Console query failed.');
            }

            $rows = $query->json()['rows'] ?? [];

            $clicks = 0;
            $impressions = 0;
            $positionWeighted = 0.0;

            foreach ($rows as $row) {
                $c = (int) ($row['clicks'] ?? 0);
                $i = (int) ($row['impressions'] ?? 0);
                $p = (float) ($row['position'] ?? 0);
                $clicks += $c;
                $impressions += $i;
                $positionWeighted += $p * $i;
            }

            $ctr = $impressions > 0 ? ($clicks / $impressions) : null;
            $avgPosition = $impressions > 0 ? ($positionWeighted / $impressions) : null;

            GoogleSearchConsoleDailyMetric::updateOrCreate(
                [
                    'organization_id' => $this->organizationId,
                    'client_id' => $this->clientId,
                    'metric_date' => $date,
                    'site_url' => $siteUrl,
                ],
                [
                    'client_channel_connection_id' => $connection->id,
                    'clicks' => $clicks,
                    'impressions' => $impressions,
                    'ctr' => $ctr,
                    'avg_position' => $avgPosition,
                    'raw_data' => [
                        'rows' => $rows,
                    ],
                ]
            );

            $connection->update([
                'last_synced_at' => now(),
                'last_sync_status' => 'success',
            ]);

            $run->update([
                'status' => 'success',
                'finished_at' => now(),
                'error_message' => null,
                'metrics' => [
                    'clicks' => $clicks,
                    'impressions' => $impressions,
                    'ctr' => $ctr,
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
                    'google_search_console',
                    $date,
                    $e->getMessage()
                );
            }

            throw $e;
        }
    }

    protected function resolveSiteUrl(ClientChannelConnection $connection, string $accessToken): ?string
    {
        if (is_string($connection->account_id) && $connection->account_id !== '') {
            return $connection->account_id;
        }

        $response = Http::withToken($accessToken)
            ->timeout(30)
            ->retry(2, 200)
            ->get('https://www.googleapis.com/webmasters/v3/sites');
        if ($response->failed()) {
            return null;
        }

        $sites = $response->json()['siteEntry'] ?? [];
        $first = $sites[0] ?? null;
        if (! is_array($first)) {
            return null;
        }

        $siteUrl = $first['siteUrl'] ?? null;
        if (! is_string($siteUrl) || $siteUrl === '') {
            return null;
        }

        $metadata = $connection->metadata ?? [];
        $metadata['site'] = $first;

        $connection->update([
            'account_id' => $siteUrl,
            'account_name' => $siteUrl,
            'metadata' => $metadata,
        ]);

        return $siteUrl;
    }
}
