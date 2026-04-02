<?php

namespace App\Livewire\Dashboard;

use App\Models\AutomationLog;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\CreativeRequest;
use App\Models\DailyBriefing;
use App\Models\FormSubmission;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\PerformanceAnomaly;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        $orgId = Auth::user()->organization_id;

        $stats = [
            'lead_flux' => [
                'total' => Lead::where('organization_id', $orgId)->count(),
                'new_today' => Lead::where('organization_id', $orgId)->whereDate('created_at', now())->count(),
                'high_intent' => Lead::where('organization_id', $orgId)->where('score', '>=', 80)->count(),
            ],
            'creative_nodes' => [
                'pending' => CreativeRequest::where('organization_id', $orgId)->where('status', 'Pending')->count(),
                'urgent' => CreativeRequest::where('organization_id', $orgId)->where('priority', 'Urgent')->count(),
            ],
            'revenue_matrix' => [
                'total_paid' => Invoice::where('organization_id', $orgId)->where('status', 'paid')->sum('total_amount'),
                'pending' => Invoice::where('organization_id', $orgId)->where('status', '!=', 'paid')->sum('total_amount'),
            ],
            'conversion_funnel' => [
                'total_submissions' => FormSubmission::whereHas('form', fn ($q) => $q->where('organization_id', $orgId))->count(),
                'active_campaigns' => Campaign::where('organization_id', $orgId)->count(),
            ],
            'automation_pulse' => [
                'total_runs' => AutomationLog::whereHas('rule', fn ($q) => $q->where('organization_id', $orgId))->count(),
                'recent_runs' => AutomationLog::whereHas('rule', fn ($q) => $q->where('organization_id', $orgId))->with('rule')->latest()->limit(5)->get(),
            ],
            'recent_projects' => Project::with('client')->where('organization_id', $orgId)->latest()->limit(3)->get(),
            'client_health_grid' => Client::with(['latestHealthScore'])
                ->where('organization_id', $orgId)
                ->active()
                ->limit(6)
                ->get(),
            'morning_briefing_preview' => DailyBriefing::with(['actionItems' => fn ($q) => $q->limit(3)->orderBy('sort_order')])
                ->where('organization_id', $orgId)
                ->whereDate('briefing_date', today())
                ->first(),
            'recent_anomalies' => PerformanceAnomaly::with('client')
                ->where('organization_id', $orgId)
                ->unresolved()
                ->orderByRaw("FIELD(severity, 'critical', 'high', 'medium', 'low')")
                ->limit(4)
                ->get(),
        ];

        return view('livewire.dashboard.index', $stats)->layout('layouts.app');
    }
}
