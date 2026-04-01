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
        $actionTypes = $rule->actions
            ->pluck('action_type')
            ->filter()
            ->values()
            ->all();

        try {
            if ($rule->conditions && !$this->evaluateConditions($rule->conditions)) {
                return;
            }

            foreach ($rule->actions as $action) {
                $this->executeAction($action, $rule, $event);
            }

            AutomationLog::create([
                'organization_id' => $event->organization_id,
                'workflow_rule_id' => $rule->id,
                'event_id' => $event->id,
                'event_type' => $this->eventType,
                'action_type' => implode(',', $actionTypes),
                'status' => 'success',
                'executed_at' => now(),
                'details' => [
                    'actions_count' => count($actionTypes),
                    'actions_executed' => $actionTypes,
                    'event_data' => $this->eventData,
                ],
            ]);

        } catch (\Exception $e) {
            AutomationLog::create([
                'organization_id' => $event->organization_id,
                'workflow_rule_id' => $rule->id,
                'event_id' => $event->id,
                'event_type' => $this->eventType,
                'action_type' => implode(',', $actionTypes),
                'status' => 'failed',
                'executed_at' => now(),
                'error_message' => $e->getMessage(),
                'details' => [
                    'actions_count' => count($actionTypes),
                    'actions_executed' => $actionTypes,
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
            'calculate_lead_score' => $this->calculateLeadScore($event),
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
        
        \App\Models\Notification::create([
            'organization_id' => $event->organization_id,
            'trigger' => $event->event_type,
            'title' => $config['title'] ?? 'System Alert',
            'message' => $message,
            'channels' => $config['channels'] ?? 'WEB',
            'metadata' => [
                'entity_type' => $event->entity_type,
                'entity_id' => $event->entity_id,
            ],
            'is_read' => false,
        ]);

        Log::info('Automation Notification created', ['message' => $message]);
    }

    protected function createTask(array $config, WorkflowEvent $event): void
    {
        \App\Models\Task::create([
            'organization_id' => $event->organization_id,
            'title' => $this->replacePlaceholders($config['title'] ?? 'Automated Task', $event->payload),
            'description' => $this->replacePlaceholders($config['description'] ?? 'Generated by workflow rule', $event->payload),
            'status' => $config['status'] ?? 'pending',
            'priority' => strtolower($config['priority'] ?? 'medium'),
        ]);
    }

    protected function updateStatus(array $config, WorkflowEvent $event): void
    {
        $entityType = $event->entity_type;
        $entityId = $event->entity_id;
        $newStatus = $config['status'] ?? null;

        if (!$newStatus) return;

        $modelClass = match ($entityType) {
            'lead' => \App\Models\Lead::class,
            'campaign' => \App\Models\Campaign::class,
            'task' => \App\Models\Task::class,
            'project' => \App\Models\Project::class,
            default => null,
        };

        if ($modelClass) {
            $entity = $modelClass::find($entityId);
            if ($entity) {
                $entity->update(['status' => $newStatus]);
                Log::info("Automation: Updated {$entityType} status to {$newStatus}", ['id' => $entityId]);
            }
        }
    }

    protected function sendEmail(array $config, WorkflowEvent $event): void
    {
        $to = $config['to'] ?? $event->payload['email'] ?? null;
        $subject = $this->replacePlaceholders($config['subject'] ?? 'System Notification', $event->payload);
        $body = $this->replacePlaceholders($config['body'] ?? '', $event->payload);

        if ($to && $body) {
            \Illuminate\Support\Facades\Mail::raw($body, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
            });
            Log::info("Automation: Email sent to {$to}", ['subject' => $subject]);
        }
    }

    protected function triggerWebhook(array $config, WorkflowEvent $event): void
    {
        $url = $config['url'] ?? null;
        if (!$url)
            return;

        $message = isset($config['message']) ? $this->replacePlaceholders($config['message'], $event->payload) : null;
        $headers = $config['headers'] ?? [];
        
        $request = \Illuminate\Support\Facades\Http::timeout(10);
        
        if (!empty($headers)) {
            $request->withHeaders($headers);
        }

        $payload = [
            'event' => $event->event_type,
            'data' => $event->payload,
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
        ];

        try {
            $response = $request->post($url, $payload);
            
            // If we have a webhook ID in the config, log the delivery
            if (isset($config['webhook_id'])) {
                \App\Models\WebhookDelivery::create([
                    'webhook_id' => $config['webhook_id'],
                    'event' => $event->event_type,
                    'payload' => $payload,
                    'response_status' => $response->status(),
                    'response_body' => substr($response->body(), 0, 1000), // Limit length
                    'delivered_at' => $response->successful() ? now() : null,
                    'failed_at' => $response->failed() ? now() : null,
                    'error_message' => $response->failed() ? 'HTTP ' . $response->status() : null,
                ]);
            }
            
            if ($response->failed()) {
                Log::warning('Outbound Webhook Failed', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'event_id' => $event->id
                ]);
                
                throw new \Exception('Webhook delivery failed with status: ' . $response->status());
            }
        } catch (\Exception $e) {
            if (isset($config['webhook_id'])) {
                \App\Models\WebhookDelivery::create([
                    'webhook_id' => $config['webhook_id'],
                    'event' => $event->event_type,
                    'payload' => $payload,
                    'response_status' => 0,
                    'response_body' => null,
                    'failed_at' => now(),
                    'error_message' => substr($e->getMessage(), 0, 500),
                ]);
            }
            throw $e;
        }
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
            $crmLead = \App\Models\Lead::find($event->payload['entity_id']);
            
            if (!$crmLead) {
                // Fallback for facebook leads
                $fbLead = \App\Models\FacebookLead::find($event->payload['entity_id']);
                if ($fbLead) {
                    $crmLead = \App\Models\Lead::where('email', $fbLead->email)
                        ->where('organization_id', $fbLead->organization_id)
                        ->first();
                }
            }

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

    protected function calculateLeadScore(WorkflowEvent $event): void
    {
        if (($event->payload['entity_type'] ?? '') === 'lead') {
            $lead = \App\Models\Lead::find($event->payload['entity_id']);
            if ($lead) {
                (new \App\Services\LeadScoringService())->calculate($lead);
            }
        }
    }
}
