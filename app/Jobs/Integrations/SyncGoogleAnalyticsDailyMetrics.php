<?php

namespace App\Jobs\Integrations;

use App\Models\ClientChannelConnection;
use App\Models\GoogleAnalyticsDailyMetric;
use App\Models\IntegrationSyncRun;
use App\Services\Integrations\GoogleTokenService;
use App\Services\Integrations\IntegrationAlertService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SyncGoogleAnalyticsDailyMetrics implements ShouldQueue
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
            ->where('channel_type', 'ga4')
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
                'channel_type' => 'ga4',
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
            $propertyId = $this->resolvePropertyId($connection, $accessToken);

            if (! $propertyId) {
                throw new \RuntimeException('No GA4 property available.');
            }

            $report = Http::withToken($accessToken)
                ->timeout(30)
                ->retry(2, 200)
                ->post("https://analyticsdata.googleapis.com/v1beta/properties/{$propertyId}:runReport", [
                'dateRanges' => [
                    ['startDate' => $date, 'endDate' => $date],
                ],
                'metrics' => [
                    ['name' => 'sessions'],
                    ['name' => 'totalUsers'],
                    ['name' => 'newUsers'],
                    ['name' => 'engagedSessions'],
                    ['name' => 'conversions'],
                    ['name' => 'purchaseRevenue'],
                ],
            ]);

            if ($report->failed()) {
                throw new \RuntimeException('GA4 report failed.');
            }

            $rows = $report->json()['rows'] ?? [];
            $values = $rows[0]['metricValues'] ?? [];

            $sessions = (int) ($values[0]['value'] ?? 0);
            $users = (int) ($values[1]['value'] ?? 0);
            $newUsers = (int) ($values[2]['value'] ?? 0);
            $engagedSessions = (int) ($values[3]['value'] ?? 0);
            $conversions = (int) ($values[4]['value'] ?? 0);
            $revenue = (float) ($values[5]['value'] ?? 0);

            GoogleAnalyticsDailyMetric::updateOrCreate(
                [
                    'organization_id' => $this->organizationId,
                    'client_id' => $this->clientId,
                    'metric_date' => $date,
                    'property_id' => $propertyId,
                ],
                [
                    'client_channel_connection_id' => $connection->id,
                    'sessions' => $sessions,
                    'users' => $users,
                    'new_users' => $newUsers,
                    'engaged_sessions' => $engagedSessions,
                    'conversions' => $conversions,
                    'revenue' => $revenue,
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
                    'sessions' => $sessions,
                    'conversions' => $conversions,
                    'revenue' => $revenue,
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
                    'ga4',
                    $date,
                    $e->getMessage()
                );
            }

            throw $e;
        }
    }

    protected function resolvePropertyId(ClientChannelConnection $connection, string $accessToken): ?string
    {
        if (is_string($connection->account_id) && $connection->account_id !== '') {
            return $connection->account_id;
        }

        $response = Http::withToken($accessToken)
            ->timeout(30)
            ->retry(2, 200)
            ->get('https://analyticsadmin.googleapis.com/v1beta/accountSummaries');
        if ($response->failed()) {
            return null;
        }

        $summaries = $response->json()['accountSummaries'] ?? [];
        $firstProperty = $summaries[0]['propertySummaries'][0] ?? null;
        if (! is_array($firstProperty)) {
            return null;
        }

        $property = $firstProperty['property'] ?? null;
        if (! is_string($property) || $property === '') {
            return null;
        }

        $propertyId = str_replace('properties/', '', $property);
        $metadata = $connection->metadata ?? [];
        $metadata['property'] = $firstProperty;

        $connection->update([
            'account_id' => $propertyId,
            'account_name' => $firstProperty['displayName'] ?? null,
            'metadata' => $metadata,
        ]);

        return $propertyId;
    }
}
