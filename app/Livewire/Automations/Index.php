<?php

namespace App\Livewire\Automations;

use Livewire\Component;

class Index extends Component
{
    public function toggleRule($ruleId)
    {
        $rule = \App\Models\WorkflowRule::find($ruleId);
        if ($rule) {
            $rule->update(['is_active' => !$rule->is_active]);
        }
    }

    public function render()
    {
        $rules = \App\Models\WorkflowRule::with('actions')
            ->latest()
            ->get();

        return view('livewire.automations.index', [
            'rules' => $rules
        ]);
    }
}
