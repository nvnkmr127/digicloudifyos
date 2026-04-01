<?php

namespace App\Livewire\AnalyticsManagement;

use App\Models\Client;
use App\Models\Project;
use App\Models\Invoice;
use App\Models\TimeEntry;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $organizationId = Auth::user()->organization_id;

        // Statistics
        $activeClients = Client::where('organization_id', $organizationId)->where('status', 'ACTIVE')->count();
        $totalProjects = Project::where('organization_id', $organizationId)->count();
        $completedProjects = Project::where('organization_id', $organizationId)->where('status', 'completed')->count();
        
        $totalRevenue = Invoice::where('organization_id', $organizationId)->where('status', 'paid')->sum('total_amount');
        $pendingRevenue = Invoice::where('organization_id', $organizationId)->where('status', '!=', 'paid')->sum('total_amount');

        $teamSize = Employee::where('organization_id', $organizationId)->where('status', 'ACTIVE')->count();

        // Workload logic
        $totalHoursThisMonth = TimeEntry::where('organization_id', $organizationId)
            ->whereMonth('date', now()->month)
            ->sum('hours');

        return view('livewire.analytics-management.dashboard', [
            'stats' => [
                'active_clients' => $activeClients,
                'project_completion' => $totalProjects > 0 ? ($completedProjects / $totalProjects) * 100 : 0,
                'revenue' => $totalRevenue,
                'pending' => $pendingRevenue,
                'team_size' => $teamSize,
                'monthly_hours' => $totalHoursThisMonth,
            ]
        ])->layout('layouts.app');
    }
}
