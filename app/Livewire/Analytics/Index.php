<?php

namespace App\Livewire\Analytics;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        $orgId = auth()->user()->organization_id;

        $revenue = Invoice::where('organization_id', $orgId)->where('status', 'paid')->sum('paid_amount');
        $pending = Invoice::where('organization_id', $orgId)->where('status', '!=', 'paid')->sum('total_amount');

        $clientsCount = Client::where('organization_id', $orgId)->count();
        $projectsCount = Project::where('organization_id', $orgId)->count();

        // Calculate a dummy conversion rate / cac based on clients
        $conversionRate = $clientsCount > 0 ? 65 : 0; 
        $cac = 450;

        return view('livewire.analytics.index', [
            'revenue' => $revenue,
            'pending' => $pending,
            'clientsCount' => $clientsCount,
            'projectsCount' => $projectsCount,
            'conversionRate' => $conversionRate,
            'cac' => $cac,
        ]);
    }
}
