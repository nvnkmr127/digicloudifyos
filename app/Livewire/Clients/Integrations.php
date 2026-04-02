<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\ClientChannelConnection;
use App\Models\ClientCompetitor;
use App\Models\IntegrationCredential;
use App\Models\IntegrationSyncRun;
use App\Models\SocialListeningSource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Livewire\Component;

class Integrations extends Component
{
    public Client $client;

    public string $shopifyShop = '';

    public string $wooStoreUrl = '';

    public string $wooConsumerKey = '';

    public string $wooConsumerSecret = '';

    public string $amazonSellerId = '';

    public string $amazonMarketplaceId = '';

    public string $amazonEndpoint = '';

    public string $amazonAwsRegion = '';

    public string $amazonAwsAccessKeyId = '';

    public string $amazonAwsSecretAccessKey = '';

    public string $amazonRefreshToken = '';

    public string $metaCompetitorLabel = '';

    public string $metaCompetitorPageId = '';

    public string $rssCompetitorLabel = '';

    public string $rssFeedUrl = '';

    public function mount(Client $client): void
    {
        $user = Auth::user();
        if (! $user instanceof User || ! $user->isAdmin()) {
            abort(403);
        }

        if ($client->organization_id !== $user->organization_id) {
            abort(403);
        }

        $this->client = $client;
    }

    public function connectShopify()
    {
        $shop = trim($this->shopifyShop);
        if ($shop === '') {
            session()->flash('error', 'Enter a Shopify shop domain.');

            return;
        }

        return redirect()->route('integrations.oauth.redirect', [
            'provider' => 'shopify',
            'client_id' => $this->client->id,
            'shop' => $shop,
        ]);
    }

    public function connectWooCommerce(): void
    {
        $storeUrl = trim($this->wooStoreUrl);
        $consumerKey = trim($this->wooConsumerKey);
        $consumerSecret = trim($this->wooConsumerSecret);

        if ($storeUrl === '' || $consumerKey === '' || $consumerSecret === '') {
            session()->flash('error', 'Enter store URL, consumer key, and consumer secret.');

            return;
        }

        $credential = IntegrationCredential::create([
            'organization_id' => $this->client->organization_id,
            'provider' => 'woocommerce',
            'credential_type' => 'api_key',
            'label' => $storeUrl,
            'external_user_id' => $storeUrl,
            'payload' => [
                'store_url' => $storeUrl,
                'consumer_key' => $consumerKey,
                'consumer_secret' => $consumerSecret,
            ],
            'last_verified_at' => null,
        ]);

        ClientChannelConnection::updateOrCreate(
            [
                'organization_id' => $this->client->organization_id,
                'client_id' => $this->client->id,
                'channel_type' => 'woocommerce',
            ],
            [
                'integration_credential_id' => $credential->id,
                'account_id' => $storeUrl,
                'account_name' => $storeUrl,
                'is_active' => true,
                'connected_at' => now(),
                'last_sync_status' => 'connected',
                'metadata' => [
                    'store_url' => $storeUrl,
                ],
            ]
        );

        $this->wooConsumerSecret = '';

        session()->flash('success', 'WooCommerce connected successfully.');
    }

