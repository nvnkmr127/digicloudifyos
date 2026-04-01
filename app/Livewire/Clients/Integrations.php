<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\ClientChannelConnection;
use App\Models\IntegrationCredential;
use App\Models\IntegrationSyncRun;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Integrations extends Component
{
    public Client $client;
    public string $shopifyShop = '';
    public string $wooStoreUrl = '';
    public string $wooConsumerKey = '';
    public string $wooConsumerSecret = '';

    public function mount(Client $client): void
    {
        $user = Auth::user();
        if (! $user instanceof \App\Models\User || ! $user->isAdmin()) {
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

        return view('livewire.clients.integrations', [
            'connections' => $connections,
            'recentRuns' => $recentRuns,
        ]);
    }
}
