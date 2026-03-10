<?php

namespace App\Livewire\Campaigns;

use Livewire\Component;

class DetailView extends Component
{
    public $campaign;
    public $activeTab = 'creative'; // Default tab: creative, adsets, performance, audience

    public function mount($id)
    {
        $this->loadCampaign($id);
    }

    protected function loadCampaign($id)
    {
        $this->campaign = \App\Models\Campaign::where('id', $id)
            ->where('organization_id', \Illuminate\Support\Facades\Auth::user()->organization_id)
            ->with(['client', 'adAccount', 'creativeRequests', 'tasks', 'adSets.ads', 'dailyMetrics', 'facebookLeads', 'audienceInsights'])
            ->firstOrFail();
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function syncMetrics()
    {
        \App\Jobs\SyncCampaignMetrics::dispatch($this->campaign);
        session()->flash('message', 'Syncing campaign metrics and hierarchy in the background...');
    }

    public function pauseCampaign()
    {
        $service = $this->getService();
        if ($service && $service->pauseCampaign($this->campaign)) {
            $this->loadCampaign($this->campaign->id);
            session()->flash('message', 'Campaign paused successfully.');
        } else {
            session()->flash('error', 'Failed to pause campaign.');
        }
    }

    public function archiveCampaign()
    {
        $service = $this->getService();
        if ($service && $service->archiveCampaign($this->campaign)) {
            $this->loadCampaign($this->campaign->id);
            session()->flash('message', 'Campaign archived successfully.');
        } else {
            session()->flash('error', 'Failed to archive campaign.');
        }
    }

    public function deleteCampaign()
    {
        $service = $this->getService();
        if ($service && $service->deleteCampaign($this->campaign)) {
            return redirect()->route('campaigns.index')->with('message', 'Campaign deleted successfully.');
        } else {
            session()->flash('error', 'Failed to delete campaign.');
        }
    }

    protected function getService()
    {
        $platform = $this->campaign->adAccount->platform ?? null;
        return match ($platform) {
            'META_ADS' => new \App\Services\MetaAdsService(),
            'GOOGLE_ADS' => new \App\Services\GoogleAdsService(),
            'LINKEDIN_ADS' => new \App\Services\LinkedInAdsService(),
            default => null,
        };
    }

    #[\Livewire\Attributes\Layout('layouts.app')]
    public function render()
    {
        return view('livewire.campaigns.detail-view');
    }
}
