<?php

namespace App\Livewire\Campaigns;

use Livewire\Component;

class CreateForm extends Component
{
    public function render()
    {
        return view('livewire.campaigns.create-form')->layout('layouts.app');
    }
}
