<?php

namespace App\Livewire\Campaigns;

use App\Models\Campaign;
use App\Models\Client;
use App\Services\CampaignService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class KanbanBoard extends Component
{
    use AuthorizesRequests;

    public $campaigns = [];

    public $clients = [];

    public $statusFilter = 'all';

    public $clientFilter = null;

    public $searchQuery = '';

    public $columns = [
        ['key' => 'planning', 'title' => 'Planning', 'color' => 'bg-gray-100'],
        ['key' => 'creative_requested', 'title' => 'Creative Requested', 'color' => 'bg-blue-100'],
        ['key' => 'ready', 'title' => 'Ready', 'color' => 'bg-purple-100'],
        ['key' => 'running', 'title' => 'Running', 'color' => 'bg-green-100'],
        ['key' => 'optimizing', 'title' => 'Optimizing', 'color' => 'bg-yellow-100'],
        ['key' => 'completed', 'title' => 'Completed', 'color' => 'bg-gray-300'],
    ];

    protected $listeners = [
        'campaignUpdated' => 'refreshCampaigns',
        'campaignCreated' => 'refreshCampaigns',
    ];

    public function mount(CampaignService $service)
    {
        $this->loadClients();
        $this->refreshCampaigns($service);
    }

    public function loadClients()
    {
        // Global scope now handles organization_id automatically
        $this->clients = Client::active()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function refreshCampaigns(CampaignService $service)
    {
        $filters = [
            'status' => $this->statusFilter,
            'client_id' => $this->clientFilter,
            'search' => $this->searchQuery,
        ];

        $campaigns = $service->getAllForOrganization(Auth::user()->organization_id, $filters);

        $this->campaigns = collect($this->columns)
            ->mapWithKeys(function ($column) use ($campaigns) {
                return [
                    $column['key'] => $campaigns->where('status', $column['key'])->values()->toArray(),
                ];
            })
            ->toArray();
    }

    public function updatedStatusFilter()
    {
        $this->refreshCampaigns(app(CampaignService::class));
    }

    public function updatedClientFilter()
    {
        $this->refreshCampaigns(app(CampaignService::class));
    }

    public function updatedSearchQuery()
    {
        $this->refreshCampaigns(app(CampaignService::class));
    }

    public function updateCampaignStatus($campaignId, $newStatus, CampaignService $service)
    {
        try {
            $campaign = Campaign::findOrFail($campaignId);

            $this->authorize('update', $campaign);

            $oldStatus = $campaign->status;

            // Use the service to handle the update, events, and cache clearing
            $service->update($campaign, ['status' => $newStatus]);

            $this->refreshCampaigns($service);

            $this->dispatch('campaignUpdated', [
                'campaignId' => $campaignId,
                'oldStatus' => $oldStatus,
                'newStatus' => $newStatus,
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Campaign status updated successfully',
            ]);

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to update campaign status: '.$e->getMessage(),
            ]);
        }
    }

    public function deleteCampaign($campaignId, CampaignService $service)
    {
        try {
            $campaign = Campaign::findOrFail($campaignId);
            $this->authorize('delete', $campaign);

            $service->delete($campaign);
            $this->refreshCampaigns($service);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Campaign deleted successfully',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to delete campaign: '.$e->getMessage(),
            ]);
        }
    }

    public function clearFilters()
    {
        $this->statusFilter = 'all';
        $this->clientFilter = null;
        $this->searchQuery = '';
        $this->refreshCampaigns(app(CampaignService::class));
    }

    public function render()
    {
        return view('livewire.campaigns.kanban-board')->layout('layouts.app');
    }
}
