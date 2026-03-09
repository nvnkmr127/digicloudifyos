<?php

namespace App\Console\Commands;

use App\Jobs\SendCampaignNotification;
use App\Models\Alert;
use App\Models\Campaign;
use Illuminate\Console\Command;

class CheckCampaignPerformance extends Command
{
    protected $signature = 'campaigns:check-performance';

    protected $description = 'Check campaign performance and generate alerts';

    public function handle(): int
    {
        $this->info('Checking campaign performance...');

        $campaigns = Campaign::where('status', 'running')
            ->with(['dailyMetrics' => function ($query) {
                $query->whereBetween('date', [now()->subDays(7), now()]);
            }])
            ->get();

        if ($campaigns->isEmpty()) {
            $this->warn('No running campaigns found.');

            return self::SUCCESS;
        }

        $alertsCreated = 0;

        foreach ($campaigns as $campaign) {
            $this->checkBudgetUsage($campaign, $alertsCreated);
            $this->checkCTRPerformance($campaign, $alertsCreated);
            $this->checkConversionRate($campaign, $alertsCreated);
        }

        $this->info("Performance check complete. Created {$alertsCreated} alerts.");

        return self::SUCCESS;
    }

    protected function checkBudgetUsage(Campaign $campaign, int &$alertsCreated): void
    {
        if (! $campaign->daily_budget && ! $campaign->lifetime_budget) {
            return;
        }

        $totalSpend = $campaign->dailyMetrics()->sum('spend');
        $budget = $campaign->lifetime_budget ?? ($campaign->daily_budget * 30);

        $percentage = ($totalSpend / $budget) * 100;

        if ($percentage >= 90 && $percentage < 100) {
            Alert::create([
                'campaign_id' => $campaign->id,
                'organization_id' => $campaign->organization_id,
                'type' => 'budget_warning',
                'severity' => 'warning',
                'message' => "Campaign has used {$percentage}% of budget",
                'details' => [
                    'total_spend' => $totalSpend,
                    'budget' => $budget,
                    'percentage' => $percentage,
                ],
            ]);

            SendCampaignNotification::dispatch($campaign, 'budget_alert', [
                'percentage' => round($percentage, 2),
            ]);

            $alertsCreated++;
        }
    }

    protected function checkCTRPerformance(Campaign $campaign, int &$alertsCreated): void
    {
        $avgCTR = $campaign->dailyMetrics()->avg('ctr');

        if ($avgCTR !== null && $avgCTR < 0.5) {
            Alert::create([
                'campaign_id' => $campaign->id,
                'organization_id' => $campaign->organization_id,
                'type' => 'low_ctr',
                'severity' => 'warning',
                'message' => "Campaign CTR is below threshold ({$avgCTR}%)",
                'details' => [
                    'average_ctr' => $avgCTR,
                    'threshold' => 0.5,
                ],
            ]);

            SendCampaignNotification::dispatch($campaign, 'performance_alert', [
                'message' => "Low CTR detected: {$avgCTR}%",
            ]);

            $alertsCreated++;
        }
    }

    protected function checkConversionRate(Campaign $campaign, int &$alertsCreated): void
    {
        $metrics = $campaign->dailyMetrics()
            ->selectRaw('SUM(conversions) as total_conversions, SUM(clicks) as total_clicks')
            ->first();

        if ($metrics->total_clicks > 0) {
            $conversionRate = ($metrics->total_conversions / $metrics->total_clicks) * 100;

            if ($conversionRate < 1) {
                Alert::create([
                    'campaign_id' => $campaign->id,
                    'organization_id' => $campaign->organization_id,
                    'type' => 'low_conversion',
                    'severity' => 'warning',
                    'message' => "Campaign conversion rate is below threshold ({$conversionRate}%)",
                    'details' => [
                        'conversion_rate' => $conversionRate,
                        'total_conversions' => $metrics->total_conversions,
                        'total_clicks' => $metrics->total_clicks,
                    ],
                ]);

                SendCampaignNotification::dispatch($campaign, 'performance_alert', [
                    'message' => "Low conversion rate detected: {$conversionRate}%",
                ]);

                $alertsCreated++;
            }
        }
    }
}
