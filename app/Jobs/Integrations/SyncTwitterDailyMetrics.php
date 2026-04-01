<?php

namespace App\Jobs\Integrations;

use App\Models\ClientChannelConnection;
use App\Models\IntegrationSyncRun;
use App\Models\TwitterDailyMetric;
use App\Services\Integrations\IntegrationAlertService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SyncTwitterDailyMetrics implements ShouldQueue
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
            ->where('channel_type', 'twitter')
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
                'channel_type' => 'twitter',
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

            $me = Http::withToken($accessToken)->get('https://api.twitter.com/2/users/me', [
                'user.fields' => 'username,public_metrics',
            ]);

            if ($me->failed()) {
                throw new \RuntimeException('Twitter user lookup failed.');
            }

            $data = $me->json()['data'] ?? [];
            $userId = isset($data['id']) ? (string) $data['id'] : null;
            $username = isset($data['username']) ? (string) $data['username'] : null;
            $metrics = is_array($data['public_metrics'] ?? null) ? $data['public_metrics'] : [];

            $followers = (int) ($metrics['followers_count'] ?? 0);
            $following = (int) ($metrics['following_count'] ?? 0);
            $tweets = (int) ($metrics['tweet_count'] ?? 0);
            $listed = (int) ($metrics['listed_count'] ?? 0);

            TwitterDailyMetric::updateOrCreate(
                [
                    'organization_id' => $this->organizationId,
                    'client_id' => $this->clientId,
                    'metric_date' => $date,
                    'twitter_user_id' => $userId,
                ],
                [
                    'client_channel_connection_id' => $connection->id,
                    'twitter_username' => $username,
                    'followers' => $followers,
                    'following' => $following,
                    'tweets' => $tweets,
                    'listed' => $listed,
                    'raw_data' => [
                        'public_metrics' => $metrics,
                    ],
                ]
            );

            $connection->update([
                'last_synced_at' => now(),
                'last_sync_status' => 'success',
                'account_id' => $userId,
                'account_name' => $username,
                'metadata' => array_merge($connection->metadata ?? [], [
                    'twitter_user_id' => $userId,
                    'username' => $username,
                ]),
            ]);

            $run->update([
                'status' => 'success',
                'finished_at' => now(),
                'error_message' => null,
                'metrics' => [
                    'followers' => $followers,
                    'tweets' => $tweets,
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
                    'twitter',
                    $date,
                    $e->getMessage()
                );
            }

            throw $e;
        }
    }
}

