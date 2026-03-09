<?php

namespace App\Livewire\WorkflowMonitoring;

use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.workflow-monitoring.dashboard')->layout('layouts.app');
    }
}