    public function connectAmazon(): void
    {
        $sellerId = trim($this->amazonSellerId);
        $marketplaceId = trim($this->amazonMarketplaceId) ?: (string) config('services.amazon_sp_api.marketplace_id', '');
        $endpoint = trim($this->amazonEndpoint) ?: (string) config('services.amazon_sp_api.endpoint', '');
        $region = trim($this->amazonAwsRegion) ?: (string) config('services.amazon_sp_api.aws_region', 'us-east-1');
        $accessKeyId = trim($this->amazonAwsAccessKeyId);
        $secretAccessKey = trim($this->amazonAwsSecretAccessKey);
        $refreshToken = trim($this->amazonRefreshToken);

        if ($marketplaceId === '' || $endpoint === '' || $region === '' || $accessKeyId === '' || $secretAccessKey === '' || $refreshToken === '') {
            session()->flash('error', 'Enter marketplace, endpoint, region, AWS keys, and refresh token.');

            return;
        }

        $credential = IntegrationCredential::create([
            'organization_id' => $this->client->organization_id,
            'provider' => 'amazon_sp_api',
            'credential_type' => 'oauth',
            'label' => $sellerId ?: 'Amazon SP-API',
            'external_user_id' => $sellerId ?: $marketplaceId,
            'access_token' => null,
            'refresh_token' => $refreshToken,
            'expires_at' => null,
            'payload' => [
                'seller_id' => $sellerId ?: null,
                'marketplace_id' => $marketplaceId,
                'endpoint' => $endpoint,
                'aws_region' => $region,
                'aws_access_key_id' => $accessKeyId,
                'aws_secret_access_key' => $secretAccessKey,
            ],
            'last_verified_at' => null,
        ]);

        ClientChannelConnection::updateOrCreate(
            [
                'organization_id' => $this->client->organization_id,
                'client_id' => $this->client->id,
                'channel_type' => 'amazon',
            ],
            [
                'integration_credential_id' => $credential->id,
                'account_id' => $sellerId ?: null,
                'account_name' => $sellerId ?: null,
                'is_active' => true,
                'connected_at' => now(),
                'last_sync_status' => 'connected',
                'metadata' => [
                    'seller_id' => $sellerId ?: null,
                    'marketplace_id' => $marketplaceId,
                    'endpoint' => $endpoint,
                    'aws_region' => $region,
                ],
            ]
        );

        $this->amazonAwsSecretAccessKey = '';
        $this->amazonRefreshToken = '';

        session()->flash('success', 'Amazon connected successfully.');
    }

    public function addMetaCompetitor(): void
    {
        $label = trim($this->metaCompetitorLabel);
        $pageId = trim($this->metaCompetitorPageId);

        if ($pageId === '') {
            session()->flash('error', 'Enter a competitor Page ID.');

            return;
        }

        ClientCompetitor::updateOrCreate(
            [
                'organization_id' => $this->client->organization_id,
                'client_id' => $this->client->id,
                'platform' => 'meta_page',
                'identifier' => $pageId,
            ],
            [
                'label' => $label ?: $pageId,
                'is_active' => true,
            ]
        );

        $this->metaCompetitorLabel = '';
        $this->metaCompetitorPageId = '';

        session()->flash('success', 'Competitor added.');
    }

    public function addRssFeed(): void
    {
        $label = trim($this->rssCompetitorLabel);
        $feedUrl = trim($this->rssFeedUrl);

        if ($label === '' || $feedUrl === '') {
            session()->flash('error', 'Enter a competitor label and RSS feed URL.');

            return;
        }

        $competitor = ClientCompetitor::firstOrCreate(
            [
                'organization_id' => $this->client->organization_id,
                'client_id' => $this->client->id,
                'platform' => 'brand',
                'identifier' => $label,
            ],
            [
                'label' => $label,
                'is_active' => true,
            ]
        );

        SocialListeningSource::create([
            'organization_id' => $this->client->organization_id,
            'client_id' => $this->client->id,
            'client_competitor_id' => $competitor->id,
            'source_type' => 'rss',
            'source_label' => $label,
            'source_url' => $feedUrl,
            'is_active' => true,
        ]);

        $this->rssCompetitorLabel = '';
        $this->rssFeedUrl = '';

        session()->flash('success', 'RSS feed added.');
    }

    public function render()
    {
        $connections = $this->client->channelConnections()
            ->with('credential')
            ->orderBy('channel_type')
            ->get();

        $recentRuns = IntegrationSyncRun::where('organization_id', $this->client->organization_id)
            ->where('client_id', $this->client->id)
            ->orderBy('run_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $metaCompetitors = ClientCompetitor::where('organization_id', $this->client->organization_id)
            ->where('client_id', $this->client->id)
            ->where('platform', 'meta_page')
            ->where('is_active', true)
            ->orderBy('label')
            ->get();

        $rssSources = SocialListeningSource::where('organization_id', $this->client->organization_id)
            ->where('client_id', $this->client->id)
            ->where('source_type', 'rss')
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        $portalUrl = URL::temporarySignedRoute('client.portal', now()->addDays(30), [
            'client' => $this->client->id,
        ]);

        return view('livewire.clients.integrations', [
            'connections' => $connections,
            'recentRuns' => $recentRuns,
            'metaCompetitors' => $metaCompetitors,
            'rssSources' => $rssSources,
            'portalUrl' => $portalUrl,
            'brandKitUrl' => route('clients.brand-kit', $this->client->id),
        ]);
    }
}
