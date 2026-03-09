<?php

namespace App\Livewire\Leads;

use App\Models\Lead;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class KanbanBoard extends Component
{
    public $leads = [];

    public $sourceFilter = 'all';

    public $searchQuery = '';

    public $columns = [
        ['key' => 'New', 'title' => 'New', 'color' => 'bg-blue-100'],
        ['key' => 'Contacted', 'title' => 'Contacted', 'color' => 'bg-yellow-100'],
        ['key' => 'Interested', 'title' => 'Interested', 'color' => 'bg-purple-100'],
        ['key' => 'Offer Sent', 'title' => 'Offer Sent', 'color' => 'bg-orange-100'],
        ['key' => 'Won', 'title' => 'Won', 'color' => 'bg-green-100'],
        ['key' => 'Lost', 'title' => 'Lost', 'color' => 'bg-red-100'],
    ];

    protected $listeners = [
        'leadUpdated' => 'refreshLeads',
        'leadCreated' => 'refreshLeads',
    ];

    public function mount()
    {
        $this->refreshLeads();
    }

    public function refreshLeads()
    {
        $query = Lead::query()
            ->where('organization_id', Auth::user()->organization_id);

        if ($this->sourceFilter !== 'all') {
            $query->where('source', $this->sourceFilter);
        }

        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->where('name', 'ilike', '%'.$this->searchQuery.'%')
                    ->orWhere('email', 'ilike', '%'.$this->searchQuery.'%')
                    ->orWhere('phone', 'ilike', '%'.$this->searchQuery.'%');
            });
        }

        $leads = $query->orderBy('created_at', 'desc')->get();

        $this->leads = collect($this->columns)
            ->mapWithKeys(function ($column) use ($leads) {
                return [
                    $column['key'] => $leads->where('status', $column['key'])->values()->toArray(),
                ];
            })
            ->toArray();
    }

    public function updatedSourceFilter()
    {
        $this->refreshLeads();
    }

    public function updatedSearchQuery()
    {
        $this->refreshLeads();
    }

    public function updateLeadStatus($leadId, $newStatus)
    {
        try {
            $lead = Lead::where('id', $leadId)
                ->where('organization_id', Auth::user()->organization_id)
                ->firstOrFail();

            $this->authorize('update', $lead);

            $oldStatus = $lead->status;
            $lead->update(['status' => $newStatus]);

            $this->refreshLeads();

            $this->dispatch('leadUpdated', [
                'leadId' => $leadId,
                'oldStatus' => $oldStatus,
                'newStatus' => $newStatus,
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Lead status updated successfully',
            ]);

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to update lead status: '.$e->getMessage(),
            ]);
        }
    }

    public function clearFilters()
    {
        $this->sourceFilter = 'all';
        $this->searchQuery = '';
        $this->refreshLeads();
    }

    public function render()
    {
        return view('livewire.leads.kanban-board');
    }
}
