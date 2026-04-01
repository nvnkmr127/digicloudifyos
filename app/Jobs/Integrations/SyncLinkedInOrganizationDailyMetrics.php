<?php

namespace App\Jobs\Integrations;

use App\Models\ClientChannelConnection;
use App\Models\IntegrationSyncRun;
use App\Models\LinkedInOrganizationDailyMetric;
use App\Services\Integrations\IntegrationAlertService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SyncLinkedInOrganizationDailyMetrics implements ShouldQueue
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
            ->where('channel_type', 'linkedin_organic')
            ->active()
            ->with('credential')
            ->first();

        if (! $connection || ! $connection->credential || ! $connection->credential->access_token) {
            return;
        }

        $orgUrn = $connection->account_id ?: ($connection->credential->payload['organization_urn'] ?? null);
        if (! is_string($orgUrn) || $orgUrn === '') {
            return;
        }

        $run = IntegrationSyncRun::updateOrCreate(
            [
                'organization_id' => $this->organizationId,
                'client_id' => $this->clientId,
                'channel_type' => 'linkedin_organic',
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
            $accessToken = $connection->credential->access_token;

            $d = Carbon::parse($date);
            $dayStartMs = $d->startOfDay()->timestamp * 1000;
            $dayEndMs = $d->endOfDay()->timestamp * 1000;

            $followersResponse = Http::withToken($accessToken)->get('https://api.linkedin.com/v2/organizationalEntityFollowerStatistics', [
                'q' => 'organizationalEntity',
                'organizationalEntity' => $orgUrn,
                'timeIntervals.timeGranularityType' => 'DAY',
                'timeIntervals.timeRange.start' => $dayStartMs,
                'timeIntervals.timeRange.end' => $dayEndMs,
            ]);

            $shareStatsResponse = Http::withToken($accessToken)->get('https://api.linkedin.com/v2/organizationalEntityShareStatistics', [
                'q' => 'organizationalEntity',
                'organizationalEntity' => $orgUrn,
                'timeIntervals.timeGranularityType' => 'DAY',
                'timeIntervals.timeRange.start' => $dayStartMs,
                'timeIntervals.timeRange.end' => $dayEndMs,
            ]);

            if ($followersResponse->failed() || $shareStatsResponse->failed()) {
                throw new \RuntimeException('LinkedIn organization stats failed.');
            }

            $followersData = $followersResponse->json();
            $followers = (int) (($followersData['elements'][0]['followerCounts']['organicFollowerCount'] ?? 0));

            $shareData = $shareStatsResponse->json();
            $totals = $shareData['elements'][0]['totalShareStatistics'] ?? [];

            $impressions = (int) ($totals['impressionCount'] ?? 0);
            $clicks = (int) ($totals['clickCount'] ?? 0);
            $likes = (int) ($totals['likeCount'] ?? 0);
            $comments = (int) ($totals['commentCount'] ?? 0);
            $shares = (int) ($totals['shareCount'] ?? 0);

            LinkedInOrganizationDailyMetric::updateOrCreate(
                [
                    'organization_id' => $this->organizationId,
                    'client_id' => $this->clientId,
                    'metric_date' => $date,
                    'linkedin_organization_urn' => $orgUrn,
                ],
                [
                    'client_channel_connection_id' => $connection->id,
                    'followers' => $followers,
                    'impressions' => $impressions,
                    'clicks' => $clicks,
                    'likes' => $likes,
                    'comments' => $comments,
                    'shares' => $shares,
                    'raw_data' => [
                        'followers' => $followersData,
                        'share_stats' => $shareData,
                    ],
                ]
            );

            $connection->update([
                'last_synced_at' => now(),
                'last_sync_status' => 'success',
                'account_id' => $orgUrn,
                'account_name' => $orgUrn,
            ]);

            $run->update([
                'status' => 'success',
                'finished_at' => now(),
                'error_message' => null,
                'metrics' => [
                    'followers' => $followers,
                    'impressions' => $impressions,
                    'clicks' => $clicks,
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
                    'linkedin_organic',
                    $date,
                    $e->getMessage()
                );
            }

            throw $e;
        }
    }
}

