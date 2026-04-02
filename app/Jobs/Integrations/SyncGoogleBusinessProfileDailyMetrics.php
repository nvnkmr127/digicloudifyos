<?php

namespace App\Jobs\Integrations;

use App\Models\ClientChannelConnection;
use App\Models\GoogleBusinessProfileDailyMetric;
use App\Models\IntegrationSyncRun;
use App\Services\Integrations\GoogleTokenService;
use App\Services\Integrations\IntegrationAlertService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SyncGoogleBusinessProfileDailyMetrics implements ShouldQueue
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
                'channel_type' => 'google_business_profile',
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

            $metrics = [
                'WEBSITE_CLICKS',
                'CALL_CLICKS',
                'DIRECTIONS_REQUESTS',
                'BUSINESS_IMPRESSIONS_DESKTOP_SEARCH',
                'BUSINESS_IMPRESSIONS_MOBILE_SEARCH',
                'BUSINESS_IMPRESSIONS_DESKTOP_MAPS',
                'BUSINESS_IMPRESSIONS_MOBILE_MAPS',
            ];

            $resp = Http::withToken($accessToken)->get(
                'https://businessprofileperformance.googleapis.com/v1/'.$locationName.':fetchMultiDailyMetricsTimeSeries',
                array_merge(
                    $this->dailyRangeParams($date, $date),
                    collect($metrics)->mapWithKeys(fn ($m, $i) => ["dailyMetrics[{$i}]" => $m])->all()
                )
            );

            if ($resp->failed()) {
                throw new \RuntimeException('GBP performance fetch failed.');
            }

            $json = $resp->json();
            $series = $json['multiDailyMetricTimeSeries'] ?? [];
            $values = $this->extractDailyValues(is_array($series) ? $series : [], $date);

            GoogleBusinessProfileDailyMetric::updateOrCreate(
                [
                    'organization_id' => $this->organizationId,
                    'client_id' => $this->clientId,
                    'metric_date' => $date,
                ],
                [
                    'website_clicks' => (int) ($values['WEBSITE_CLICKS'] ?? 0),
                    'call_clicks' => (int) ($values['CALL_CLICKS'] ?? 0),
                    'directions_requests' => (int) ($values['DIRECTIONS_REQUESTS'] ?? 0),
                    'impressions_search_desktop' => (int) ($values['BUSINESS_IMPRESSIONS_DESKTOP_SEARCH'] ?? 0),
                    'impressions_search_mobile' => (int) ($values['BUSINESS_IMPRESSIONS_MOBILE_SEARCH'] ?? 0),
                    'impressions_maps_desktop' => (int) ($values['BUSINESS_IMPRESSIONS_DESKTOP_MAPS'] ?? 0),
                    'impressions_maps_mobile' => (int) ($values['BUSINESS_IMPRESSIONS_MOBILE_MAPS'] ?? 0),
                    'raw_data' => $json,
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
                    'website_clicks' => (int) ($values['WEBSITE_CLICKS'] ?? 0),
                    'call_clicks' => (int) ($values['CALL_CLICKS'] ?? 0),
                    'directions_requests' => (int) ($values['DIRECTIONS_REQUESTS'] ?? 0),
                ],
            ]);
        } catch (\Throwable $e) {
            $connection->update(['last_sync_status' => 'failed']);

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
                    'google_business_profile',
                    $date,
                    $e->getMessage()
                );
            }

            throw $e;
        }
    }

    protected function dailyRangeParams(string $start, string $end): array
    {
        $s = date_parse($start);
        $e = date_parse($end);

        return [
            'dailyRange.start_date.year' => $s['year'] ?? null,
            'dailyRange.start_date.month' => $s['month'] ?? null,
            'dailyRange.start_date.day' => $s['day'] ?? null,
            'dailyRange.end_date.year' => $e['year'] ?? null,
            'dailyRange.end_date.month' => $e['month'] ?? null,
            'dailyRange.end_date.day' => $e['day'] ?? null,
        ];
    }

    protected function extractDailyValues(array $series, string $date): array
    {
        $result = [];

        foreach ($series as $row) {
            if (! is_array($row)) {
                continue;
            }
            $metric = $row['dailyMetric'] ?? null;
            if (! is_string($metric) || $metric === '') {
                continue;
            }

            $ts = $row['dailyMetricTimeSeries'] ?? null;
            if (! is_array($ts)) {
                continue;
            }
            $timeSeries = $ts['timeSeries'] ?? null;
            if (! is_array($timeSeries)) {
                continue;
            }
            $dated = $timeSeries['datedValues'] ?? null;
            if (! is_array($dated)) {
                continue;
            }

            $valueForDate = 0;
            foreach ($dated as $dv) {
                if (! is_array($dv)) {
                    continue;
                }
                $d = $dv['date'] ?? null;
                $v = $dv['value'] ?? null;
                if (! is_array($d)) {
                    continue;
                }
                $yyyy = $d['year'] ?? null;
                $mm = $d['month'] ?? null;
                $dd = $d['day'] ?? null;
                if (! is_int($yyyy) || ! is_int($mm) || ! is_int($dd)) {
                    continue;
                }
                $iso = sprintf('%04d-%02d-%02d', $yyyy, $mm, $dd);
                if ($iso !== $date) {
                    continue;
                }
                if (is_array($v) && isset($v['intValue'])) {
                    $valueForDate = (int) $v['intValue'];
                } elseif (is_numeric($v)) {
                    $valueForDate = (int) $v;
                }
                break;
            }

            $result[$metric] = $valueForDate;
        }

        return $result;
    }
}
