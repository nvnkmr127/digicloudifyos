<?php

namespace App\Livewire\Intelligence;

use App\Models\BriefingActionItem;
use App\Models\DailyBriefing;
use Livewire\Component;

class BriefingDashboard extends Component
{
    public $briefing;

    public $activeTab = 'urgent';

    public function mount($id = null)
    {
        $orgId = auth()->user()->organization_id;

        if ($id) {
            $this->briefing = DailyBriefing::where('organization_id', $orgId)->findOrFail($id);

            return;
        } else {
            $this->briefing = DailyBriefing::where('organization_id', $orgId)
                ->latest('briefing_date')
                ->first();
        }
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function completeItem($itemId)
    {
        // Enforce role-based access control (B035)
        if (auth()->user()->hasRole('VIEWER')) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'You do not have permission to complete action items.']);

            return;
        }

        $item = BriefingActionItem::whereHas('briefing', function ($q) {
            $q->where('organization_id', auth()->user()->organization_id);
        })->findOrFail($itemId);

        $item->complete(auth()->id());

        session()->flash('message', 'Action item marked as completed.');

        // No explicit filter needed here as render() will re-query and filter (B033)
    }

    public function render()
    {
        $items = [];
        if ($this->briefing) {
            // Filter out completed items to ensure UX state consistency (B033)
            $query = $this->briefing->actionItems()
                ->whereNull('completed_at')
                ->with('client');

            if ($this->activeTab === 'urgent') {
                $items = $query->where('priority_level', 'urgent')->orderBy('sort_order')->get();
            } elseif ($this->activeTab === 'important') {
                $items = $query->where('priority_level', 'important')->orderBy('sort_order')->get();
            } else {
                $items = $query->where('priority_level', 'opportunity')->orderBy('sort_order')->get();
            }
        }

        return view('livewire.intelligence.briefing-dashboard', [
            'items' => $items,
        ])->layout('layouts.app');
    }
}
