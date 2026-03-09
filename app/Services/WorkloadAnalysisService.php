<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\WorkloadEntry;
use Illuminate\Support\Facades\Cache;

class WorkloadAnalysisService
{
    public function getTeamWorkload(string $organizationId, ?string $period = 'current_week'): array
    {
        $cacheKey = "workload.team.{$organizationId}.{$period}";

        return Cache::remember($cacheKey, 300, function () use ($organizationId, $period) {
            [$startDate, $endDate] = $this->getPeriodDates($period);

            return [
                'employees' => $this->getEmployeeWorkload($organizationId, $startDate, $endDate),
                'projects' => $this->getProjectWorkload($organizationId, $startDate, $endDate),
                'department_breakdown' => $this->getDepartmentWorkload($organizationId, $startDate, $endDate),
                'capacity_analysis' => $this->getCapacityAnalysis($organizationId, $startDate, $endDate),
                'overallocation_warnings' => $this->getOverallocationWarnings($organizationId, $startDate, $endDate),
            ];
        });
    }

    protected function getEmployeeWorkload(string $organizationId, $startDate, $endDate): array
    {
        $employees = Employee::where('organization_id', $organizationId)
            ->active()
            ->with(['workloadEntries' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate]);
            }])
            ->get();

        return $employees->map(function ($employee) use ($startDate, $endDate) {
            $allocatedHours = $employee->workloadEntries->sum('allocated_hours');
            $actualHours = $employee->timeEntries()
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('hours');

            $availableHours = $this->getAvailableHours($employee, $startDate, $endDate);
            $utilizationRate = $availableHours > 0 ? ($allocatedHours / $availableHours) * 100 : 0;

            return [
                'employee_id' => $employee->id,
                'name' => $employee->full_name,
                'department' => $employee->department,
                'position' => $employee->position,
                'available_hours' => round($availableHours, 2),
                'allocated_hours' => round($allocatedHours, 2),
                'actual_hours' => round($actualHours, 2),
                'utilization_rate' => round($utilizationRate, 2),
                'is_overallocated' => $utilizationRate > 100,
                'capacity_status' => $this->getCapacityStatus($utilizationRate),
                'active_projects' => $employee->projectAssignments()->active()->count(),
            ];
        })->toArray();
    }

    protected function getProjectWorkload(string $organizationId, $startDate, $endDate): array
    {
        $projects = Project::where('organization_id', $organizationId)
            ->active()
            ->with(['workloadEntries', 'timeEntries', 'assignments'])
            ->get();

        return $projects->map(function ($project) use ($startDate, $endDate) {
            $allocatedHours = $project->workloadEntries()
                ->whereBetween('start_date', [$startDate, $endDate])
                ->sum('allocated_hours');

            $actualHours = $project->timeEntries()
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('hours');

            $teamSize = $project->assignments()->active()->count();

            return [
                'project_id' => $project->id,
                'name' => $project->name,
                'client' => $project->client->name ?? 'N/A',
                'status' => $project->status,
                'team_size' => $teamSize,
                'allocated_hours' => round($allocatedHours, 2),
                'actual_hours' => round($actualHours, 2),
                'budget_utilization' => round($project->getBudgetUtilization(), 2),
                'health_status' => $project->health_status,
                'is_on_track' => $actualHours <= $allocatedHours,
            ];
        })->toArray();
    }

    protected function getDepartmentWorkload(string $organizationId, $startDate, $endDate): array
    {
        $departments = Employee::where('organization_id', $organizationId)
            ->active()
            ->select('department')
            ->distinct()
            ->pluck('department');

        return $departments->map(function ($department) use ($organizationId, $startDate, $endDate) {
            $employees = Employee::where('organization_id', $organizationId)
                ->byDepartment($department)
                ->active()
                ->get();

            $totalAllocated = WorkloadEntry::whereIn('employee_id', $employees->pluck('id'))
                ->whereBetween('start_date', [$startDate, $endDate])
                ->sum('allocated_hours');

            $totalActual = TimeEntry::whereIn('employee_id', $employees->pluck('id'))
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('hours');

            $totalAvailable = $employees->sum('work_hours_per_week') *
                $this->getWeeksInPeriod($startDate, $endDate);

            return [
                'department' => $department,
                'employee_count' => $employees->count(),
                'total_available_hours' => round($totalAvailable, 2),
                'total_allocated_hours' => round($totalAllocated, 2),
                'total_actual_hours' => round($totalActual, 2),
                'utilization_rate' => $totalAvailable > 0
                    ? round(($totalAllocated / $totalAvailable) * 100, 2)
                    : 0,
            ];
        })->toArray();
    }

    protected function getCapacityAnalysis(string $organizationId, $startDate, $endDate): array
    {
        $employees = Employee::where('organization_id', $organizationId)
            ->active()
            ->get();

        $totalCapacity = $employees->sum('work_hours_per_week') *
            $this->getWeeksInPeriod($startDate, $endDate);

        $totalAllocated = WorkloadEntry::where('organization_id', $organizationId)
            ->whereBetween('start_date', [$startDate, $endDate])
            ->sum('allocated_hours');

        $totalActual = TimeEntry::where('organization_id', $organizationId)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('hours');

        $availableCapacity = $totalCapacity - $totalAllocated;

        return [
            'total_capacity' => round($totalCapacity, 2),
            'total_allocated' => round($totalAllocated, 2),
            'total_actual' => round($totalActual, 2),
            'available_capacity' => round($availableCapacity, 2),
            'utilization_rate' => $totalCapacity > 0
                ? round(($totalAllocated / $totalCapacity) * 100, 2)
                : 0,
            'efficiency_rate' => $totalAllocated > 0
                ? round(($totalActual / $totalAllocated) * 100, 2)
                : 0,
        ];
    }

    protected function getOverallocationWarnings(string $organizationId, $startDate, $endDate): array
    {
        $overallocated = Employee::where('organization_id', $organizationId)
            ->active()
            ->get()
            ->filter(function ($employee) use ($startDate, $endDate) {
                $allocated = $employee->workloadEntries()
                    ->whereBetween('start_date', [$startDate, $endDate])
                    ->sum('allocated_hours');

                $available = $this->getAvailableHours($employee, $startDate, $endDate);

                return $allocated > $available;
            })
            ->map(function ($employee) use ($startDate, $endDate) {
                $allocated = $employee->workloadEntries()
                    ->whereBetween('start_date', [$startDate, $endDate])
                    ->sum('allocated_hours');

                $available = $this->getAvailableHours($employee, $startDate, $endDate);
                $overallocation = $allocated - $available;

                return [
                    'employee_id' => $employee->id,
                    'name' => $employee->full_name,
                    'department' => $employee->department,
                    'available_hours' => round($available, 2),
                    'allocated_hours' => round($allocated, 2),
                    'overallocation_hours' => round($overallocation, 2),
                    'overallocation_percentage' => round(($overallocation / $available) * 100, 2),
                ];
            })
            ->values()
            ->toArray();

        return $overallocated;
    }

    protected function getAvailableHours(Employee $employee, $startDate, $endDate): float
    {
        $weeks = $this->getWeeksInPeriod($startDate, $endDate);

        return $employee->work_hours_per_week * $weeks;
    }

    protected function getWeeksInPeriod($startDate, $endDate): float
    {
        $days = $startDate->diffInDays($endDate);

        return $days / 7;
    }

    protected function getCapacityStatus(float $utilizationRate): string
    {
        if ($utilizationRate >= 100) {
            return 'overallocated';
        } elseif ($utilizationRate >= 85) {
            return 'at_capacity';
        } elseif ($utilizationRate >= 60) {
            return 'optimal';
        } elseif ($utilizationRate >= 40) {
            return 'under_utilized';
        } else {
            return 'low_utilization';
        }
    }

    protected function getPeriodDates(string $period): array
    {
        return match ($period) {
            'current_week' => [now()->startOfWeek(), now()->endOfWeek()],
            'next_week' => [now()->addWeek()->startOfWeek(), now()->addWeek()->endOfWeek()],
            'current_month' => [now()->startOfMonth(), now()->endOfMonth()],
            'next_month' => [now()->addMonth()->startOfMonth(), now()->addMonth()->endOfMonth()],
            'current_quarter' => [now()->startOfQuarter(), now()->endOfQuarter()],
            default => [now()->startOfWeek(), now()->endOfWeek()],
        };
    }

    public function clearCache(string $organizationId): void
    {
        $periods = ['current_week', 'next_week', 'current_month', 'next_month', 'current_quarter'];

        foreach ($periods as $period) {
            Cache::forget("workload.team.{$organizationId}.{$period}");
        }
    }
}
