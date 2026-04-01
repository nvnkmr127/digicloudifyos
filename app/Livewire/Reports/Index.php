<?php

namespace App\Livewire\Reports;

use App\Models\Invoice;
use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public $reportType = 'financial';
    public $dateRange = 'this_month';

    public function render()
    {
        $orgId = Auth::user()->organization_id;
        
        $data = [
            'financial' => [
                'total_invoiced' => Invoice::where('organization_id', $orgId)->sum('total_amount'),
                'total_paid' => Invoice::where('organization_id', $orgId)->where('status', 'paid')->sum('total_amount'),
                'pending_amount' => Invoice::where('organization_id', $orgId)->where('status', '!=', 'paid')->sum('total_amount'),
            ],
            'performance' => [
                'active_projects' => Project::where('organization_id', $orgId)->where('status', 'active')->count(),
                'completed_projects' => Project::where('organization_id', $orgId)->where('status', 'completed')->count(),
                'total_hours' => TimeEntry::where('organization_id', $orgId)->sum('hours'),
            ],
            'clients' => [
                'total_clients' => Client::where('organization_id', $orgId)->count(),
                'new_clients_this_month' => Client::where('organization_id', $orgId)->whereMonth('created_at', now()->month)->count(),
            ]
        ];

        return view('livewire.reports.index', [
            'reportData' => $data
        ])->layout('layouts.app');
    }
}
