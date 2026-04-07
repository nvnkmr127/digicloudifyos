<?php

namespace App\Jobs\Integrations;

use App\Models\Client;
use App\Models\ClientChannelConnection;
use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunDailyIntegrationSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;

    public function __construct(public ?string $date = null)
    {
        $this->onQueue('intelligence');
    }

    public function handle(): void
    {
        $date = $this->date ?? now()->subDay()->toDateString();

        foreach (Organization::lazy() as $org) {
            $clients = Client::where('organization_id', $org->id)->active()->get();
            $connections = ClientChannelConnection::where('organization_id', $org->id)
                ->active()
                ->get(['client_id', 'channel_type']);

            $has = [];
            foreach ($connections as $c) {
                $has[$c->client_id][$c->channel_type] = true;
            }

            foreach ($clients as $client) {
                if (isset($has[$client->id]['ga4'])) {
                    SyncGoogleAnalyticsDailyMetrics::dispatch($org->id, $client->id, $date);
                }

                if (isset($has[$client->id]['google_search_console'])) {
                    SyncSearchConsoleDailyMetrics::dispatch($org->id, $client->id, $date);
                }

                if (isset($has[$client->id]['google_merchant_center'])) {
                    SyncGoogleMerchantCenterDailyMetrics::dispatch($org->id, $client->id, $date);
                }

                if (isset($has[$client->id]['google_business_profile'])) {
                    SyncGoogleBusinessProfileDailyMetrics::dispatch($org->id, $client->id, $date);

                    if (now()->day === 1) {
                        $monthStart = now()->subMonth()->startOfMonth()->toDateString();
                        SyncGoogleBusinessProfileMonthlyKeywords::dispatch($org->id, $client->id, $monthStart);
                    }
                }

                if (isset($has[$client->id]['amazon'])) {
                    SyncAmazonSpDailyMetrics::dispatch($org->id, $client->id, $date);
                }

                if (isset($has[$client->id]['shopify'])) {
                    SyncShopifyDailyMetrics::dispatch($org->id, $client->id, $date);
                }

                if (isset($has[$client->id]['woocommerce'])) {
                    SyncWooCommerceDailyMetrics::dispatch($org->id, $client->id, $date);
                }

                if (isset($has[$client->id]['facebook_organic'])) {
                    SyncMetaPageDailyMetrics::dispatch($org->id, $client->id, $date);
                }

                if (isset($has[$client->id]['instagram'])) {
                    SyncInstagramDailyMetrics::dispatch($org->id, $client->id, $date);
                }

                if (isset($has[$client->id]['twitter'])) {
                    SyncTwitterDailyMetrics::dispatch($org->id, $client->id, $date);
                }

                if (isset($has[$client->id]['linkedin_organic'])) {
                    SyncLinkedInOrganizationDailyMetrics::dispatch($org->id, $client->id, $date);
                }
            }
        }
    }
}
