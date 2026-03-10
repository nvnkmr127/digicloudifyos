<?php

namespace App\Livewire\Settings;

use App\Models\FacebookUser;
use App\Models\AdAccount;
use App\Models\Client;
use App\Services\MetaAdsService;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class AdsConnections extends Component
{
    public $clients;
    public $selectedClientId = '';
    public $showPageSelector = false;
    public $selectedAccountId = null;
    public $pages = [];

    public function mount()
    {
        $this->clients = Client::where('organization_id', auth()->user()->organization_id)->get();
    }

    public function render()
    {
        $facebookUser = FacebookUser::where('organization_id', auth()->user()->organization_id)->first();
        $adAccounts = AdAccount::where('organization_id', auth()->user()->organization_id)
            ->where('platform', 'META_ADS')
            ->get();

        return view('livewire.settings.ads-connections', [
            'facebookUser' => $facebookUser,
            'adAccounts' => $adAccounts,
        ]);
    }

    public function disconnectFacebook()
    {
        try {
            FacebookUser::where('organization_id', auth()->user()->organization_id)->delete();
            AdAccount::where('organization_id', auth()->user()->organization_id)
                ->where('platform', 'META_ADS')
                ->delete();

            session()->flash('success', 'Facebook Ads disconnected successfully.');
        } catch (\Exception $e) {
            Log::error('FB disconnection error: ' . $e->getMessage());
            session()->flash('error', 'Failed to disconnect Facebook Ads.');
        }
    }

    public function syncNow()
    {
        $accounts = AdAccount::where('organization_id', auth()->user()->organization_id)
            ->where('status', 'ACTIVE')
            ->get();

        if ($accounts->isEmpty()) {
            session()->flash('error', 'No active ad accounts found to sync.');
            return;
        }

        foreach ($accounts as $account) {
            \App\Jobs\SyncAdsStructure::dispatch($account);
        }

        session()->flash('success', 'Campaign and ad structure synchronization started for all accounts.');
    }

    public function openPageSelector($accountId)
    {
        $this->selectedAccountId = $accountId;
        $account = AdAccount::findOrFail($accountId);

        $service = new MetaAdsService();
        $this->pages = $service->getPages($account->access_token)->toArray();
        $this->showPageSelector = true;
    }

    public function connectPage($pageId, $pageToken)
    {
        $account = AdAccount::findOrFail($this->selectedAccountId);
        $account->update([
            'facebook_page_id' => $pageId,
            'facebook_page_token' => $pageToken,
        ]);

        $this->showPageSelector = false;
        $this->selectedAccountId = null;

        \App\Jobs\SyncFacebookLeads::dispatch($account);
        session()->flash('success', 'Facebook Page connected for lead sync!');
    }
}
