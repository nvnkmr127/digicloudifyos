<?php

namespace App\Livewire\Campaigns;

use Livewire\Component;

class DetailView extends Component
{
    public $campaign;

    public function mount($id)
    {
        $this->campaign = \App\Models\Campaign::where('id', $id)
            ->where('organization_id', \Illuminate\Support\Facades\Auth::user()->organization_id)
            ->with(['client', 'adAccount', 'creativeRequests', 'tasks'])
            ->firstOrFail();
    }

    public function render()
    {
        return view('livewire.campaigns.detail-view')->layout('layouts.app');
    }
}
