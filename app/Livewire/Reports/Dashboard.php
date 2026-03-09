<?php

namespace App\Livewire\Reports;

use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.reports.dashboard')->layout('layouts.app');
    }
}
