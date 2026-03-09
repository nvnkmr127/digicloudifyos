<?php

namespace App\Livewire\Leads;

use Livewire\Component;

class DetailView extends Component
{
    public function render()
    {
        return view('livewire.leads.detail-view')->layout('layouts.app');
    }
}
