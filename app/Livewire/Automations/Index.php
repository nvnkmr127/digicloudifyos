<?php

namespace App\Livewire\Automations;

use App\Models\WorkflowRule;
use Livewire\Component;

class Index extends Component
{
    public function toggleRule($ruleId)
    {
        $rule = WorkflowRule::find($ruleId);
        if ($rule) {
            $rule->update(['is_active' => ! $rule->is_active]);
        }
    }

    public function render()
    {
        $rules = WorkflowRule::with('actions')
            ->latest()
            ->get();

        return view('livewire.automations.index', [
            'rules' => $rules,
        ]);
    }
}
