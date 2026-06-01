<?php

namespace App\Jobs\Integrations;

use App\Models\ClientChannelConnection;
use App\Models\IntegrationSyncRun;
use App\Models\MetaPageDailyMetric;
use App\Services\Integrations\IntegrationAlertService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SyncMetaPageDailyMetrics implements ShouldQueue
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
            ->where('channel_type', 'facebook_organic')
            ->active()
            ->with('credential')
            ->first();

        if (! $connection || ! $connection->credential || ! $connection->credential->access_token) {
            return;
        }

        $run = IntegrationSyncRun::updateOrCreate(
            [
                'organization_id' => $this->organizationId,
                'client_id' => $this->clientId,
                'channel_type' => 'facebook_organic',
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
            $pageId = $connection->account_id;
            $pageName = $connection->account_name;

            $accounts = Http::withToken($userToken)
                ->timeout(30)
                ->retry(2, 200)
                ->get('https://graph.facebook.com/v25.0/me/accounts', [
                    'fields' => 'id,name,access_token',
                ]);

            if ($accounts->failed()) {
                throw new \RuntimeException('Meta pages discovery failed.');
            }

            $pages = $accounts->json()['data'] ?? [];
            $selected = null;

            foreach ($pages as $p) {
                if (is_array($p) && isset($p['id']) && (string) $p['id'] === (string) $pageId) {
                    $selected = $p;
                    break;
                }
            }

            $selected = $selected ?: ($pages[0] ?? null);

            if (! is_array($selected) || ! isset($selected['id'], $selected['access_token'])) {
                throw new \RuntimeException('No accessible Meta page found.');
            }

            $pageId = (string) $selected['id'];
            $pageName = isset($selected['name']) ? (string) $selected['name'] : $pageName;
            $pageToken = (string) $selected['access_token'];

            $insights = Http::withToken($pageToken)
                ->timeout(30)
                ->retry(2, 200)
                ->get("https://graph.facebook.com/v25.0/{$pageId}/insights", [
                    'metric' => 'page_impressions,page_impressions_unique,page_engaged_users,page_post_engagements',
                    'since' => $date,
                    'until' => $date,
                    'period' => 'day',
                ]);

            if ($insights->failed()) {
                throw new \RuntimeException('Meta page insights failed.');
            }

            $insightData = $insights->json()['data'] ?? [];
            $byName = [];
            foreach ($insightData as $row) {
                if (is_array($row) && isset($row['name'])) {
                    $byName[(string) $row['name']] = $row;
                }
            }

            $impressions = $this->extractMetricValue($byName['page_impressions'] ?? null);
            $reach = $this->extractMetricValue($byName['page_impressions_unique'] ?? null);
            $engagedUsers = $this->extractMetricValue($byName['page_engaged_users'] ?? null);
            $postEngagements = $this->extractMetricValue($byName['page_post_engagements'] ?? null);

            MetaPageDailyMetric::updateOrCreate(
                [
                    'organization_id' => $this->organizationId,
                    'client_id' => $this->clientId,
                    'metric_date' => $date,
                    'page_id' => $pageId,
                ],
                [
                    'client_channel_connection_id' => $connection->id,
                    'page_name' => $pageName,
                    'impressions' => $impressions,
                    'reach' => $reach,
                    'engaged_users' => $engagedUsers,
                    'post_engagements' => $postEngagements,
                    'raw_data' => [
                        'insights' => $insightData,
                    ],
                ]
            );

            $connection->update([
                'last_synced_at' => now(),
                'last_sync_status' => 'success',
                'account_id' => $pageId,
                'account_name' => $pageName,
            ]);

            $run->update([
                'status' => 'success',
                'finished_at' => now(),
                'error_message' => null,
                'metrics' => [
                    'impressions' => $impressions,
                    'reach' => $reach,
                    'engaged_users' => $engagedUsers,
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
                    'facebook_organic',
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
