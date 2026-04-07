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

    public function render()
    {
        return view('livewire.opportunities.show');
    }
}
