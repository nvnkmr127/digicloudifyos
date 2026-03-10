<?php

namespace App\Jobs;

use App\Models\AdAccount;
use App\Services\MetaAdsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncFacebookLeads implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;

    public function __construct(
        public AdAccount $adAccount
    ) {
        $this->onQueue('leads');
    }

    public function handle(): void
    {
        if ($this->adAccount->platform !== 'META_ADS') {
            return;
        }

        try {
            Log::info('Syncing Facebook Leads', ['ad_account_id' => $this->adAccount->id]);

            $service = new MetaAdsService();
            $leads = $service->syncAllLeads($this->adAccount);

            Log::info('Successfully finished Facebook Lead sync', [
                'ad_account_id' => $this->adAccount->id,
                'count' => $leads->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to sync Facebook Leads', [
                'ad_account_id' => $this->adAccount->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
