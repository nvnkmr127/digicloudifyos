<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Jobs\ProcessWorkflowAutomation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncCampaignMetrics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 120;

    public $backoff = [30, 60, 120];

    public function __construct(
        public Campaign $campaign,
        public ?string $date = null
    ) {
        $this->onQueue('metrics');
    }

    public function handle(): void
    {
        try {
            Log::info('Syncing metrics for campaign', [
                'campaign_id' => $this->campaign->id,
                'date' => $this->date,
            ]);

            if (!$this->campaign->external_campaign_id) {
                Log::warning('Campaign has no external ID, skipping metrics sync', [
                    'campaign_id' => $this->campaign->id,
                ]);

                return;
            }

            $platform = $this->campaign->adAccount->platform;
            $date = $this->date ?? now()->toDateString();

            /** @var \App\Services\BaseAdsService $service */
            $service = match ($platform) {
                'META_ADS' => new \App\Services\MetaAdsService(),
                'GOOGLE_ADS' => new \App\Services\GoogleAdsService(),
                'LINKEDIN_ADS' => new \App\Services\LinkedInAdsService(),
                default => null,
            };

            if ($service) {
                // Sync Hierarchy for Meta
                if ($platform === 'META_ADS') {
                    $adSets = $service->syncAdSets($this->campaign);
                    foreach ($adSets as $adSet) {
                        $service->syncAds($adSet);
                    }
                }

                $service->syncMetrics($this->campaign, $date, $date);

                // Also sync new AdInsight level data
                if ($this->campaign->adAccount) {
                    $service->syncInsights($this->campaign->adAccount, $date, $date, 'campaign');
                    if ($platform === 'META_ADS') {
                        $service->syncBreakdowns($this->campaign->adAccount, $date, $date, 'campaign');
                    }
                }

                Log::info('Successfully synced campaign metrics and hierarchy', [
                    'campaign_id' => $this->campaign->id,
                    'platform' => $platform,
                    'date' => $date,
                ]);

                // Automation Triggers
                $this->evaluatePerformanceAutomations($date);
            } else {
                Log::warning('No service found for platform', [
                    'platform' => $platform,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to sync campaign metrics', [
                'campaign_id' => $this->campaign->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($this->attempts() >= $this->tries) {
                $this->fail($e);
            } else {
                $this->release($this->backoff[$this->attempts() - 1]);
            }
        }
    }

    protected function evaluatePerformanceAutomations(string $date): void
    {
        $insight = \App\Models\AdInsight::where('campaign_id', $this->campaign->id)
            ->where('date', $date)
            ->where('level', 'campaign')
            ->first();

        if (!$insight)
            return;

        $eventData = [
            'organization_id' => $this->campaign->organization_id,
            'entity_type' => 'campaign',
            'entity_id' => $this->campaign->id,
            'campaign_name' => $this->campaign->name,
            'spend' => $insight->spend,
            'ctr' => $insight->ctr,
            'cpc' => $insight->cpc,
            'impressions' => $insight->impressions,
        ];

        // 1. Low CTR Alert (< 0.5%)
        if ($insight->ctr < 0.005 && $insight->impressions > 500) {
            ProcessWorkflowAutomation::dispatch('ads_low_ctr', $eventData);
        }

        // 2. High CPC Alert (> $5.00)
        if ($insight->cpc > 5.00 && $insight->clicks > 10) {
            ProcessWorkflowAutomation::dispatch('ads_high_cpc', $eventData);
        }

        // 3. Creative Fatigue (CTR drop > 30% compared to avgerage)
        $avgCtr = \App\Models\AdInsight::where('campaign_id', $this->campaign->id)
            ->where('level', 'campaign')
            ->where('date', '<', $date)
            ->avg('ctr');

        if ($avgCtr > 0 && ($insight->ctr < $avgCtr * 0.7)) {
            ProcessWorkflowAutomation::dispatch('ads_creative_fatigue', $eventData);
        }

        // 4. Budget Pacing Alert
        if ($this->campaign->daily_budget > 0 && $insight->spend > ($this->campaign->daily_budget * 1.2)) {
            ProcessWorkflowAutomation::dispatch('ads_budget_pacing', array_merge($eventData, [
                'daily_budget' => $this->campaign->daily_budget,
                'over_spend_ratio' => $insight->spend / $this->campaign->daily_budget
            ]));
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Campaign metrics sync job failed permanently', [
            'campaign_id' => $this->campaign->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
