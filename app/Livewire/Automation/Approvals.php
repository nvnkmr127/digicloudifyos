<?php

namespace App\Livewire\Automation;

use App\Models\AutomationAction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Approvals extends Component
{
    public array $selected = [];

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
            session()->flash('error', 'Select at least one action.');

            return;
        }

        AutomationAction::where('organization_id', $user->organization_id)
            ->whereIn('id', $this->selected)
            ->where('status', 'proposed')
            ->update([
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

        $this->selected = [];
        session()->flash('success', 'Approved.');
    }

    public function rejectSelected(): void
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }
        if (! $user->can('manage-workflow')) {
            abort(403);
        }

        if (empty($this->selected)) {
            session()->flash('error', 'Select at least one action.');

            return;
        }

        AutomationAction::where('organization_id', $user->organization_id)
            ->whereIn('id', $this->selected)
            ->where('status', 'proposed')
            ->update([
                'status' => 'rejected',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

        $this->selected = [];
        session()->flash('success', 'Rejected.');
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

        $actions = AutomationAction::where('organization_id', $user->organization_id)
            ->whereIn('status', ['proposed', 'approved', 'failed'])
            ->with(['client', 'campaign', 'rule'])
            ->orderByRaw("FIELD(status, 'proposed', 'failed', 'approved')")
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        return view('livewire.automation.approvals', [
            'actions' => $actions,
        ]);
    }
}
