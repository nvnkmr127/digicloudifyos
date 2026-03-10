<?php

namespace App\Jobs;

use App\Models\AdAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncAdInsights implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;

    public function __construct(
        public AdAccount $adAccount,
        public ?string $startDate = null,
        public ?string $endDate = null
    ) {
        $this->onQueue('insights');
    }

    public function handle(): void
    {
        try {
            $startDate = $this->startDate ?? now()->subDays(1)->toDateString();
            $endDate = $this->endDate ?? now()->toDateString();

            Log::info('Syncing ad insights insights', [
                'ad_account_id' => $this->adAccount->id,
                'platform' => $this->adAccount->platform,
                'range' => "$startDate to $endDate",
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

            // Sync all levels
            $levels = ['account', 'campaign', 'adset', 'ad'];

            foreach ($levels as $level) {
                Log::info("Syncing level: $level", ['ad_account_id' => $this->adAccount->id]);
                $service->syncInsights($this->adAccount, $startDate, $endDate, $level);

                // Sync breakdowns for each level (Discovery Phase 6)
                if ($this->adAccount->platform === 'META_ADS') {
                    $breakdownGroups = [
                        ['age', 'gender'],
                        ['country'],
                        ['region'],
                        ['city'],
                        ['device_platform'],
                        ['publisher_platform', 'platform_position'],
                        ['hourly_stats']
                    ];

                    foreach ($breakdownGroups as $group) {
                        $service->syncBreakdowns($this->adAccount, $startDate, $endDate, $level, $group);
                    }
                }
            }

            Log::info('Successfully finished ad insights sync', [
                'ad_account_id' => $this->adAccount->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to sync ad insights', [
                'ad_account_id' => $this->adAccount->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
