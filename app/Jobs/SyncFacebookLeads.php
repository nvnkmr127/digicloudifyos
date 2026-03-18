<?php

namespace App\Jobs;

use App\Models\AdAccount;
use App\Models\LeadSyncLog;
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
        public AdAccount $adAccount,
        public string $source = 'scheduled'
    ) {
        $this->onQueue('leads');
    }

    public function handle(): void
    {
        if ($this->adAccount->platform !== 'META_ADS') {
            return;
        }

        $syncLog = LeadSyncLog::create([
            'organization_id' => $this->adAccount->organization_id,
            'ad_account_id' => $this->adAccount->id,
            'source' => $this->source,
            'status' => 'processing',
        ]);

        try {
            Log::info('Syncing Facebook Leads', ['ad_account_id' => $this->adAccount->id]);

            $service = new MetaAdsService();
            $leads = $service->syncAllLeads($this->adAccount);

            $syncLog->update([
                'status' => 'success',
                'leads_processed' => $leads->count(),
            ]);

            Log::info('Successfully finished Facebook Lead sync', [
                'ad_account_id' => $this->adAccount->id,
                'count' => $leads->count(),
            ]);

        } catch (\Exception $e) {
            $syncLog->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'details' => ['trace' => $e->getTraceAsString()],
            ]);

            Log::error('Failed to sync Facebook Leads', [
                'ad_account_id' => $this->adAccount->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
