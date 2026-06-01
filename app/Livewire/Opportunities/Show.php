<?php

namespace App\Livewire\Opportunities;

use App\Models\Opportunity;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public Opportunity $opportunity;

    public function mount($id)
    {
        $this->opportunity = Opportunity::with(['pipeline', 'stage', 'contact'])
            ->where('organization_id', Auth::user()->organization_id)
            ->findOrFail($id);
    }

    public function markAsWon()
    {
        $this->opportunity->update(['status' => 'won']);
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Opportunity marked as won!']);
    }

    public function markAsLost()
    {
        $this->opportunity->update(['status' => 'lost']);
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Opportunity marked as lost.']);
    }

    public function createProposal()
    {
        // Simple redirection to proposal creation with client pre-selected if available
        return redirect()->route('proposals.create', ['client_id' => $this->opportunity->contact?->client_id]);
    }

    public function render()
    {
        return view('livewire.opportunities.show');
    }
}
