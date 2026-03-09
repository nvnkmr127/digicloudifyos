<?php

namespace App\Livewire\Analytics;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        $revenue = \App\Models\Invoice::where('status', 'paid')->sum('paid_amount');
        $pending = \App\Models\Invoice::where('status', '!=', 'paid')->sum('total_amount');

        $clientsCount = \App\Models\Client::count();
        $projectsCount = \App\Models\Project::count();

        // Calculate a dummy conversion rate / cac based on clients
        $conversionRate = $clientsCount > 0 ? 65 : 0; // Using dummy logic for funnel depth for now
        $cac = 450;

        return view('livewire.analytics.index', [
            'revenue' => collect([$revenue])->first() ?? 0,
            'pending' => collect([$pending])->first() ?? 0,
            'clientsCount' => $clientsCount,
            'projectsCount' => $projectsCount,
            'conversionRate' => $conversionRate,
            'cac' => $cac,
        ]);
    }
}
