<?php

namespace App\Livewire\Automations;

use App\Models\WorkflowAction;
use App\Models\WorkflowRule;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Builder extends Component
{
    public $name = '';

    public $description = '';

    public $event_type = 'lead_created';

    public $is_active = true;

    public $conditions = [];

    public $actions = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'event_type' => 'required',
    ];

    public function mount($id = null)
    {
        if ($id) {
            $rule = WorkflowRule::with('actions')->findOrFail($id);
            $this->name = $rule->name;
            $this->description = $rule->description;
            $this->event_type = $rule->event_type;
            $this->is_active = $rule->is_active;
            $this->conditions = $rule->conditions ?? [];

            foreach ($rule->actions as $action) {
                $this->actions[] = [
                    'id' => $action->id,
                    'type' => $action->action_type,
                    'config' => $action->config,
                ];
            }
        } else {
            // Default first action
            $this->addAction();
        }
    }

    public function addAction()
    {
        $this->actions[] = [
            'type' => 'send_notification',
            'config' => ['message' => 'New automation action template notification'],
        ];
    }

    public function removeAction($index)
    {
        unset($this->actions[$index]);
        $this->actions = array_values($this->actions);
    }

    public function addCondition()
    {
        $this->conditions[] = [
            'field' => 'status',
            'operator' => '=',
            'value' => '',
        ];
    }

    public function removeCondition($index)
    {
        unset($this->conditions[$index]);
        $this->conditions = array_values($this->conditions);
    }

    public function save()
    {
        $this->validate();

        $rule = WorkflowRule::updateOrCreate(
            ['name' => $this->name, 'organization_id' => Auth::user()->organization_id],
            [
                'description' => $this->description,
                'event_type' => $this->event_type,
                'is_active' => $this->is_active,
                'conditions' => $this->conditions,
            ]
        );

        // Simple sync: delete old and create new (MVP style)
        $rule->actions()->delete();

        foreach ($this->actions as $actionData) {
            WorkflowAction::create([
                'workflow_rule_id' => $rule->id,
                'action_type' => $actionData['type'],
                'config' => $actionData['config'],
            ]);
        }

        session()->flash('message', 'Automation rule saved successfully.');

        return redirect()->route('automations.index');
    }

    public function render()
    {
        $eventTypes = [
            'lead_created' => 'Lead Created',
            'proposal_sent' => 'Proposal Sent',
            'proposal_accepted' => 'Proposal Accepted',
            'invoice_paid' => 'Invoice Paid',
            'invoice_overdue' => 'Invoice Overdue',
            'campaign_status_changed' => 'Campaign Status Changed',
            'ads_budget_warning' => 'Ads Budget Warning',
            'task_completed' => 'Task Completed',
        ];

        $actionTypes = [
            'send_notification' => 'Send In-App Notification',
            'send_email' => 'Send Email',
            'create_task' => 'Create Task',
            'update_status' => 'Update Entity Status',
        ];

        return view('livewire.automations.builder', [
            'eventTypes' => $eventTypes,
            'actionTypes' => $actionTypes,
        ]);
    }
}
