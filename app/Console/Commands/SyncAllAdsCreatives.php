<?php

namespace App\Console\Commands;

use App\Jobs\SyncAdsCreatives;
use App\Models\AdAccount;
use Illuminate\Console\Command;

class SyncAllAdsCreatives extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ads:sync-creatives {--account= : Specific ad account local id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync ad creatives for all active ad accounts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $accountId = $this->option('account');

        $this->info('Starting ads creatives sync...');

        $query = AdAccount::query()->where('status', 'ACTIVE');

        if ($accountId) {
            $query->where('id', $accountId);
        }

        $accounts = $query->get();

        if ($accounts->isEmpty()) {
            $this->warn('No active ad accounts found to sync creatives.');

            return self::SUCCESS;
        }

        foreach ($accounts as $account) {
            $this->info("Dispatching creatives sync for: {$account->account_name} ({$account->platform})");
            SyncAdsCreatives::dispatch($account);
        }

        $this->info("Dispatched {$accounts->count()} creatives sync jobs.");

        return self::SUCCESS;
    }
}
