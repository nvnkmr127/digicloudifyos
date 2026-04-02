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

class PurgeRetentionData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue('intelligence');
    }

    public function handle(): void
    {
        $clients = Client::whereNotNull('data_retention_days')->get(['id', 'organization_id', 'data_retention_days']);

        foreach ($clients as $client) {
            $days = (int) $client->data_retention_days;
            if ($days <= 0) {
                continue;
            }

            $cutoff = now()->subDays($days)->startOfDay();

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
    }
}
