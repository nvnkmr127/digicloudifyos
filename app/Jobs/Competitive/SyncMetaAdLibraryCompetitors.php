<?php

namespace App\Jobs\Competitive;

use App\Models\Client;
use App\Models\ClientCompetitor;
use App\Models\IntegrationSyncRun;
use App\Models\MetaAdLibraryAd;
use App\Models\MetaAdLibraryDailySummary;
use App\Services\Integrations\IntegrationAlertService;
use App\Services\Integrations\MetaAdLibraryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncMetaAdLibraryCompetitors implements ShouldQueue
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

    public function handle(MetaAdLibraryService $service, IntegrationAlertService $alertService): void
    {
        $date = $this->date ?? now()->subDay()->toDateString();

        $client = Client::where('organization_id', $this->organizationId)->find($this->clientId);
        if (! $client) {
            return;
        }

        $competitors = ClientCompetitor::where('organization_id', $this->organizationId)
            ->where('client_id', $this->clientId)
            ->where('platform', 'meta_page')
            ->where('is_active', true)
            ->get();

        $run = IntegrationSyncRun::updateOrCreate(
            [
                'organization_id' => $this->organizationId,
                'client_id' => $this->clientId,
                'channel_type' => 'meta_ad_library',
                'run_date' => $date,
            ],
            [
                'client_channel_connection_id' => null,
                'status' => 'running',
                'attempt' => 0,
                'started_at' => now(),
                'finished_at' => null,
                'next_retry_at' => null,
                'error_message' => null,
            ]
        );

        $run->increment('attempt');

        $totalActive = 0;
        $totalNew = 0;
        $totalRecords = 0;
        $totalPages = 0;
        $anyTruncated = false;

        try {
            foreach ($competitors as $competitor) {
                $result = $this->syncCompetitor($service, $competitor, $date, $client);
                $totalActive += $result['active_ads_count'];
                $totalNew += $result['new_ads_count'];
                $totalRecords += $result['records_fetched'];
                $totalPages += $result['pages_fetched'];
                $anyTruncated = $anyTruncated || $result['truncated'];
            }

            $run->update([
                'status' => 'success',
                'finished_at' => now(),
                'error_message' => null,
                'metrics' => [
                    'competitors' => $competitors->count(),
                    'active_ads_count' => $totalActive,
                    'new_ads_count' => $totalNew,
                    'records_fetched' => $totalRecords,
                    'pages_fetched' => $totalPages,
                    'truncated' => $anyTruncated,
                ],
            ]);
        } catch (\Throwable $e) {
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
                    'meta_ad_library',
                    $date,
                    $e->getMessage()
                );
            }

            throw $e;
        }
    }

    protected function syncCompetitor(
        MetaAdLibraryService $service,
        ClientCompetitor $competitor,
        string $date,
        Client $client
    ): array {
        $country = $client->country_code ?: 'US';
        $maxPages = 10;
        $pagesFetched = 0;
        $recordsFetched = 0;
        $truncated = false;

        $seenAdIds = [];
        $newAds = 0;

        $after = null;
        do {
            $params = [
                'ad_reached_countries' => [$country],
            ];
            if ($after) {
                $params['after'] = $after;
            }

            $data = $service->fetchAdsForPage($competitor->identifier, $params);
            $ads = $data['data'] ?? [];
            $paging = $data['paging'] ?? [];
            $cursors = is_array($paging) ? ($paging['cursors'] ?? []) : [];
            $after = is_array($cursors) ? ($cursors['after'] ?? null) : null;

            $pagesFetched++;

            if (is_array($ads)) {
                foreach ($ads as $ad) {
                    if (! is_array($ad)) {
                        continue;
                    }
                    $libraryAdId = isset($ad['id']) ? (string) $ad['id'] : null;
                    if (! $libraryAdId) {
                        continue;
                    }

                    $seenAdIds[$libraryAdId] = true;
                    $recordsFetched++;

                    $existing = MetaAdLibraryAd::where('organization_id', $this->organizationId)
                        ->where('client_id', $this->clientId)
                        ->where('client_competitor_id', $competitor->id)
                        ->where('library_ad_id', $libraryAdId)
                        ->first();

                    if (! $existing) {
                        $newAds++;
                    }

                    MetaAdLibraryAd::updateOrCreate(
                        [
                            'organization_id' => $this->organizationId,
                            'client_id' => $this->clientId,
                            'client_competitor_id' => $competitor->id,
                            'library_ad_id' => $libraryAdId,
                        ],
                        [
                            'page_id' => isset($ad['page_id']) ? (string) $ad['page_id'] : null,
                            'page_name' => isset($ad['page_name']) ? (string) $ad['page_name'] : null,
                            'ad_snapshot_url' => isset($ad['ad_snapshot_url']) ? (string) $ad['ad_snapshot_url'] : null,
                            'ad_creation_time' => isset($ad['ad_creation_time']) ? $ad['ad_creation_time'] : null,
                            'ad_delivery_start_time' => isset($ad['ad_delivery_start_time']) ? $ad['ad_delivery_start_time'] : null,
                            'ad_delivery_stop_time' => isset($ad['ad_delivery_stop_time']) ? $ad['ad_delivery_stop_time'] : null,
                            'publisher_platforms' => $ad['publisher_platforms'] ?? null,
                            'creative_bodies' => $ad['ad_creative_bodies'] ?? null,
                            'creative_link_titles' => $ad['ad_creative_link_titles'] ?? null,
                            'creative_link_descriptions' => $ad['ad_creative_link_descriptions'] ?? null,
                            'creative_link_captions' => $ad['ad_creative_link_captions'] ?? null,
                            'first_seen_at' => $existing?->first_seen_at ?? now(),
                            'last_seen_at' => now(),
                            'raw_data' => $ad,
                        ]
                    );
                }
            }

            if ($pagesFetched >= $maxPages && $after) {
                $truncated = true;
                break;
            }
        } while ($after);

        $activeCount = count($seenAdIds);

        MetaAdLibraryDailySummary::updateOrCreate(
            [
                'organization_id' => $this->organizationId,
                'client_id' => $this->clientId,
                'client_competitor_id' => $competitor->id,
                'metric_date' => $date,
            ],
            [
                'active_ads_count' => $activeCount,
                'new_ads_count' => $newAds,
                'pages_fetched' => $pagesFetched,
                'records_fetched' => $recordsFetched,
                'truncated' => $truncated,
                'raw_data' => [
                    'country' => $country,
                ],
            ]
        );

        return [
            'active_ads_count' => $activeCount,
            'new_ads_count' => $newAds,
            'pages_fetched' => $pagesFetched,
            'records_fetched' => $recordsFetched,
            'truncated' => $truncated,
        ];
    }
}
