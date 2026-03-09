<?php

namespace App\Livewire\Leads;

use Livewire\Component;

class DetailView extends Component
{
    public $lead;

    public function mount($id)
    {
        $this->lead = \App\Models\Lead::where('id', $id)
            ->where('organization_id', \Illuminate\Support\Facades\Auth::user()->organization_id)
            ->with(['client'])
            ->firstOrFail();
    }

    public function render()
    {
        return view('livewire.leads.detail-view')->layout('layouts.app');
    }
}
