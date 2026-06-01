<?php

namespace App\Livewire\Proposals;

use App\Models\Proposal;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public function delete($id)
    {
        $proposal = Proposal::where('organization_id', Auth::user()->organization_id)
            ->findOrFail($id);
        $proposal->delete();
        session()->flash('success', 'Proposal deleted.');
    }

    public function render()
    {
        $proposals = Proposal::where('organization_id', Auth::user()->organization_id)
            ->with(['client'])
            ->latest()
            ->paginate(10);

        return view('livewire.proposals.index', [
            'proposals' => $proposals,
        ])->layout('layouts.app');
    }
}
