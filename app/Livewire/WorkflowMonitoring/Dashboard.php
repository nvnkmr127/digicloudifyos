<?php

namespace App\Livewire\WorkflowMonitoring;

use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $organizationId = \Illuminate\Support\Facades\Auth::user()->organization_id;

        $rules = \App\Models\WorkflowRule::where('organization_id', $organizationId)
            ->withCount(['logs'])
            ->get();

        $recentLogs = \App\Models\AutomationLog::where('organization_id', $organizationId)
            ->with(['rule'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('livewire.workflow-monitoring.dashboard', [
            'rules' => $rules,
            'recentLogs' => $recentLogs
        ])->layout('layouts.app');
    }
}
