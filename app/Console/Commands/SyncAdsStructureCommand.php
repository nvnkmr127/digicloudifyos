<?php

namespace App\Console\Commands;

use App\Jobs\SyncAdsStructure;
use App\Models\AdAccount;
use Illuminate\Console\Command;

class SyncAdsStructureCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ads:sync-structure {--account= : Specific ad account local id to sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync ad hierarchy (campaigns, adsets, ads, creatives) for all active ad accounts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $accountId = $this->option('account');

        $this->info('Starting ads structure sync...');

        $query = AdAccount::query()->where('status', 'ACTIVE');

        if ($accountId) {
            $query->where('id', $accountId);
        }

        $accounts = $query->get();

        if ($accounts->isEmpty()) {
            $this->warn('No active ad accounts found to sync.');
            return self::SUCCESS;
        }

        foreach ($accounts as $account) {
            $this->info("Dispatching sync job for account: {$account->account_name} ({$account->platform})");
            SyncAdsStructure::dispatch($account);
        }

        $this->info("Dispatched {$accounts->count()} sync jobs.");

        return self::SUCCESS;
    }
}
