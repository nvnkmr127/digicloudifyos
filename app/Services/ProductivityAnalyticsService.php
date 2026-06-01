<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\ProductivityDailySummary;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\WorkloadEntry;
use Carbon\Carbon;

class ProductivityAnalyticsService
{
    public function computeForOrganization(string $orgId, string $date): void
    {
        $dayStart = Carbon::parse($date)->startOfDay();
        $dayEnd = Carbon::parse($date)->endOfDay();
        $dateStr = $dayStart->toDateString();

        // Performance: Aggregate all time entries for the org/day in one query (D008)
        $timeData = TimeEntry::where('organization_id', $orgId)
            ->whereBetween('date', [$dateStr, $dateStr])
            ->selectRaw('employee_id, SUM(hours) as total, SUM(CASE WHEN billable = 1 THEN hours ELSE 0 END) as billable')
            ->groupBy('employee_id')
            ->get()
            ->keyBy('employee_id');

        // Performance: Aggregate all completed tasks
        $taskData = Task::where('organization_id', $orgId)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$dayStart, $dayEnd])
            ->selectRaw('assigned_to as employee_id, COUNT(*) as count, AVG(DATEDIFF(completed_at, created_at)) as avg_cycle')
            ->groupBy('assigned_to')
            ->get()
            ->keyBy('employee_id');

        // Performance: Aggregate overdue tasks
        $overdueData = Task::where('organization_id', $orgId)
            ->where('status', '!=', 'completed')
            ->whereNotNull('deadline')
            ->where('deadline', '<', $dayEnd)
            ->selectRaw('assigned_to as employee_id, COUNT(*) as count')
            ->groupBy('assigned_to')
            ->get()
            ->keyBy('employee_id');

        // Performance: Aggregate workload allocations
        $workloadData = WorkloadEntry::where('organization_id', $orgId)
            ->whereDate('start_date', '<=', $dateStr)
            ->whereDate('end_date', '>=', $dateStr)
            ->selectRaw('employee_id, SUM(allocated_hours) as total')
            ->groupBy('employee_id')
            ->get()
            ->keyBy('employee_id');

        $employees = Employee::where('organization_id', $orgId)->active()->get();

        foreach ($employees as $employee) {
            $id = $employee->id;

            $t = $timeData->get($id);
            $hoursTracked = (float) ($t?->total ?? 0);
            $billableHours = (float) ($t?->billable ?? 0);

            $taskInfo = $taskData->get($id);
            $overdueCount = (int) ($overdueData->get($id)?->count ?? 0);
            $allocated = (float) ($workloadData->get($id)?->total ?? 0);

            $billableRatio = $hoursTracked > 0 ? ($billableHours / $hoursTracked) * 100 : 0;
            $utilization = $allocated > 0 ? ($hoursTracked / $allocated) * 100 : 0;

            ProductivityDailySummary::updateOrCreate(
                [
                    'organization_id' => $orgId,
                    'employee_id' => $id,
                    'summary_date' => $dateStr,
                ],
                [
                    'hours_tracked' => round($hoursTracked, 2),
                    'billable_hours' => round($billableHours, 2),
                    'billable_ratio' => round($billableRatio, 2),
                    'tasks_completed' => (int) ($taskInfo?->count ?? 0),
                    'avg_task_cycle_days' => round((float) ($taskInfo?->avg_cycle ?? 0), 2),
                    'overdue_tasks' => $overdueCount,
                    'allocated_hours' => round($allocated, 2),
                    'utilization_rate' => round($utilization, 2),
                ]
            );
        }
    }

    public function computeForEmployee(string $orgId, string $employeeId, string $date): void
    {
        // Keep for individual triggers, but the organization-level call is now optimized
        $dayStart = Carbon::parse($date)->startOfDay();
        $dayEnd = Carbon::parse($date)->endOfDay();
        $dateStr = $dayStart->toDateString();

        $hoursTracked = (float) TimeEntry::where('organization_id', $orgId)
            ->where('employee_id', $employeeId)
            ->whereDate('date', $dateStr)
            ->sum('hours');

        $billableHours = (float) TimeEntry::where('organization_id', $orgId)
            ->where('employee_id', $employeeId)
            ->whereDate('date', $dateStr)
            ->where('billable', true)
            ->sum('hours');

        $tasksCompleted = Task::where('organization_id', $orgId)
            ->where('assigned_to', $employeeId)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$dayStart, $dayEnd])
            ->count();

        $avgCycle = (float) Task::where('organization_id', $orgId)
            ->where('assigned_to', $employeeId)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$dayStart, $dayEnd])
            ->selectRaw('AVG(DATEDIFF(completed_at, created_at)) as avg_cycle')
            ->value('avg_cycle');

        $overdueCount = Task::where('organization_id', $orgId)
            ->where('assigned_to', $employeeId)
            ->where('status', '!=', 'completed')
            ->whereNotNull('deadline')
            ->where('deadline', '<', $dayEnd)
            ->count();

        $allocated = (float) WorkloadEntry::where('organization_id', $orgId)
            ->where('employee_id', $employeeId)
            ->whereDate('start_date', '<=', $dateStr)
            ->whereDate('end_date', '>=', $dateStr)
            ->sum('allocated_hours');

        $billableRatio = $hoursTracked > 0 ? ($billableHours / $hoursTracked) * 100 : 0;
        $utilization = $allocated > 0 ? ($hoursTracked / $allocated) * 100 : 0;

        ProductivityDailySummary::updateOrCreate(
            [
                'organization_id' => $orgId,
                'employee_id' => $employeeId,
                'summary_date' => $dateStr,
            ],
            [
                'hours_tracked' => round($hoursTracked, 2),
                'billable_hours' => round($billableHours, 2),
                'billable_ratio' => round($billableRatio, 2),
                'tasks_completed' => $tasksCompleted,
                'avg_task_cycle_days' => round($avgCycle, 2),
                'overdue_tasks' => $overdueCount,
                'allocated_hours' => round($allocated, 2),
                'utilization_rate' => round($utilization, 2),
            ]
        );
    }
}
