<?php

namespace App\Livewire\TimeTracking;

use Livewire\Component;

class Index extends Component
{
    public $activeTimer = null;
    public $projectId = '';
    public $description = '';

    public function mount()
    {
        $this->activeTimer = \App\Models\TimeEntry::where('organization_id', \Illuminate\Support\Facades\Auth::user()->organization_id)
            ->whereNull('end_at')
            ->first();
    }

    public function startTimer()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $employee = \App\Models\Employee::where('user_id', $user->id)->first();

        if (!$employee) return;

        $this->activeTimer = \App\Models\TimeEntry::create([
            'organization_id' => $user->organization_id,
            'employee_id' => $employee->id,
            'project_id' => $this->projectId ?: null,
            'description' => $this->description,
            'date' => now()->toDateString(),
            'start_at' => now(),
            'billable' => true,
        ]);

        $this->description = '';
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
        $orgId = \Illuminate\Support\Facades\Auth::user()->organization_id;
        $timeEntries = \App\Models\TimeEntry::where('organization_id', $orgId)
            ->with(['employee.user', 'project'])
            ->whereNotNull('end_at')
            ->orderByDesc('date')
            ->orderByDesc('end_at')
            ->get();
        $projects = \App\Models\Project::where('organization_id', $orgId)->get();

        $totalHours = $timeEntries->sum('hours');
        $billableHours = $timeEntries->where('billable', true)->sum('hours');
        $billableRatio = $totalHours > 0 ? round(($billableHours / $totalHours) * 100) : 0;

        return view('livewire.time-tracking.index', [
            'timeEntries' => $timeEntries,
            'totalHours' => $totalHours,
            'billableRatio' => $billableRatio,
            'projects' => $projects,
        ]);
    }
}
