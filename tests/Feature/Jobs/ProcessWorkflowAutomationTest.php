<?php

namespace Tests\Feature\Jobs;

use App\Jobs\ProcessWorkflowAutomation;
use App\Models\WorkflowAction;
use App\Models\WorkflowRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessWorkflowAutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_is_idempotent_and_skips_duplicate_executions()
    {
        $organization = \App\Models\Organization::factory()->create();

        $rule = WorkflowRule::create([
            'organization_id' => $organization->id,
            'name' => 'Test Rule',
            'event_type' => 'lead_created',
            'action_type' => 'create_task',
            'action_config' => [],
            'is_active' => true,
        ]);

        $idempotencyKey = 'unique-event-id-123';

        $eventData = [
            'organization_id' => $organization->id,
            'entity_type' => 'lead',
            'entity_id' => 'lead-1',
            'email' => 'test@example.com',
        ];

        WorkflowAction::create([
            'workflow_rule_id' => $rule->id,
            'action_type' => 'create_task',
            'config' => [
                'title' => 'Follow up',
                'description' => 'Call the lead',
                'status' => 'pending',
                'priority' => 'medium',
            ],
        ]);

        $job1 = new ProcessWorkflowAutomation('lead_created', $eventData, $idempotencyKey);
        $job1->handle();

        $this->assertDatabaseHas('workflow_events', [
            'id' => $idempotencyKey,
            'event_type' => 'lead_created',
        ]);

        $this->assertDatabaseCount('tasks', 1);
        $this->assertDatabaseCount('automation_logs', 1);

        $job2 = new ProcessWorkflowAutomation('lead_created', $eventData, $idempotencyKey);
        $job2->handle();

        $this->assertDatabaseCount('workflow_events', 1);
        $this->assertDatabaseCount('tasks', 1);
        $this->assertDatabaseCount('automation_logs', 1);
    }
}
