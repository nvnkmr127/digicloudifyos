<?php

namespace App\Livewire\Proposals;

use App\Models\Proposal;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Show extends Component
{
    public Proposal $proposal;

    public function mount(Proposal $proposal): void
    {
        if ($proposal->organization_id !== Auth::user()->organization_id) {
            abort(404);
        }

        $this->proposal = $proposal->load('client');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.proposals.show');
    }
}
