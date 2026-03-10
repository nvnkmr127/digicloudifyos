<?php

namespace App\Console\Commands;

use App\Jobs\SyncAdInsights;
use App\Models\AdAccount;
use Illuminate\Console\Command;

class SyncAllAdInsights extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ads:sync-insights 
                            {--start= : Start date (Y-m-d)} 
                            {--end= : End date (Y-m-d)} 
                            {--account= : Specific ad account local id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync ad insights (spend, clicks, conversions, etc.) for all active ad accounts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startDate = $this->option('start') ?? now()->subDays(1)->toDateString();
        $endDate = $this->option('end') ?? now()->toDateString();
        $accountId = $this->option('account');

        $this->info("Starting ads insights sync for range: $startDate to $endDate");

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
            $this->info("Dispatching insights sync for: {$account->account_name} ({$account->platform})");
            SyncAdInsights::dispatch($account, $startDate, $endDate);
        }

        $this->info("Dispatched {$accounts->count()} sync jobs.");

        return self::SUCCESS;
    }
}
