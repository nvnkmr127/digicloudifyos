<?php

namespace App\Livewire\Intelligence;

use App\Models\Client;
use App\Models\PerformanceAnomaly;
use App\Models\AiInsight;
use Livewire\Component;

class Overview extends Component
{
    public function render()
    {
        $orgId = auth()->user()->organization_id;

        return view('livewire.intelligence.overview', [
            'clients' => Client::where('organization_id', $orgId)
                ->with(['latestHealthScore'])
                ->active()
                ->get(),
            'topAlerts' => PerformanceAnomaly::where('organization_id', $orgId)
                ->unresolved()
                ->orderByRaw("FIELD(severity, 'critical', 'high', 'medium', 'low')")
                ->limit(5)
                ->get(),
            'recentInsights' => AiInsight::where('organization_id', $orgId)
                ->active()
                ->latest('insight_date')
                ->limit(3)
                ->get(),
        ])->layout('layouts.app');
    }
}
