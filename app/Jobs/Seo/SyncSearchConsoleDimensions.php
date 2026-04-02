<?php

namespace App\Jobs\Seo;

use App\Models\ClientChannelConnection;
use App\Models\IntegrationSyncRun;
use App\Models\SearchConsoleDimensionRow;
use App\Services\Integrations\GoogleTokenService;
use App\Services\Integrations\IntegrationAlertService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SyncSearchConsoleDimensions implements ShouldQueue
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
                'channel_type' => 'google_search_console_dimensions',
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
            $siteUrl = is_string($connection->account_id) && $connection->account_id !== '' ? $connection->account_id : null;

            if (! $siteUrl) {
                $sites = Http::withToken($accessToken)->get('https://www.googleapis.com/webmasters/v3/sites');
                if ($sites->failed()) {
                    throw new \RuntimeException('Search Console site discovery failed.');
                }
                $first = ($sites->json()['siteEntry'] ?? [])[0] ?? null;
                $siteUrl = is_array($first) ? ($first['siteUrl'] ?? null) : null;
                if (! is_string($siteUrl) || $siteUrl === '') {
                    throw new \RuntimeException('No Search Console site available.');
                }
            }

            $rowsQuery = $this->fetchRows($accessToken, $siteUrl, $date, ['query']);
            $rowsPage = $this->fetchRows($accessToken, $siteUrl, $date, ['page']);

            $upserts = 0;
            $upserts += $this->persistRows($date, $siteUrl, 'query', $rowsQuery);
            $upserts += $this->persistRows($date, $siteUrl, 'page', $rowsPage);

            $connection->update([
                'last_synced_at' => now(),
                'last_sync_status' => 'success',
            ]);

            $run->update([
                'status' => 'success',
                'finished_at' => now(),
                'error_message' => null,
                'metrics' => [
                    'rows' => $upserts,
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
                    'google_search_console_dimensions',
                    $date,
                    $e->getMessage()
                );
            }

            throw $e;
        }
    }

    protected function fetchRows(string $token, string $siteUrl, string $date, array $dimensions): array
    {
        $resp = Http::withToken($token)->post(
            'https://www.googleapis.com/webmasters/v3/sites/'.rawurlencode($siteUrl).'/searchAnalytics/query',
            [
                'startDate' => $date,
                'endDate' => $date,
                'dimensions' => $dimensions,
                'rowLimit' => 250,
            ]
        );

        if ($resp->failed()) {
            throw new \RuntimeException('Search Console dimension query failed.');
        }

        $rows = $resp->json()['rows'] ?? [];

        return is_array($rows) ? $rows : [];
    }

    protected function persistRows(string $date, string $siteUrl, string $dimension, array $rows): int
    {
        $count = 0;
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $key = ($row['keys'] ?? [])[0] ?? null;
            if (! is_string($key) || $key === '') {
                continue;
            }

            $clicks = (int) ($row['clicks'] ?? 0);
            $impressions = (int) ($row['impressions'] ?? 0);
            $ctr = isset($row['ctr']) ? (float) $row['ctr'] : null;
            $position = isset($row['position']) ? (float) $row['position'] : null;

            SearchConsoleDimensionRow::updateOrCreate(
                [
                    'organization_id' => $this->organizationId,
                    'client_id' => $this->clientId,
                    'metric_date' => $date,
                    'dimension' => $dimension,
                    'key' => $key,
                ],
                [
                    'site_url' => $siteUrl,
                    'clicks' => $clicks,
                    'impressions' => $impressions,
                    'ctr' => $ctr,
                    'avg_position' => $position,
                    'raw_data' => $row,
                ]
            );

            $count++;
        }

        return $count;
    }
}
