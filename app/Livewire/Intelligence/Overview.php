<?php

namespace App\Livewire\Intelligence;

use App\Models\AiInsight;
use App\Models\Client;
use App\Models\PerformanceAnomaly;
use Livewire\Component;

class Overview extends Component
{
    public function render()
    {
        $orgId = auth()->user()->organization_id;
        $lastSync = \App\Models\PerformanceSnapshot::where('organization_id', $orgId)->latest()->first()?->created_at;
        $activeAlertsCount = PerformanceAnomaly::where('organization_id', $orgId)->unresolved()->count();

        return view('livewire.intelligence.overview', [
            'clients' => Client::where('organization_id', $orgId)
                ->with(['latestHealthScore'])
                ->active()
                ->get(),
            'topAlerts' => PerformanceAnomaly::where('organization_id', $orgId)
                ->unresolved()
                ->orderByRaw("CASE severity WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 ELSE 5 END")
                ->limit(5)
                ->get(),
            'recentInsights' => AiInsight::where('organization_id', $orgId)
                ->active()
                ->latest('insight_date')
                ->limit(3)
                ->get(),
            'lastSync' => $lastSync,
            'activeAlertsCount' => $activeAlertsCount,
            'aiModel' => config('intelligence.llm_model', 'Gemini 1.5 Flash'),
        ])->layout('layouts.app');
    }
}
