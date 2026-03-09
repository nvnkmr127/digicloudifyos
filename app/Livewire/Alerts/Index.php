<?php

namespace App\Livewire\Alerts;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.alerts.index')->layout('layouts.app');
    }
}
