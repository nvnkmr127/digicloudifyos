<?php

namespace App\Console\Commands;

use App\Jobs\SyncCampaignMetrics;
use App\Models\Campaign;
use Illuminate\Console\Command;

class SyncAllCampaignMetrics extends Command
{
    protected $signature = 'ads:sync-metrics {--campaign= : Specific campaign local id}';
    protected $description = 'Sync metrics and hierarchy for all active ad campaigns';

    public function handle()
    {
        $campaignId = $this->option('campaign');

        $query = Campaign::whereHas('adAccount', function ($q) {
            $q->where('status', 'ACTIVE');
        });

        if ($campaignId) {
            $query->where('id', $campaignId);
        }

        $campaigns = $query->get();

        if ($campaigns->isEmpty()) {
            $this->warn('No active ad campaigns found for sync.');
            return self::SUCCESS;
        }

        foreach ($campaigns as $campaign) {
            $this->info("Dispatching sync for: {$campaign->name}");
            SyncCampaignMetrics::dispatch($campaign);
        }

        $this->info("Dispatched {$campaigns->count()} campaign sync jobs.");

        return self::SUCCESS;
    }
}
