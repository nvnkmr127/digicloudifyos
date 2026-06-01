<?php

namespace App\Jobs\Compliance;

use App\Models\Client;
use App\Models\GoogleMerchantCenterDailyMetric;
use App\Models\IntegrationSyncRun;
use App\Models\MetaAdLibraryAd;
use App\Models\PerformanceSnapshot;
use App\Models\SocialListeningEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PurgeRetentionData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 300;

    public function __construct(public bool $dryRun = false)
    {
        $this->onQueue('intelligence');
    }

    public function handle(): void
    {
        Log::info('Starting PurgeRetentionData job', ['dry_run' => $this->dryRun]);

        $clients = Client::whereNotNull('data_retention_days')->get(['id', 'organization_id', 'data_retention_days']);
        $totalPurged = 0;

        foreach ($clients as $client) {
            $days = (int) $client->data_retention_days;
            if ($days <= 0) {
                continue;
            }

            $cutoff = now()->subDays($days)->startOfDay();

            $snapshotsCount = PerformanceSnapshot::where('organization_id', $client->organization_id)
                ->where('client_id', $client->id)
                ->where('snapshot_date', '<', $cutoff->toDateString())
                ->whereNotNull('raw_data')
                ->count();

            $syncsCount = IntegrationSyncRun::where('organization_id', $client->organization_id)
                ->where('client_id', $client->id)
                ->where('created_at', '<', $cutoff)
                ->whereNotNull('metrics')
                ->count();

            $socialCount = SocialListeningEvent::where('organization_id', $client->organization_id)
                ->where('client_id', $client->id)
                ->where('created_at', '<', $cutoff)
                ->where(function ($query) {
                    $query->whereNotNull('content')->orWhereNotNull('raw_data');
                })
                ->count();

            $adsCount = MetaAdLibraryAd::where('organization_id', $client->organization_id)
                ->where('client_id', $client->id)
                ->where('created_at', '<', $cutoff)
                ->whereNotNull('raw_data')
                ->count();

            $merchantCount = GoogleMerchantCenterDailyMetric::where('organization_id', $client->organization_id)
                ->where('client_id', $client->id)
                ->where('metric_date', '<', $cutoff->toDateString())
                ->where(function ($query) {
                    $query->whereNotNull('raw_data')->orWhereNotNull('feed_statuses')->orWhereNotNull('top_issue_examples');
                })
                ->count();

            $totalForClient = $snapshotsCount + $syncsCount + $socialCount + $adsCount + $merchantCount;

            if ($totalForClient > 0) {
                Log::info('PurgeRetentionData: Found records to purge for client', [
                    'client_id' => $client->id,
                    'cutoff_date' => $cutoff->toDateString(),
                    'snapshots' => $snapshotsCount,
                    'syncs' => $syncsCount,
                    'social' => $socialCount,
                    'ads' => $adsCount,
                    'merchant' => $merchantCount,
                ]);
            }

            if (! $this->dryRun) {
                PerformanceSnapshot::where('organization_id', $client->organization_id)
                    ->where('client_id', $client->id)
                    ->where('snapshot_date', '<', $cutoff->toDateString())
                    ->update(['raw_data' => null]);

                IntegrationSyncRun::where('organization_id', $client->organization_id)
                    ->where('client_id', $client->id)
                    ->where('created_at', '<', $cutoff)
                    ->update(['metrics' => null]);

                SocialListeningEvent::where('organization_id', $client->organization_id)
                    ->where('client_id', $client->id)
                    ->where('created_at', '<', $cutoff)
                    ->update(['content' => null, 'raw_data' => null]);

                MetaAdLibraryAd::where('organization_id', $client->organization_id)
                    ->where('client_id', $client->id)
                    ->where('created_at', '<', $cutoff)
                    ->update(['raw_data' => null]);

                GoogleMerchantCenterDailyMetric::where('organization_id', $client->organization_id)
                    ->where('client_id', $client->id)
                    ->where('metric_date', '<', $cutoff->toDateString())
                    ->update(['raw_data' => null, 'feed_statuses' => null, 'top_issue_examples' => null]);
            }

            $totalPurged += $totalForClient;
        }

        Log::info('Finished PurgeRetentionData job', [
            'dry_run' => $this->dryRun,
            'total_records_affected' => $totalPurged,
        ]);
    }
}
