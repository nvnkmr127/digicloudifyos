<?php

namespace App\Jobs;

use App\Models\AutomationLog;
use App\Models\WorkflowAction;
use App\Models\WorkflowEvent;
use App\Models\WorkflowRule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWorkflowAutomation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 180;

    public function __construct(
        public string $eventType,
        public array $eventData
    ) {
        $this->onQueue('automation');
    }

    public function handle(): void
    {
        try {
            $event = WorkflowEvent::create([
                'organization_id' => $this->eventData['organization_id'] ?? auth()->user()?->organization_id,
                'event_type' => $this->eventType,
                'entity_type' => $this->eventData['entity_type'] ?? 'unknown',
                'entity_id' => $this->eventData['entity_id'] ?? \Illuminate\Support\Str::uuid()->toString(),
                'payload' => $this->eventData,
            ]);

            /** @var \Illuminate\Database\Eloquent\Collection<int, WorkflowRule> $rules */
            $rules = WorkflowRule::where('organization_id', $event->organization_id)
                ->where('event_type', $this->eventType)
                ->where('is_active', true)
                ->with('actions')
                ->get();

            foreach ($rules as $rule) {
                $this->processRule($rule, $event);
            }

        } catch (\Exception $e) {
            Log::error('Workflow automation processing failed', [
                'event_type' => $this->eventType,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function processRule(WorkflowRule $rule, WorkflowEvent $event): void
    {
        try {
            if ($rule->conditions && !$this->evaluateConditions($rule->conditions)) {
                return;
            }

            foreach ($rule->actions as $action) {
                $this->executeAction($action, $rule, $event);
            }

            AutomationLog::create([
                'workflow_rule_id' => $rule->id,
                'event_id' => $event->id,
                'event_type' => $this->eventType,
                'status' => 'success',
                'executed_at' => now(),
                'details' => [
                    'actions_count' => $rule->actions->count(),
                    'event_data' => $this->eventData,
                ],
            ]);

        } catch (\Exception $e) {
            AutomationLog::create([
                'workflow_rule_id' => $rule->id,
                'event_id' => $event->id,
                'event_type' => $this->eventType,
                'status' => 'failed',
                'executed_at' => now(),
                'error_message' => $e->getMessage(),
                'details' => [
                    'event_data' => $this->eventData,
                ],
            ]);

            Log::error('Workflow rule execution failed', [
                'rule_id' => $rule->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function evaluateConditions(array $conditions): bool
    {
        foreach ($conditions as $condition) {
            $field = $condition['field'];
            $operator = $condition['operator'];
            $value = $condition['value'];
            $actualValue = data_get($this->eventData, $field);

            $result = match ($operator) {
                '=' => $actualValue == $value,
                '!=' => $actualValue != $value,
                '>' => $actualValue > $value,
                '<' => $actualValue < $value,
                '>=' => $actualValue >= $value,
                '<=' => $actualValue <= $value,
                'contains' => str_contains($actualValue, $value),
                'starts_with' => str_starts_with($actualValue, $value),
                'ends_with' => str_ends_with($actualValue, $value),
                default => false,
            };

            if (!$result) {
                return false;
            }
        }

        return true;
    }

    protected function executeAction(WorkflowAction $action, WorkflowRule $rule, WorkflowEvent $event): void
    {
        $actionType = $action->action_type;
        $config = $action->config;

        match ($actionType) {
            'send_notification' => $this->sendNotification($config, $event),
            'create_task' => $this->createTask($config, $event),
            'update_status' => $this->updateStatus($config, $event),
            'send_email' => $this->sendEmail($config, $event),
            'trigger_webhook', 'send_whatsapp' => $this->triggerWebhook($config, $event),
            'assign_sales' => $this->assignSales($config, $event),
            default => Log::warning('Unknown action type', ['type' => $actionType]),
        };

        Log::info('Workflow action executed', [
            'rule_id' => $rule->id,
            'action_type' => $actionType,
        ]);
    }

    protected function sendNotification(array $config, WorkflowEvent $event): void
    {
        $message = $this->replacePlaceholders($config['message'] ?? 'Notification', $event->payload);
        Log::info('Automation Notification', ['message' => $message]);

        // Create a formal alert in the database
        \App\Models\Alert::create([
            'organization_id' => $event->organization_id,
            'campaign_id' => $event->payload['entity_id'] ?? null,
            'type' => $event->event_type,
            'severity' => 'warning',
            'message' => $message,
            'details' => $event->payload,
        ]);
    }

    protected function createTask(array $config, WorkflowEvent $event): void
    {
        \App\Models\Task::create([
            'organization_id' => $event->organization_id,
            'title' => $this->replacePlaceholders($config['title'] ?? 'Automated Task', $event->payload),
            'description' => $this->replacePlaceholders($config['description'] ?? 'Generated by workflow rule', $event->payload),
            'status' => 'Pending',
            'priority' => $config['priority'] ?? 'Medium',
        ]);
    }

    protected function updateStatus(array $config, WorkflowEvent $event): void
    {
        // Generic status update for entity
    }

    protected function sendEmail(array $config, WorkflowEvent $event): void
    {
        // Integration with Mailer
    }

    protected function triggerWebhook(array $config, WorkflowEvent $event): void
    {
        $url = $config['url'] ?? null;
        if (!$url)
            return;

        $message = isset($config['message']) ? $this->replacePlaceholders($config['message'], $event->payload) : null;

        \Illuminate\Support\Facades\Http::post($url, [
            'event' => $event->event_type,
            'data' => $event->payload,
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    protected function replacePlaceholders(string $text, array $data): string
    {
        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $text = str_replace('{{' . $key . '}}', (string) $value, $text);
            }
        }
        return $text;
    }

    protected function assignSales(array $config, WorkflowEvent $event): void
    {
        if ($event->payload['entity_type'] === 'lead') {
            $fbLead = \App\Models\FacebookLead::find($event->payload['entity_id']);
            if ($fbLead) {
                // Find corresponding CRM lead by email
                $crmLead = \App\Models\Lead::where('email', $fbLead->email)
                    ->where('organization_id', $fbLead->organization_id)
                    ->first();

                if ($crmLead) {
                    $assignedTo = $config['user_id'] ?? 'Round Robin';
                    $crmLead->update([
                        'assigned_user' => $assignedTo,
                        'notes' => $crmLead->notes . "\n[System] Auto-assigned to {$assignedTo} via automation rule."
                    ]);
                    Log::info('Lead auto-assigned', ['lead_id' => $crmLead->id, 'to' => $assignedTo]);
                }
            }
        }
    }
}
