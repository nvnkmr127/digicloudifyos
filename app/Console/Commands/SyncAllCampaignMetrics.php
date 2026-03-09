<?php

namespace App\Console\Commands;

use App\Jobs\SyncCampaignMetrics;
use App\Models\Campaign;
use Illuminate\Console\Command;

class SyncAllCampaignMetrics extends Command
{
    protected $signature = 'campaigns:sync-metrics
                            {--date= : Specific date to sync metrics for (Y-m-d format)}
                            {--campaign= : Specific campaign ID to sync}';

    protected $description = 'Sync metrics for all active campaigns from external platforms';

    public function handle(): int
    {
        $date = $this->option('date') ?? now()->toDateString();
        $campaignId = $this->option('campaign');

        $this->info('Starting campaign metrics sync...');

        $query = Campaign::query()->where('status', 'running');

        if ($campaignId) {
            $query->where('id', $campaignId);
        }

        $campaigns = $query->get();

        if ($campaigns->isEmpty()) {
            $this->warn('No active campaigns found to sync.');

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($campaigns->count());
        $bar->start();

        foreach ($campaigns as $campaign) {
            SyncCampaignMetrics::dispatch($campaign, $date);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info("Dispatched {$campaigns->count()} sync jobs to the queue.");

        return self::SUCCESS;
    }
}
