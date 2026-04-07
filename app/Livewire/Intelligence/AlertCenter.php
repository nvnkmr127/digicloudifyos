<?php

namespace App\Livewire\Intelligence;

use App\Models\Client;
use App\Models\PerformanceAnomaly;
use Livewire\Component;

class AlertCenter extends Component
{
    public $sevFilter = 'all';

    public $clientFilter = '';

    public function resolve($anomalyId)
    {
        $anomaly = PerformanceAnomaly::findOrFail($anomalyId);
        $anomaly->resolve();
    }

    public function render()
    {
        $query = PerformanceAnomaly::where('organization_id', auth()->user()->organization_id)
            ->with(['client', 'snapshot'])
            ->unresolved();

        if ($this->sevFilter !== 'all') {
            $query->bySeverity($this->sevFilter);
        }

        if ($this->clientFilter) {
            $query->where('client_id', $this->clientFilter);
        }

        return view('livewire.intelligence.alert-center', [
            'anomalies' => $query->orderByRaw("CASE severity WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 ELSE 5 END")
                ->latest('detected_at')
                ->get(),
            'clients' => Client::where('organization_id', auth()->user()->organization_id)->get(),
        ])->layout('layouts.app');
    }
}
