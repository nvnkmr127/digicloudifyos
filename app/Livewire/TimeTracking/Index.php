<?php

namespace App\Livewire\TimeTracking;

use App\Models\Employee;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public $activeTimer = null;

    public $projectId = '';

    public $description = '';

    public $taskId = '';

    public function mount()
    {
        $this->activeTimer = TimeEntry::where('organization_id', Auth::user()->organization_id)
            ->whereNull('end_at')
            ->first();
    }

    public function startTimer()
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (! $employee) {
            return;
        }

        $this->activeTimer = TimeEntry::create([
            'organization_id' => $user->organization_id,
            'employee_id' => $employee->id,
            'project_id' => $this->projectId ?: null,
            'task_id' => $this->taskId ?: null,
            'description' => $this->description,
            'date' => now()->toDateString(),
            'start_at' => now(),
            'billable' => true,
        ]);

        $this->description = '';
        $this->taskId = '';
    }

    public function startFromTask(string $taskId): void
    {
        if ($this->activeTimer) {
            return;
        }

        $task = Task::with('project')->find($taskId);
        if (! $task) {
            return;
        }

        $this->projectId = $task->project_id ?: '';
        $this->taskId = $task->id;
        $this->description = $task->title;

        $this->startTimer();
    }

    public function stopTimer()
    {
        if ($this->activeTimer) {
            $endAt = now();
            $startAt = $this->activeTimer->start_at;
            $hours = round($endAt->diffInMinutes($startAt) / 60, 2);

            $this->activeTimer->update([
                'end_at' => $endAt,
                'hours' => $hours,
            ]);

            $this->activeTimer = null;
        }
    }

    public function render()
    {
        $user = Auth::user();
        $orgId = $user->organization_id;
        $timeEntries = TimeEntry::where('organization_id', $orgId)
            ->with(['employee.user', 'project'])
            ->whereNotNull('end_at')
            ->orderByDesc('date')
            ->orderByDesc('end_at')
            ->get();
        $projects = Project::where('organization_id', $orgId)->get();

        $employee = Employee::where('user_id', $user->id)->first();
        $suggestions = collect();

        if ($employee) {
            $suggestions = Task::where('organization_id', $orgId)
                ->where('assigned_to', $employee->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->orderByRaw("FIELD(status, 'in_progress', 'pending')")
                ->orderByDesc('updated_at')
                ->limit(8)
                ->get();
        }

        $totalHours = $timeEntries->sum('hours');
        $billableHours = $timeEntries->where('billable', true)->sum('hours');
        $billableRatio = $totalHours > 0 ? round(($billableHours / $totalHours) * 100) : 0;

        return view('livewire.time-tracking.index', [
            'timeEntries' => $timeEntries,
            'totalHours' => $totalHours,
            'billableRatio' => $billableRatio,
            'projects' => $projects,
            'suggestions' => $suggestions,
        ]);
    }
}
