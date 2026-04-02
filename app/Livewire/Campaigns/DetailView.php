<?php

namespace App\Livewire\Campaigns;

use App\Jobs\SyncCampaignMetrics;
use App\Models\Campaign;
use App\Services\GoogleAdsService;
use App\Services\LinkedInAdsService;
use App\Services\MetaAdsService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

class DetailView extends Component
{
    use AuthorizesRequests;

    public $campaign;

    public $activeTab = 'creative'; // Default tab: creative, adsets, performance, audience

    public function mount($id)
    {
        $this->loadCampaign($id);
    }

    protected function loadCampaign($id)
    {
        $this->campaign = Campaign::with(['client', 'adAccount', 'creativeRequests', 'tasks', 'adSets.ads', 'dailyMetrics', 'facebookLeads', 'audienceInsights'])
            ->findOrFail($id);

        $this->authorize('view', $this->campaign);
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function syncMetrics()
    {
        $this->authorize('update', $this->campaign);
        SyncCampaignMetrics::dispatch($this->campaign);
        session()->flash('message', 'Syncing campaign metrics and hierarchy in the background...');
    }

    public function pauseCampaign()
    {
        $this->authorize('update', $this->campaign);
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
        $this->authorize('update', $this->campaign);
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
        $this->authorize('delete', $this->campaign);
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
            'META_ADS' => new MetaAdsService,
            'GOOGLE_ADS' => new GoogleAdsService,
            'LINKEDIN_ADS' => new LinkedInAdsService,
            default => null,
        };
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.campaigns.detail-view');
    }
}
