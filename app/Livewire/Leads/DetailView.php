<?php

namespace App\Livewire\Leads;

use Livewire\Component;

class DetailView extends Component
{
    public $lead;

    public function mount($id, \App\Repositories\LeadRepository $repository)
    {
        $this->lead = $repository->find($id);

        if (!$this->lead) {
            abort(404);
        }

        $this->authorize('view', $this->lead);
    }

    public function render()
    {
        return view('livewire.leads.detail-view')->layout('layouts.app');
    }
}
