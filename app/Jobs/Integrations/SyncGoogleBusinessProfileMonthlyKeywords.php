<?php

namespace App\Jobs\Integrations;

use App\Models\ClientChannelConnection;
use App\Models\GoogleBusinessProfileMonthlyKeyword;
use App\Models\IntegrationSyncRun;
use App\Services\Integrations\GoogleTokenService;
use App\Services\Integrations\IntegrationAlertService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SyncGoogleBusinessProfileMonthlyKeywords implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(
        public string $organizationId,
        public string $clientId,
        public ?string $monthStart = null
    ) {
        $this->onQueue('intelligence');
    }

    public function handle(GoogleTokenService $tokenService, IntegrationAlertService $alertService): void
    {
        $monthStart = $this->monthStart
            ? Carbon::parse($this->monthStart)->startOfMonth()->toDateString()
            : now()->subMonth()->startOfMonth()->toDateString();

        $connection = ClientChannelConnection::where('organization_id', $this->organizationId)
            ->where('client_id', $this->clientId)
            ->where('channel_type', 'google_business_profile')
            ->active()
            ->with('credential')
            ->first();

        if (! $connection || ! $connection->credential) {
            return;
        }

        $locationName = $connection->account_id;
        if (! is_string($locationName) || $locationName === '') {
            return;
        }

        $run = IntegrationSyncRun::updateOrCreate(
            [
                'organization_id' => $this->organizationId,
                'client_id' => $this->clientId,
                'channel_type' => 'google_business_profile_keywords',
                'run_date' => $monthStart,
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

            $start = Carbon::parse($monthStart);
            $end = $start->copy()->endOfMonth();

            $resp = Http::withToken($accessToken)->get(
                'https://businessprofileperformance.googleapis.com/v1/'.$locationName.'/searchkeywords/impressions/monthly',
                [
                    'monthlyRange.start_month.year' => $start->year,
                    'monthlyRange.start_month.month' => $start->month,
                    'monthlyRange.end_month.year' => $end->year,
                    'monthlyRange.end_month.month' => $end->month,
                    'pageSize' => 1000,
                ]
            );

            if ($resp->failed()) {
                throw new \RuntimeException('GBP monthly keywords fetch failed.');
            }

            $json = $resp->json();
            $rows = $json['searchKeywordsCounts'] ?? [];
            $count = 0;

            if (is_array($rows)) {
                foreach ($rows as $row) {
                    if (! is_array($row)) {
                        continue;
                    }
                    $keyword = $row['searchKeyword'] ?? null;
                    $impressions = $row['insightsValue'] ?? null;
                    if (! is_string($keyword) || $keyword === '') {
                        continue;
                    }

                    $impr = 0;
                    if (is_array($impressions) && isset($impressions['intValue'])) {
                        $impr = (int) $impressions['intValue'];
                    } elseif (is_numeric($impressions)) {
                        $impr = (int) $impressions;
                    }

                    GoogleBusinessProfileMonthlyKeyword::updateOrCreate(
                        [
                            'organization_id' => $this->organizationId,
                            'client_id' => $this->clientId,
                            'month_start' => $monthStart,
                            'keyword' => $keyword,
                        ],
                        [
                            'impressions' => $impr,
                            'raw_data' => $row,
                        ]
                    );

                    $count++;
                }
            }

            $run->update([
                'status' => 'success',
                'finished_at' => now(),
                'error_message' => null,
                'metrics' => ['keywords' => $count],
            ]);
        } catch (\Throwable $e) {
            $run->update([
                'status' => 'failed',
                'finished_at' => now(),
                'error_message' => $e->getMessage(),
                'next_retry_at' => now()->addMinutes(30),
            ]);

            if ($this->attempts() >= $this->tries) {
                $alertService->notifySyncFailure(
                    $this->organizationId,
                    $this->clientId,
                    'google_business_profile_keywords',
                    $monthStart,
                    $e->getMessage()
                );
            }

            throw $e;
        }
    }
}
