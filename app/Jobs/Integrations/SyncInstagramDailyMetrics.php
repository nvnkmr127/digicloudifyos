<?php

namespace App\Jobs\Integrations;

use App\Models\ClientChannelConnection;
use App\Models\InstagramDailyMetric;
use App\Models\IntegrationSyncRun;
use App\Services\Integrations\IntegrationAlertService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SyncInstagramDailyMetrics implements ShouldQueue
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
            ->where('channel_type', 'instagram')
            ->active()
            ->with('credential')
            ->first();

        if (! $connection || ! $connection->credential || ! $connection->credential->access_token) {
            return;
        }

        $igAccountId = $connection->account_id;
        if (! is_string($igAccountId) || $igAccountId === '') {
            return;
        }

        $run = IntegrationSyncRun::updateOrCreate(
            [
                'organization_id' => $this->organizationId,
                'client_id' => $this->clientId,
                'channel_type' => 'instagram',
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
            $userToken = $connection->credential->access_token;

            $insights = Http::withToken($userToken)->get("https://graph.facebook.com/v25.0/{$igAccountId}/insights", [
                'metric' => 'impressions,reach,profile_views,website_clicks',
                'period' => 'day',
                'since' => $date,
                'until' => $date,
            ]);

            if ($insights->failed()) {
                throw new \RuntimeException('Instagram insights failed.');
            }

            $insightData = $insights->json()['data'] ?? [];
            $byName = [];
            foreach ($insightData as $row) {
                if (is_array($row) && isset($row['name'])) {
                    $byName[(string) $row['name']] = $row;
                }
            }

            $impressions = $this->extractMetricValue($byName['impressions'] ?? null);
            $reach = $this->extractMetricValue($byName['reach'] ?? null);
            $profileViews = $this->extractMetricValue($byName['profile_views'] ?? null);
            $websiteClicks = $this->extractMetricValue($byName['website_clicks'] ?? null);

            InstagramDailyMetric::updateOrCreate(
                [
                    'organization_id' => $this->organizationId,
                    'client_id' => $this->clientId,
                    'metric_date' => $date,
                    'instagram_account_id' => $igAccountId,
                ],
                [
                    'client_channel_connection_id' => $connection->id,
                    'impressions' => $impressions,
                    'reach' => $reach,
                    'profile_views' => $profileViews,
                    'website_clicks' => $websiteClicks,
                    'raw_data' => [
                        'insights' => $insightData,
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
                    'impressions' => $impressions,
                    'reach' => $reach,
                    'profile_views' => $profileViews,
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
                    'instagram',
                    $date,
                    $e->getMessage()
                );
            }

            throw $e;
        }
    }

    protected function extractMetricValue(?array $metricRow): int
    {
        if (! $metricRow) {
            return 0;
        }
        $values = $metricRow['values'] ?? [];
        $value = $values[0]['value'] ?? null;
        if (is_array($value)) {
            return (int) array_sum(array_map(fn ($v) => (int) $v, $value));
        }

        return (int) $value;
    }
}
