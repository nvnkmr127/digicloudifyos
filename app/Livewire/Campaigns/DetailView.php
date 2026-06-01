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

    public $selectedAd = null;

    public $showAdModal = false;

    public function showAdPreview($adId)
    {
        $this->selectedAd = \App\Models\Ad::with('adCreative')
            ->where('id', $adId)
            ->where('organization_id', auth()->user()->organization_id)
            ->firstOrFail();
        $this->showAdModal = true;
    }

    public function mount(Campaign $campaign)
    {
        $this->loadCampaign($campaign->id);
    }

    protected function loadCampaign($id)
    {
        // Enforce organization scoping early to prevent unauthorized DB load (B029)
        $this->campaign = Campaign::where('id', $id)
            ->where('organization_id', auth()->user()->organization_id)
            ->firstOrFail();

        $this->authorize('view', $this->campaign);

        // Load only core relations; others are deferred to setTab (B026)
        $this->campaign->load(['client', 'adAccount']);

        // Load initial tab data
        $this->loadTabData();
    }

    /**
     * Set the active tab and load associated data if needed.
     */
    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->loadTabData();
    }

    /**
     * Efficiently handle deferred loading of tab-specific relationships (B026)
     */
    protected function loadTabData()
    {
        match ($this->activeTab) {
            'creative' => $this->campaign->load('creativeRequests'),
            'adsets' => $this->campaign->load('adSets.ads'),
            'performance' => $this->campaign->load('adInsights'),
            'audience' => $this->campaign->load('audienceInsights'),
            'tasks' => $this->campaign->load('tasks'),
            'leads' => $this->campaign->load('facebookLeads'),
            default => null
        };
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

        $platformDeleted = false;
        if ($service) {
            try {
                $platformDeleted = $service->deleteCampaign($this->campaign);
            } catch (\Exception $e) {
                \Log::warning('Platform campaign deletion failed for ID: '.$this->campaign?->id.'. Error: '.$e->getMessage());
            }
        }

        // Atomicity Patch: Always ensure local deletion if it was a draft or platform delete was attempted
        if (! $this->campaign->external_campaign_id || $platformDeleted) {
            if ($this->campaign->exists) {
                $this->campaign->delete();
            }

            return redirect()->route('campaigns.index')->with('success', 'Campaign removed successfully.');
        }

        // If it had an external ID but service failed, still allow local-only delete with specific flag
        if ($this->campaign->exists) {
            $this->campaign->delete();

            return redirect()->route('campaigns.index')->with('success', 'Campaign removed locally (platform deletion may have failed).');
        }

        return redirect()->route('campaigns.index')->with('error', 'Failed to remove campaign.');
    }

    protected function getService()
    {
        $platform = $this->campaign->adAccount->platform ?? null;

        return match (strtoupper($platform)) {
            'META_ADS', 'META', 'FACEBOOK' => new MetaAdsService,
            'GOOGLE_ADS', 'GOOGLE' => new GoogleAdsService,
            'LINKEDIN_ADS', 'LINKEDIN' => new LinkedInAdsService,
            default => null,
        };
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.campaigns.detail-view');
    }
}
