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

        $now = now();
        $dispatchedCount = 0;

        foreach ($accounts as $account) {
            $frequency = $account->credentials['sync_frequency'] ?? '15_min';
            $lastSync = $account->credentials['last_lead_sync'] ?? null;
            
            $shouldSync = false;
            
            if ($frequency === 'never') {
                continue;
            }

            if (!$lastSync) {
                $shouldSync = true;
            } else {
                $lastSyncTime = \Carbon\Carbon::parse($lastSync);
                $shouldSync = match($frequency) {
                    '15_min' => $now->diffInMinutes($lastSyncTime) >= 15,
                    'hourly' => $now->diffInHours($lastSyncTime) >= 1,
                    'daily' => $now->diffInDays($lastSyncTime) >= 1,
                    default => $now->diffInMinutes($lastSyncTime) >= 15,
                };
            }

            if ($shouldSync || $accountId) {
                $this->info("Dispatching lead sync for: {$account->account_name}");
                SyncFacebookLeads::dispatch($account);
                $dispatchedCount++;
                
                // Update last sync time
                $creds = $account->credentials ?? [];
                $creds['last_lead_sync'] = $now->toIso8601String();
                $account->update(['credentials' => $creds]);
            }
        }

        $this->info("Dispatched {$dispatchedCount} lead sync jobs.");

        return self::SUCCESS;
    }
}
