<?php

namespace App\Livewire\Automation;

use App\Models\AutomationRule;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Rules extends Component
{
    public string $name = '';

    public string $channel_type = '';

    public string $trigger_type = 'anomaly';

    public string $anomaly_types = '';

    public string $action_type = 'create_task';

    public bool $requires_approval = true;

    public bool $is_active = true;

    public function save(): void
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }
        if (! $user->can('manage-workflow')) {
            abort(403);
        }

        $this->validate([
            'name' => 'required|string|min:3',
            'channel_type' => 'nullable|string|max:100',
            'trigger_type' => 'required|in:anomaly',
            'anomaly_types' => 'required|string',
            'action_type' => 'required|in:create_task,propose_change',
        ]);

        $types = collect(preg_split("/\r\n|\n|\r|,/", $this->anomaly_types))
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->values()
            ->all();

        AutomationRule::create([
            'organization_id' => $user->organization_id,
            'client_id' => null,
            'campaign_id' => null,
            'name' => $this->name,
            'channel_type' => $this->channel_type !== '' ? $this->channel_type : null,
            'trigger_type' => 'anomaly',
            'trigger_config' => [
                'anomaly_types' => $types,
            ],
            'action_type' => $this->action_type,
            'action_config' => $this->action_type === 'create_task'
                ? [
                    'title' => 'Review: '.$this->name,
                    'priority' => 'high',
                ]
                : [
                    'action_type' => 'pause_meta_campaign',
                ],
            'is_active' => $this->is_active,
            'requires_approval' => $this->requires_approval,
            'created_by' => $user->id,
        ]);

        $this->reset(['name', 'channel_type', 'anomaly_types']);

        session()->flash('success', 'Rule created.');
    }

    public function toggle(string $id): void
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }
        if (! $user->can('manage-workflow')) {
            abort(403);
        }

        $rule = AutomationRule::where('organization_id', $user->organization_id)->find($id);
        if (! $rule) {
            return;
        }

        $rule->update(['is_active' => ! $rule->is_active]);
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

        $rules = AutomationRule::where('organization_id', $user->organization_id)
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.automation.rules', [
            'rules' => $rules,
        ]);
    }
}
