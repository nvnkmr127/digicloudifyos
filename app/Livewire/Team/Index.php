<?php

namespace App\Livewire\Team;

use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public function removeMember($id)
    {
        // D028: Enforce Role-Based Access Control and Tenant Isolation
        $user = Auth::user();
        if (! $user->isAdmin()) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Unauthorized. Only admins can remove members.']);

            return;
        }

        $employee = Employee::where('organization_id', $user->organization_id)
            ->findOrFail($id);

        $employee->delete();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Team member removed.']);
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
