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
        $employees = Employee::where('organization_id', $orgId)->active()->get();

        foreach ($employees as $employee) {
            $this->computeForEmployee($orgId, $employee->id, $date);
        }
    }

    public function computeForEmployee(string $orgId, string $employeeId, string $date): void
    {
        $dayStart = Carbon::parse($date)->startOfDay();
        $dayEnd = Carbon::parse($date)->endOfDay();

        $entries = TimeEntry::where('organization_id', $orgId)
            ->where('employee_id', $employeeId)
            ->whereBetween('date', [$dayStart->toDateString(), $dayEnd->toDateString()])
            ->get();

        $hoursTracked = (float) $entries->sum('hours');
        $billableHours = (float) $entries->where('billable', true)->sum('hours');
        $billableRatio = $hoursTracked > 0 ? ($billableHours / $hoursTracked) * 100 : 0;

        $tasksCompleted = Task::where('organization_id', $orgId)
            ->where('assigned_to', $employeeId)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$dayStart, $dayEnd])
            ->get();

        $cycleDays = $tasksCompleted->map(function ($t) {
            if (! $t->completed_at) {
                return null;
            }

            return $t->created_at ? $t->created_at->diffInDays($t->completed_at) : null;
        })->filter()->values();

        $avgCycle = $cycleDays->count() > 0 ? ($cycleDays->sum() / $cycleDays->count()) : 0;

        $overdueTasks = Task::where('organization_id', $orgId)
            ->where('assigned_to', $employeeId)
            ->where('status', '!=', 'completed')
            ->whereNotNull('deadline')
            ->where('deadline', '<', $dayEnd)
            ->count();

        $allocatedHours = (float) WorkloadEntry::where('organization_id', $orgId)
            ->where('employee_id', $employeeId)
            ->whereDate('date', $dayStart->toDateString())
            ->sum('allocated_hours');

        $utilization = $allocatedHours > 0 ? ($hoursTracked / $allocatedHours) * 100 : 0;

        ProductivityDailySummary::updateOrCreate(
            [
                'organization_id' => $orgId,
                'employee_id' => $employeeId,
                'summary_date' => $dayStart->toDateString(),
            ],
            [
                'hours_tracked' => round($hoursTracked, 2),
                'billable_hours' => round($billableHours, 2),
                'billable_ratio' => round($billableRatio, 2),
                'tasks_completed' => $tasksCompleted->count(),
                'avg_task_cycle_days' => round($avgCycle, 2),
                'overdue_tasks' => (int) $overdueTasks,
                'allocated_hours' => round($allocatedHours, 2),
                'utilization_rate' => round($utilization, 2),
            ]
        );
    }
}
