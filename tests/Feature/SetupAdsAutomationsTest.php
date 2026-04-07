<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\WorkflowAction;
use App\Models\WorkflowRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetupAdsAutomationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_workflow_actions_for_ads_rules(): void
    {
        $org = Organization::factory()->create();

        $this->artisan('ads:setup-automations', ['organization_id' => $org->id])->assertSuccessful();

        $expected = [
            'ads_low_ctr' => ['create_task'],
            'ads_creative_fatigue' => ['send_notification'],
            'ads_high_cpl' => ['send_notification'],
            'ads_high_cpc' => ['send_notification'],
        ];

        foreach ($expected as $eventType => $actionTypes) {
            $rule = WorkflowRule::where('organization_id', $org->id)
                ->where('event_type', $eventType)
                ->first();

            $this->assertNotNull($rule, "Missing workflow rule for {$eventType}");

            foreach ($actionTypes as $actionType) {
                $this->assertTrue(
                    WorkflowAction::where('workflow_rule_id', $rule->id)->where('action_type', $actionType)->exists(),
                    "Missing workflow action {$actionType} for {$eventType}"
                );
            }
        }
    }
}

