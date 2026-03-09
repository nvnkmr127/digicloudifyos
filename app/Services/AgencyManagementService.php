<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\TimeEntry;
use Illuminate\Support\Facades\Cache;

class AgencyManagementService
{
    public function getAgencyDashboard(string $organizationId): array
    {
        $cacheKey = "agency.dashboard.{$organizationId}";

        return Cache::remember($cacheKey, 600, function () use ($organizationId) {
            return [
                'overview' => $this->getOverview($organizationId),
                'financial' => $this->getFinancialMetrics($organizationId),
                'team' => $this->getTeamMetrics($organizationId),
                'clients' => $this->getClientMetrics($organizationId),
                'projects' => $this->getProjectMetrics($organizationId),
                'productivity' => $this->getProductivityMetrics($organizationId),
            ];
        });
    }

    protected function getOverview(string $organizationId): array
    {
        return [
            'active_projects' => Project::where('organization_id', $organizationId)
                ->active()
                ->count(),
            'active_clients' => Client::where('organization_id', $organizationId)
                ->where('status', 'ACTIVE')
                ->count(),
            'team_size' => Employee::where('organization_id', $organizationId)
                ->active()
                ->count(),
            'pending_invoices' => Invoice::where('organization_id', $organizationId)
                ->where('status', 'pending')
                ->count(),
            'overdue_invoices' => Invoice::where('organization_id', $organizationId)
                ->where('status', '!=', 'paid')
                ->where('due_date', '<', now())
                ->count(),
        ];
    }

    protected function getFinancialMetrics(string $organizationId): array
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $monthlyRevenue = Invoice::where('organization_id', $organizationId)
            ->whereYear('issue_date', $currentYear)
            ->whereMonth('issue_date', $currentMonth)
            ->where('status', 'paid')
            ->sum('total_amount');

        $monthlyPending = Invoice::where('organization_id', $organizationId)
            ->whereYear('issue_date', $currentYear)
            ->whereMonth('issue_date', $currentMonth)
            ->where('status', '!=', 'paid')
            ->sum('total_amount');

        $yearlyRevenue = Invoice::where('organization_id', $organizationId)
            ->whereYear('issue_date', $currentYear)
            ->where('status', 'paid')
            ->sum('total_amount');

        $projectCosts = Project::where('organization_id', $organizationId)
            ->active()
            ->sum('actual_cost');

