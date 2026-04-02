<?php

namespace App\Livewire\Team;

use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public function removeMember($id)
    {
        $employee = Employee::find($id);
        if ($employee) {
            $employee->delete();
        }
    }

    public function render()
    {
        $orgId = Auth::user()->organization_id;
        $employees = Employee::where('organization_id', $orgId)
            ->with(['user'])
            ->get();

        return view('livewire.team.index', [
            'employees' => $employees,
        ]);
    }
}
