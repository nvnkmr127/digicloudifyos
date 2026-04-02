<?php

namespace App\Livewire\TimeTracking;

use App\Models\Employee;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Approvals extends Component
{
    public array $selected = [];

    public string $employeeId = '';

    public function toggleSelect(string $id): void
    {
        if (in_array($id, $this->selected, true)) {
            $this->selected = array_values(array_diff($this->selected, [$id]));

            return;
        }

        $this->selected[] = $id;
    }

    public function approveSelected(): void
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }
        if (! $user->can('manage-workflow')) {
            abort(403);
        }

        if (empty($this->selected)) {
            session()->flash('error', 'Select at least one entry.');

            return;
        }

        TimeEntry::where('organization_id', $user->organization_id)
            ->whereIn('id', $this->selected)
            ->update([
                'approved' => true,
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

        $this->selected = [];
        session()->flash('success', 'Approved.');
    }

    public function render()
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }
        if (! $user->can('manage-workflow')) {
            abort(403);
        }

        $query = TimeEntry::where('organization_id', $user->organization_id)
            ->whereNotNull('end_at')
            ->where(function ($q) {
                $q->whereNull('approved')->orWhere('approved', false);
            })
            ->with(['employee.user', 'project', 'task'])
            ->orderByDesc('date')
            ->orderByDesc('end_at');

        if ($this->employeeId !== '') {
            $query->where('employee_id', $this->employeeId);
        }

        $entries = $query->limit(200)->get();

        $employees = Employee::where('organization_id', $user->organization_id)
            ->active()
            ->orderBy('full_name')
            ->get(['id', 'full_name']);

        return view('livewire.time-tracking.approvals', [
            'entries' => $entries,
            'employees' => $employees,
        ]);
    }
}
