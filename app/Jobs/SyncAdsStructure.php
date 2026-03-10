<?php

namespace App\Jobs;

use App\Models\AdAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncAdsStructure implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300; // Structure sync can take time

    /**
     * Create a new job instance.
     */
    public function __construct(public AdAccount $adAccount)
    {
        $this->onQueue('sync');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Syncing ads structure for account', [
                'ad_account_id' => $this->adAccount->id,
                'platform' => $this->adAccount->platform,
            ]);

            $service = match ($this->adAccount->platform) {
                'META_ADS' => new \App\Services\MetaAdsService(),
                'GOOGLE_ADS' => new \App\Services\GoogleAdsService(),
                'LINKEDIN_ADS' => new \App\Services\LinkedInAdsService(),
                default => null,
            };

            if (!$service) {
                Log::warning('No service found for platform', ['platform' => $this->adAccount->platform]);
                return;
            }

            // Sync Full Hierarchy
            $service->syncFullHierarchy($this->adAccount);

            Log::info('Successfully synced ads structure', [
                'ad_account_id' => $this->adAccount->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to sync ads structure', [
                'ad_account_id' => $this->adAccount->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