        return [
            'monthly_revenue' => round($monthlyRevenue, 2),
            'monthly_pending' => round($monthlyPending, 2),
            'yearly_revenue' => round($yearlyRevenue, 2),
            'total_project_costs' => round($projectCosts, 2),
            'profit_margin' => $monthlyRevenue > 0
                ? round((($monthlyRevenue - $projectCosts) / $monthlyRevenue) * 100, 2)
                : 0,
            'average_project_value' => $this->getAverageProjectValue($organizationId),
        ];
    }

    protected function getTeamMetrics(string $organizationId): array
    {
        $employees = Employee::where('organization_id', $organizationId)
            ->active()
            ->get();

        $currentWeekStart = now()->startOfWeek();
        $currentWeekEnd = now()->endOfWeek();

        $totalHoursThisWeek = TimeEntry::where('organization_id', $organizationId)
            ->whereBetween('date', [$currentWeekStart, $currentWeekEnd])
            ->sum('hours');

        $billableHours = TimeEntry::where('organization_id', $organizationId)
            ->whereBetween('date', [$currentWeekStart, $currentWeekEnd])
            ->billable()
            ->sum('hours');

        return [
            'total_employees' => $employees->count(),
            'by_department' => $employees->groupBy('department')->map->count(),
            'total_hours_this_week' => round($totalHoursThisWeek, 2),
            'billable_hours_this_week' => round($billableHours, 2),
            'billable_ratio' => $totalHoursThisWeek > 0
                ? round(($billableHours / $totalHoursThisWeek) * 100, 2)
                : 0,
            'average_utilization' => round($employees->avg(function ($emp) {
                return $emp->getUtilizationRate();
            }), 2),
        ];
    }

    protected function getClientMetrics(string $organizationId): array
    {
        $clients = Client::where('organization_id', $organizationId)->get();
        $activeClients = $clients->where('status', 'ACTIVE');

        return [
            'total_clients' => $clients->count(),
            'active_clients' => $activeClients->count(),
            'clients_with_active_projects' => Client::where('organization_id', $organizationId)
                ->whereHas('campaigns', function ($q) {
                    $q->where('status', 'running');
                })
                ->count(),
            'new_clients_this_month' => Client::where('organization_id', $organizationId)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'client_retention_rate' => $this->calculateRetentionRate($organizationId),
        ];
    }

    protected function getProjectMetrics(string $organizationId): array
    {
        $projects = Project::where('organization_id', $organizationId)->get();
        $activeProjects = $projects->where('status', 'active');

        $onTrack = $activeProjects->filter(function ($project) {
            return $project->health_status === 'on_track';
        })->count();

        $atRisk = $activeProjects->filter(function ($project) {
            return in_array($project->health_status, ['at_risk', 'critical']);
        })->count();

        return [
            'total_projects' => $projects->count(),
            'active_projects' => $activeProjects->count(),
            'on_track' => $onTrack,
            'at_risk' => $atRisk,
            'average_project_duration' => $this->getAverageProjectDuration($organizationId),
            'completion_rate' => $this->getProjectCompletionRate($organizationId),
        ];
    }

    protected function getProductivityMetrics(string $organizationId): array
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $totalHours = TimeEntry::where('organization_id', $organizationId)
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->sum('hours');

        $billableHours = TimeEntry::where('organization_id', $organizationId)
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->billable()
            ->sum('hours');

        $revenue = Invoice::where('organization_id', $organizationId)
            ->whereYear('issue_date', $currentYear)
            ->whereMonth('issue_date', $currentMonth)
            ->where('status', 'paid')
            ->sum('total_amount');

        return [
            'total_hours_this_month' => round($totalHours, 2),
            'billable_hours_this_month' => round($billableHours, 2),
            'revenue_per_hour' => $billableHours > 0 ? round($revenue / $billableHours, 2) : 0,
            'billable_utilization' => $totalHours > 0
                ? round(($billableHours / $totalHours) * 100, 2)
                : 0,
        ];
    }

    protected function getAverageProjectValue(string $organizationId): float
    {
        return round(Project::where('organization_id', $organizationId)
            ->avg('budget'), 2);
    }

    protected function calculateRetentionRate(string $organizationId): float
    {
        $startOfYear = now()->startOfYear();
        $clientsStartYear = Client::where('organization_id', $organizationId)
            ->where('created_at', '<', $startOfYear)
            ->count();

        if ($clientsStartYear == 0) {
            return 0;
        }

        $retainedClients = Client::where('organization_id', $organizationId)
            ->where('created_at', '<', $startOfYear)
            ->where('status', 'ACTIVE')
            ->count();

        return round(($retainedClients / $clientsStartYear) * 100, 2);
    }

    protected function getAverageProjectDuration(string $organizationId): int
    {
        $avgDays = Project::where('organization_id', $organizationId)
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->get()
            ->avg(function ($project) {
                return $project->start_date->diffInDays($project->end_date);
            });

        return round($avgDays ?? 0);
    }

    protected function getProjectCompletionRate(string $organizationId): float
    {
        $total = Project::where('organization_id', $organizationId)->count();

        if ($total == 0) {
            return 0;
        }

        $completed = Project::where('organization_id', $organizationId)
            ->where('status', 'completed')
            ->count();

        return round(($completed / $total) * 100, 2);
    }

    public function clearCache(string $organizationId): void
    {
        Cache::forget("agency.dashboard.{$organizationId}");
    }
}
