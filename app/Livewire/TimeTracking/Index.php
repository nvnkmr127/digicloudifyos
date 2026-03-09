<?php

namespace App\Livewire\TimeTracking;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        $timeEntries = \App\Models\TimeEntry::with(['employee.user', 'project'])->orderByDesc('date')->get();
        $totalHours = $timeEntries->sum('hours');

        $billableHours = $timeEntries->where('billable', true)->sum('hours');
        $billableRatio = $totalHours > 0 ? round(($billableHours / $totalHours) * 100) : 0;

        return view('livewire.time-tracking.index', [
            'timeEntries' => $timeEntries,
            'totalHours' => $totalHours,
            'billableRatio' => $billableRatio
        ]);
    }
}
