<?php

namespace App\Livewire\Productivity;

use App\Models\ProductivityDailySummary;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public string $date;

    public function mount(): void
    {
        $this->date = now()->subDay()->toDateString();
    }

    public function render()
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }

        $rows = ProductivityDailySummary::where('organization_id', $user->organization_id)
            ->whereDate('summary_date', $this->date)
            ->with('employee')
            ->orderByDesc('utilization_rate')
            ->get();

        $totals = [
            'hours_tracked' => (float) $rows->sum('hours_tracked'),
            'billable_hours' => (float) $rows->sum('billable_hours'),
            'tasks_completed' => (int) $rows->sum('tasks_completed'),
            'overdue_tasks' => (int) $rows->sum('overdue_tasks'),
        ];

        $totals['billable_ratio'] = $totals['hours_tracked'] > 0
            ? ($totals['billable_hours'] / $totals['hours_tracked']) * 100
            : 0;

        return view('livewire.productivity.index', [
            'rows' => $rows,
            'totals' => $totals,
        ]);
    }
}
