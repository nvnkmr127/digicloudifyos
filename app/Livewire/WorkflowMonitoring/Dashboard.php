<?php

namespace App\Livewire\WorkflowMonitoring;

use App\Models\AutomationLog;
use App\Models\WorkflowRule;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $organizationId = Auth::user()->organization_id;

        $rules = WorkflowRule::where('organization_id', $organizationId)
            ->withCount(['logs'])
            ->get();

        $recentLogs = AutomationLog::where('organization_id', $organizationId)
            ->with(['rule'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('livewire.workflow-monitoring.dashboard', [
            'rules' => $rules,
            'recentLogs' => $recentLogs,
        ])->layout('layouts.app');
    }
}
