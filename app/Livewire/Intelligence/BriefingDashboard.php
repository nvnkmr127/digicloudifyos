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
        if ($id) {
            $this->briefing = DailyBriefing::findOrFail($id);
        } else {
            $this->briefing = DailyBriefing::where('organization_id', auth()->user()->organization_id)
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
        $item = BriefingActionItem::findOrFail($itemId);
        $item->complete(auth()->id());

        session()->flash('message', 'Action item marked as completed.');
    }

    public function render()
    {
        $items = [];
        if ($this->briefing) {
            $query = $this->briefing->actionItems()->with('client');

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
