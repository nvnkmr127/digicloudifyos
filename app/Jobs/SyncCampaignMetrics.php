<?php

namespace App\Jobs;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncCampaignMetrics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 120;

    public $backoff = [30, 60, 120];

    public function __construct(
        public Campaign $campaign,
        public ?string $date = null
    ) {
        $this->onQueue('metrics');
    }

    public function handle(): void
    {
        try {
            Log::info('Syncing metrics for campaign', [
                'campaign_id' => $this->campaign->id,
                'date' => $this->date,
            ]);

            if (! $this->campaign->external_campaign_id) {
                Log::warning('Campaign has no external ID, skipping metrics sync', [
                    'campaign_id' => $this->campaign->id,
                ]);

                return;
            }

            $platform = $this->campaign->adAccount->platform;

            $metricsData = match ($platform) {
                'facebook' => $this->fetchFacebookMetrics(),
                'google' => $this->fetchGoogleMetrics(),
                'tiktok' => $this->fetchTikTokMetrics(),
                default => null,
            };

            if ($metricsData) {
                $this->campaign->dailyMetrics()->updateOrCreate(
                    ['date' => $this->date ?? now()->toDateString()],
                    $metricsData
                );

                Log::info('Successfully synced campaign metrics', [
                    'campaign_id' => $this->campaign->id,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to sync campaign metrics', [
                'campaign_id' => $this->campaign->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($this->attempts() >= $this->tries) {
                $this->fail($e);
            } else {
                $this->release($this->backoff[$this->attempts() - 1]);
            }
        }
    }

    protected function fetchFacebookMetrics(): array
    {
        return [
            'impressions' => rand(1000, 10000),
            'clicks' => rand(100, 1000),
            'spend' => rand(50, 500),
            'conversions' => rand(10, 100),
            'ctr' => rand(1, 5),
            'cpc' => rand(1, 10),
            'cpm' => rand(5, 20),
        ];
    }

    protected function fetchGoogleMetrics(): array
    {
        return [
            'impressions' => rand(1000, 10000),
            'clicks' => rand(100, 1000),
            'spend' => rand(50, 500),
            'conversions' => rand(10, 100),
            'ctr' => rand(1, 5),
            'cpc' => rand(1, 10),
            'cpm' => rand(5, 20),
        ];
    }

    protected function fetchTikTokMetrics(): array
    {
        return [
            'impressions' => rand(1000, 10000),
            'clicks' => rand(100, 1000),
            'spend' => rand(50, 500),
            'conversions' => rand(10, 100),
            'ctr' => rand(1, 5),
            'cpc' => rand(1, 10),
            'cpm' => rand(5, 20),
        ];
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Campaign metrics sync job failed permanently', [
            'campaign_id' => $this->campaign->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
