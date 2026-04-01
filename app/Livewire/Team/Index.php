<?php

namespace App\Livewire\Team;

use Livewire\Component;

class Index extends Component
{
    public function removeMember($id)
    {
        $employee = \App\Models\Employee::find($id);
        if ($employee) {
            $employee->delete();
        }
    }

    public function render()
    {
        $orgId = \Illuminate\Support\Facades\Auth::user()->organization_id;
        $employees = \App\Models\Employee::where('organization_id', $orgId)
            ->with(['user'])
            ->get();

        return view('livewire.team.index', [
            'employees' => $employees
        ]);
    }
}
