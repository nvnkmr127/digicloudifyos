<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class EvaluateAdsPerformanceRules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ads:evaluate-rules';
    protected $description = 'Evaluate performance rules and trigger automations';

    public function handle()
    {
        $this->info('Evaluating performance rules...');

        $accounts = \App\Models\AdAccount::where('status', 'ACTIVE')->get();

        foreach ($accounts as $account) {
            $this->evaluateCampaigns($account);
            $this->evaluateAds($account);
            $this->evaluateBudgets($account);
        }

        $this->info('Evaluation complete.');
    }

    protected function evaluateBudgets($account)
    {
        $campaigns = $account->campaigns()->where('status', 'running')->get();

        foreach ($campaigns as $campaign) {
            $this->checkBudgetThresholds($campaign);
        }
    }

    protected function checkBudgetThresholds(\App\Models\Campaign $campaign)
    {
        // Check Daily Budget usage (last 24h)
        if ($campaign->daily_budget > 0) {
            $dailySpend = $campaign->adInsights()
                ->where('date', now()->toDateString())
                ->sum('spend');

            $usage = ($dailySpend / $campaign->daily_budget) * 100;
            if ($usage >= 90) {
                $this->createBudgetAlert($campaign, 'daily_usage', "Daily budget usage is at " . round($usage, 2) . "%", [
                    'spend' => $dailySpend,
                    'budget' => $campaign->daily_budget,
                    'usage_percentage' => $usage
                ]);
            }
        }

        // Check Lifetime Budget usage
        if ($campaign->lifetime_budget > 0) {
            $totalSpend = $campaign->adInsights()->sum('spend');
            $usage = ($totalSpend / $campaign->lifetime_budget) * 100;

            if ($usage >= 90) {
                $this->createBudgetAlert($campaign, 'lifetime_usage', "Lifetime budget usage is at " . round($usage, 2) . "%", [
                    'spend' => $totalSpend,
                    'budget' => $campaign->lifetime_budget,
                    'usage_percentage' => $usage
                ]);
            }
        }

        // Check Spend Cap usage
        if ($campaign->spend_cap > 0) {
            $totalSpend = $campaign->adInsights()->sum('spend');
            $usage = ($totalSpend / $campaign->spend_cap) * 100;

            if ($usage >= 90) {
                $this->createBudgetAlert($campaign, 'cap_usage', "Spend cap usage is at " . round($usage, 2) . "%", [
                    'spend' => $totalSpend,
                    'budget' => $campaign->spend_cap,
                    'usage_percentage' => $usage
                ]);
            }
        }
    }

    protected function createBudgetAlert(\App\Models\Campaign $campaign, $type, $message, $details)
    {
        $existing = \App\Models\BudgetAlert::where('campaign_id', $campaign->id)
            ->where('alert_type', $type)
            ->whereDate('created_at', now()->toDateString())
            ->first();

        if ($existing)
            return;

        \App\Models\BudgetAlert::create([
            'organization_id' => $campaign->organization_id,
            'campaign_id' => $campaign->id,
            'alert_type' => $type,
            'message' => $message,
            'details' => $details
        ]);

        $this->warn("Budget Alert [{$type}] on {$campaign->name}: {$message}");

        // Also trigger general automation
        \App\Jobs\ProcessWorkflowAutomation::dispatch('ads_budget_warning', [
            'organization_id' => $campaign->organization_id,
            'entity_type' => 'campaign',
            'entity_id' => $campaign->id,
            'campaign_name' => $campaign->name,
            'alert_type' => $type,
            'message' => $message
        ]);
    }

    protected function evaluateCampaigns($account)
    {
        $campaigns = $account->campaigns()->where('status', 'running')->get();
        $targetCpl = $account->target_cpl ?? 10.00;
        $targetCtr = $account->target_ctr ?? 1.00;

        /** @var \App\Models\Campaign $campaign */
        foreach ($campaigns as $campaign) {
            // Get last 24h insights
            $insight = $campaign->adInsights()
                ->where('date', '>=', now()->subDay()->toDateString())
                ->where('level', 'campaign')
                ->selectRaw('SUM(spend) as spend, SUM(impressions) as impressions, SUM(clicks) as clicks, SUM(conversions) as conversions')
                ->first();

            if (!$insight || $insight->impressions == 0)
                continue;

            $ctr = ($insight->clicks / $insight->impressions) * 100;
            $cpl = $insight->conversions > 0 ? $insight->spend / $insight->conversions : 0;

            // Low CTR Alert
            if ($ctr < $targetCtr) {
                $this->warn("Low CTR on campaign {$campaign->name}: " . round($ctr, 2) . "%");
                \App\Jobs\ProcessWorkflowAutomation::dispatch('ads_low_ctr', [
                    'organization_id' => $account->organization_id,
                    'entity_type' => 'campaign',
                    'entity_id' => $campaign->id,
                    'campaign_name' => $campaign->name,
                    'ctr' => round($ctr, 2),
                    'threshold' => $targetCtr
                ]);
            }

            // High CPL Alert
            if ($cpl > $targetCpl && $insight->conversions > 0) {
                $this->warn("High CPL on campaign {$campaign->name}: $" . round($cpl, 2));
                \App\Jobs\ProcessWorkflowAutomation::dispatch('ads_high_cpl', [
                    'organization_id' => $account->organization_id,
                    'entity_type' => 'campaign',
                    'entity_id' => $campaign->id,
                    'campaign_name' => $campaign->name,
                    'cpl' => round($cpl, 2),
                    'threshold' => $targetCpl
                ]);
            }
        }
    }

    protected function evaluateAds($account)
    {
        $targetFrequency = $account->target_frequency ?? 3.0;

        // Fetch ads across all running campaigns for this account
        $ads = \App\Models\Ad::whereHas('adSet.campaign', function ($q) use ($account) {
            $q->where('ad_account_id', $account->id)->where('status', 'running');
        })->get();

        /** @var \App\Models\Ad $ad */
        foreach ($ads as $ad) {
            $insight = $ad->adInsights()
                ->where('date', '>=', now()->subDays(7)->toDateString()) // Check frequency over 7 days
                ->where('level', 'ad')
                ->selectRaw('SUM(impressions) as impressions, SUM(reach) as reach')
                ->first();

            if (!$insight || $insight->reach == 0)
                continue;

            $frequency = $insight->impressions / $insight->reach;

            if ($frequency > $targetFrequency) {
                $this->warn("Creative Fatigue on ad {$ad->name}: " . round($frequency, 2));
                \App\Jobs\ProcessWorkflowAutomation::dispatch('ads_creative_fatigue', [
                    'organization_id' => $account->organization_id,
                    'entity_type' => 'ad',
                    'entity_id' => $ad->id,
                    'ad_name' => $ad->name,
                    'frequency' => round($frequency, 2),
                    'threshold' => $targetFrequency
                ]);
            }
        }
    }
}
