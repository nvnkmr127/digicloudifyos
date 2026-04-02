<?php

namespace App\Livewire\Settings;

use App\Jobs\SyncAdsStructure;
use App\Jobs\SyncFacebookLeads;
use App\Models\AdAccount;
use App\Models\Client;
use App\Models\FacebookUser;
use App\Services\MetaAdsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class AdsConnections extends Component
{
    public $clients;

    public $selectedClientId = '';

    public $showPageSelector = false;

    public $selectedAccountId = null;

    public $pages = [];

    public $pageSelectorError = null;

    public $syncFrequencies = [];

    public function mount()
    {
        $this->clients = Client::where('organization_id', Auth::user()?->organization_id)->get();

        // Initialize sync frequencies
        $adAccounts = AdAccount::where('organization_id', Auth::user()?->organization_id)
            ->where('platform', 'META_ADS')
            ->get();

        foreach ($adAccounts as $account) {
            $this->syncFrequencies[$account->id] = $account->credentials['sync_frequency'] ?? '15_min';
        }
    }

    public function updateSyncFrequency($accountId)
    {
        $account = AdAccount::where('organization_id', Auth::user()?->organization_id)
            ->findOrFail($accountId);

        $freq = $this->syncFrequencies[$accountId] ?? '15_min';

        $creds = $account->credentials ?? [];
        $creds['sync_frequency'] = $freq;
        $account->update(['credentials' => $creds]);

        session()->flash('success', 'Sync frequency updated to '.str_replace('_', ' ', $freq).'.');
    }

    public function render()
    {
        $facebookUser = FacebookUser::where('organization_id', Auth::user()?->organization_id)->first();
        $adAccounts = AdAccount::where('organization_id', Auth::user()?->organization_id)
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
            FacebookUser::where('organization_id', Auth::user()?->organization_id)->delete();
            AdAccount::where('organization_id', Auth::user()?->organization_id)
                ->where('platform', 'META_ADS')
                ->delete();

            session()->flash('success', 'Facebook Ads disconnected successfully.');
        } catch (\Exception $e) {
            Log::error('FB disconnection error: '.$e->getMessage());
            session()->flash('error', 'Failed to disconnect Facebook Ads.');
        }
    }

    public function syncNow()
    {
        $accounts = AdAccount::where('organization_id', Auth::user()?->organization_id)
            ->where('status', 'ACTIVE')
            ->get();

        if ($accounts->isEmpty()) {
            session()->flash('error', 'No active ad accounts found to sync.');

            return;
        }

        foreach ($accounts as $account) {
            SyncAdsStructure::dispatch($account);
        }

        session()->flash('success', 'Campaign and ad structure synchronization started for all accounts.');
    }

    public function openPageSelector($accountId)
    {
        $this->pageSelectorError = null;
        $this->pages = [];
        $this->selectedAccountId = $accountId;
        $account = AdAccount::where('organization_id', Auth::user()?->organization_id)->findOrFail($accountId);

        try {
            $this->pages = app(MetaAdsService::class)->getPages($account->access_token)->toArray();
        } catch (\Throwable $e) {
            Log::warning('Failed to fetch Meta pages for selector: '.$e->getMessage());
            $this->pageSelectorError = 'Failed to load pages from Meta. Please try again.';
        }

        $this->showPageSelector = true;
    }

    public function connectPage($pageId, $pageToken)
    {
        $account = AdAccount::where('organization_id', Auth::user()?->organization_id)->findOrFail($this->selectedAccountId);
        $account->update([
            'facebook_page_id' => $pageId,
            'facebook_page_token' => $pageToken,
        ]);

        $this->showPageSelector = false;
        $this->selectedAccountId = null;
        $this->pageSelectorError = null;

        SyncFacebookLeads::dispatch($account);
        session()->flash('success', 'Facebook Page connected for lead sync!');
    }
}
