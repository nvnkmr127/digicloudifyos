<?php

namespace App\Livewire\Campaigns;

use App\Models\Campaign;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class KanbanBoard extends Component
{
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

    public function mount()
    {
        $this->loadClients();
        $this->refreshCampaigns();
    }

    public function loadClients()
    {
        $this->clients = Client::where('organization_id', Auth::user()->organization_id)
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function refreshCampaigns()
    {
        $query = Campaign::query()
            ->where('organization_id', Auth::user()->organization_id)
            ->with(['client:id,name', 'adAccount:id,account_name,platform']);

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->clientFilter) {
            $query->where('client_id', $this->clientFilter);
        }

        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->searchQuery.'%')
                    ->orWhereHas('client', function ($clientQuery) {
                        $clientQuery->where('name', 'like', '%'.$this->searchQuery.'%');
                    });
            });
        }

        $campaigns = $query->orderBy('created_at', 'desc')->get();

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
        $this->refreshCampaigns();
    }

    public function updatedClientFilter()
    {
        $this->refreshCampaigns();
    }

    public function updatedSearchQuery()
    {
        $this->refreshCampaigns();
    }

    public function updateCampaignStatus($campaignId, $newStatus)
    {
        try {
            $campaign = Campaign::where('id', $campaignId)
                ->where('organization_id', Auth::user()->organization_id)
                ->firstOrFail();

            $this->authorize('update', $campaign);

            $oldStatus = $campaign->status;
            $campaign->update(['status' => $newStatus]);

            $this->refreshCampaigns();

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

    public function clearFilters()
    {
        $this->statusFilter = 'all';
        $this->clientFilter = null;
        $this->searchQuery = '';
        $this->refreshCampaigns();
    }

    public function render()
    {
        return view('livewire.campaigns.kanban-board')->layout('layouts.app');
    }
}
