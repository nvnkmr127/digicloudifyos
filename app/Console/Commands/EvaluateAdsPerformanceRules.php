<?php

namespace App\Console\Commands;

use App\Jobs\ProcessWorkflowAutomation;
use App\Models\Ad;
use App\Models\AdAccount;
use App\Models\Alert;
use App\Models\BudgetAlert;
use App\Models\Campaign;
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

        $accounts = AdAccount::where('status', 'ACTIVE')->get();

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

    protected function checkBudgetThresholds(Campaign $campaign)
    {
        // Check Daily Budget usage (last 24h)
        if ($campaign->daily_budget > 0) {
            $dailySpend = $campaign->adInsights()
                ->where('date', now()->toDateString())
                ->sum('spend');

            $usage = ($dailySpend / $campaign->daily_budget) * 100;
            if ($usage >= 90) {
                $this->createBudgetAlert($campaign, 'daily_usage', 'Daily budget usage is at '.round($usage, 2).'%', [
                    'spend' => $dailySpend,
                    'budget' => $campaign->daily_budget,
                    'usage_percentage' => $usage,
                ]);
            }
        }

        // Check Lifetime Budget usage
        if ($campaign->lifetime_budget > 0) {
            $totalSpend = $campaign->adInsights()->sum('spend');
            $usage = ($totalSpend / $campaign->lifetime_budget) * 100;

            if ($usage >= 90) {
                $this->createBudgetAlert($campaign, 'lifetime_usage', 'Lifetime budget usage is at '.round($usage, 2).'%', [
                    'spend' => $totalSpend,
                    'budget' => $campaign->lifetime_budget,
                    'usage_percentage' => $usage,
                ]);
            }
        }

        // Check Spend Cap usage
        if ($campaign->spend_cap > 0) {
            $totalSpend = $campaign->adInsights()->sum('spend');
            $usage = ($totalSpend / $campaign->spend_cap) * 100;

            if ($usage >= 90) {
                $this->createBudgetAlert($campaign, 'cap_usage', 'Spend cap usage is at '.round($usage, 2).'%', [
                    'spend' => $totalSpend,
                    'budget' => $campaign->spend_cap,
                    'usage_percentage' => $usage,
                ]);
            }
        }
    }

    protected function createBudgetAlert(Campaign $campaign, $type, $message, $details)
    {
        $existing = BudgetAlert::where('campaign_id', $campaign->id)
            ->where('alert_type', $type)
            ->whereDate('created_at', now()->toDateString())
            ->first();

        if ($existing) {
            return;
        }

        BudgetAlert::create([
            'organization_id' => $campaign->organization_id,
            'campaign_id' => $campaign->id,
            'alert_type' => $type,
            'message' => $message,
            'details' => $details,
        ]);

        $this->warn("Budget Alert [{$type}] on {$campaign->name}: {$message}");

        // Also trigger general automation
        ProcessWorkflowAutomation::dispatch('ads_budget_warning', [
            'organization_id' => $campaign->organization_id,
            'entity_type' => 'campaign',
            'entity_id' => $campaign->id,
            'campaign_name' => $campaign->name,
            'alert_type' => $type,
            'message' => $message,
        ]);
    }

    protected function createPerformanceAlert(Campaign $campaign, $type, $message, $details, $severity = 'warning')
    {
        $existing = Alert::where('campaign_id', $campaign->id)
            ->where('alert_type', $type)
            ->whereDate('created_at', now()->toDateString())
            ->first();

        if ($existing) {
            return;
        }

        Alert::create([
            'organization_id' => $campaign->organization_id,
            'client_id' => $campaign->client_id,
            'campaign_id' => $campaign->id,
            'alert_type' => $type,
            'severity' => $severity,
            'title' => ucwords(str_replace('_', ' ', $type)),
            'message' => $message,
            'payload' => $details,
            'triggered_at' => now(),
            'status' => 'OPEN',
        ]);
    }

    protected function evaluateCampaigns($account)
    {
        $campaigns = $account->campaigns()->where('status', 'running')->get();
        $targetCpl = $account->target_cpl ?? 10.00;
        $targetCtr = $account->target_ctr ?? 1.00;
        $targetCpc = $account->target_cpc ?? 0.50;

        /** @var Campaign $campaign */
        foreach ($campaigns as $campaign) {
            // Get last 24h insights
            $insight = $campaign->adInsights()
                ->where('date', '>=', now()->subDay()->toDateString())
                ->where('level', 'campaign')
                ->selectRaw('SUM(spend) as spend, SUM(impressions) as impressions, SUM(clicks) as clicks, SUM(conversions) as conversions')
                ->first();

            if (! $insight || $insight->impressions == 0) {
                continue;
            }

            $ctr = ($insight->clicks / $insight->impressions) * 100;
            $cpl = $insight->conversions > 0 ? $insight->spend / $insight->conversions : 0;
            $cpc = $insight->clicks > 0 ? $insight->spend / $insight->clicks : 0;

            // Low CTR Alert
            if ($ctr < $targetCtr) {
                $msg = "Low CTR on campaign {$campaign->name}: ".round($ctr, 2).'%';
                $this->warn($msg);

                $details = [
                    'entity_type' => 'campaign',
                    'entity_id' => $campaign->id,
                    'campaign_name' => $campaign->name,
                    'ctr' => round($ctr, 2),
                    'threshold' => $targetCtr,
                ];
                $this->createPerformanceAlert($campaign, 'ads_low_ctr', $msg, $details);

                ProcessWorkflowAutomation::dispatch('ads_low_ctr', array_merge($details, ['organization_id' => $account->organization_id]));
            }

            // High CPL Alert
            if ($cpl > $targetCpl && $insight->conversions > 0) {
                $msg = "High CPL on campaign {$campaign->name}: $".round($cpl, 2);
                $this->warn($msg);

                $details = [
                    'entity_type' => 'campaign',
                    'entity_id' => $campaign->id,
                    'campaign_name' => $campaign->name,
                    'cpl' => round($cpl, 2),
                    'threshold' => $targetCpl,
                ];
                $this->createPerformanceAlert($campaign, 'ads_high_cpl', $msg, $details);

                ProcessWorkflowAutomation::dispatch('ads_high_cpl', array_merge($details, ['organization_id' => $account->organization_id]));
            }

            // High CPC Alert
            if ($cpc > $targetCpc && $insight->clicks > 0) {
                $msg = "High CPC on campaign {$campaign->name}: $".round($cpc, 2);
                $this->warn($msg);

                $details = [
                    'entity_type' => 'campaign',
                    'entity_id' => $campaign->id,
                    'campaign_name' => $campaign->name,
                    'cpc' => round($cpc, 2),
                    'threshold' => $targetCpc,
                ];
                $this->createPerformanceAlert($campaign, 'ads_high_cpc', $msg, $details);

                ProcessWorkflowAutomation::dispatch('ads_high_cpc', array_merge($details, ['organization_id' => $account->organization_id]));
            }
        }
    }

    protected function evaluateAds($account)
    {
        $targetFrequency = $account->target_frequency ?? 3.0;

        // Fetch ads across all running campaigns for this account
        $ads = Ad::whereHas('adSet.campaign', function ($q) use ($account) {
            $q->where('ad_account_id', $account->id)->where('status', 'running');
        })->get();

        /** @var Ad $ad */
        foreach ($ads as $ad) {
            $insight = $ad->adInsights()
                ->where('date', '>=', now()->subDays(7)->toDateString()) // Check frequency over 7 days
                ->where('level', 'ad')
                ->selectRaw('SUM(impressions) as impressions, SUM(reach) as reach')
                ->first();

            if (! $insight || $insight->reach == 0) {
                continue;
            }

            $frequency = $insight->impressions / $insight->reach;

            if ($frequency > $targetFrequency) {
                $msg = "Creative Fatigue on ad {$ad->name}: ".round($frequency, 2);
                $this->warn($msg);

                $details = [
                    'entity_type' => 'ad',
                    'entity_id' => $ad->id,
                    'ad_name' => $ad->name,
                    'frequency' => round($frequency, 2),
                    'threshold' => $targetFrequency,
                ];

                $this->createPerformanceAlert($ad->adSet->campaign, 'ads_creative_fatigue', $msg, $details);

                ProcessWorkflowAutomation::dispatch('ads_creative_fatigue', array_merge($details, ['organization_id' => $account->organization_id]));
            }
        }
    }
}
