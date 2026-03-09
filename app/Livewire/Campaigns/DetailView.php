<?php

namespace App\Livewire\Campaigns;

use Livewire\Component;

class DetailView extends Component
{
    public function render()
    {
        return view('livewire.campaigns.detail-view')->layout('layouts.app');
    }
}
