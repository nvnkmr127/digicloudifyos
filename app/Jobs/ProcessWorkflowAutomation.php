<?php

namespace App\Jobs;

use App\Models\AutomationLog;
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
            WorkflowEvent::create([
                'event_type' => $this->eventType,
                'payload' => $this->eventData,
                'processed_at' => null,
            ]);

            $rules = WorkflowRule::where('event_type', $this->eventType)
                ->where('active', true)
                ->get();

            foreach ($rules as $rule) {
                $this->processRule($rule);
            }

        } catch (\Exception $e) {
            Log::error('Workflow automation processing failed', [
                'event_type' => $this->eventType,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function processRule(WorkflowRule $rule): void
    {
        try {
            if (! $this->evaluateConditions($rule->conditions)) {
                return;
            }

            foreach ($rule->actions as $action) {
                $this->executeAction($action, $rule);
            }

            AutomationLog::create([
                'workflow_rule_id' => $rule->id,
                'event_type' => $this->eventType,
                'status' => 'success',
                'executed_at' => now(),
                'details' => [
                    'actions_count' => count($rule->actions),
                    'event_data' => $this->eventData,
                ],
            ]);

        } catch (\Exception $e) {
            AutomationLog::create([
                'workflow_rule_id' => $rule->id,
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

            if (! $result) {
                return false;
            }
        }

        return true;
    }

    protected function executeAction(array $action, WorkflowRule $rule): void
    {
        $actionType = $action['type'];

        match ($actionType) {
            'send_notification' => $this->sendNotification($action['config']),
            'create_task' => $this->createTask($action['config']),
            'update_status' => $this->updateStatus($action['config']),
            'send_email' => $this->sendEmail($action['config']),
            'trigger_webhook' => $this->triggerWebhook($action['config']),
            default => Log::warning('Unknown action type', ['type' => $actionType]),
        };

        Log::info('Workflow action executed', [
            'rule_id' => $rule->id,
            'action_type' => $actionType,
        ]);
    }

    protected function sendNotification(array $config): void {}

    protected function createTask(array $config): void {}

    protected function updateStatus(array $config): void {}

    protected function sendEmail(array $config): void {}

    protected function triggerWebhook(array $config): void {}
}
