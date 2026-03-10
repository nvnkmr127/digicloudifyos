<?php

namespace App\Console\Commands;

use App\Jobs\SyncFacebookLeads;
use App\Models\AdAccount;
use Illuminate\Console\Command;

class SyncAllFacebookLeads extends Command
{
    protected $signature = 'ads:sync-leads {--account= : Specific ad account local id}';
    protected $description = 'Sync Facebook Lead Ads for all active ad accounts with connected pages';

    public function handle()
    {
        $accountId = $this->option('account');

        $query = AdAccount::query()
            ->where('status', 'ACTIVE')
            ->where('platform', 'META_ADS')
            ->whereNotNull('facebook_page_id');

        if ($accountId) {
            $query->where('id', $accountId);
        }

        $accounts = $query->get();

        if ($accounts->isEmpty()) {
            $this->warn('No active Meta ad accounts with connected pages found.');
            return self::SUCCESS;
        }

        foreach ($accounts as $account) {
            $this->info("Dispatching lead sync for: {$account->account_name}");
            SyncFacebookLeads::dispatch($account);
        }

        $this->info("Dispatched {$accounts->count()} lead sync jobs.");

        return self::SUCCESS;
    }
}
