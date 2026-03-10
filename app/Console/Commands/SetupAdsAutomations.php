<?php

namespace App\Console\Commands;

use App\Models\Organization;
use App\Models\WorkflowRule;
use App\Models\WorkflowAction;
use Illuminate\Console\Command;

class SetupAdsAutomations extends Command
{
    protected $signature = 'ads:setup-automations {organization_id?}';
    protected $description = 'Setup default automation rules for ads and leads';

    public function handle()
    {
        $orgId = $this->argument('organization_id');
        /** @var \Illuminate\Database\Eloquent\Collection<int, Organization> $orgs */
        $orgs = $orgId ? Organization::where('id', $orgId)->get() : Organization::all();

        foreach ($orgs as $org) {
            $this->setupForOrg($org);
        }

        $this->info('Automations setup successfully.');
    }

    private function setupForOrg(Organization $org)
    {
        // 1. WhatsApp for New Lead
        $leadRule = WorkflowRule::updateOrCreate(
            ['organization_id' => $org->id, 'event_type' => 'lead_captured', 'name' => 'Instant WhatsApp Welcome'],
            [
                'description' => 'Send a WhatsApp message when a new lead arrives.',
                'is_active' => true,
            ]
        );

        WorkflowAction::updateOrCreate(
            ['workflow_rule_id' => $leadRule->id, 'action_type' => 'send_whatsapp'],
            [
                'config' => [
                    'url' => 'https://api.whatsapp-provider.com/send',
                    'message' => 'Hello {{full_name}}, thanks for your interest in {{form_name}}! We will contact you soon.'
                ]
            ]
        );

        // 2. Sales Assignment
        WorkflowAction::updateOrCreate(
            ['workflow_rule_id' => $leadRule->id, 'action_type' => 'assign_sales'],
            [
                'config' => ['user_id' => 'round_robin']
            ]
        );

        // 3. Low CTR Alert (CTR < 1%)
        WorkflowRule::updateOrCreate(
            ['organization_id' => $org->id, 'event_type' => 'ads_low_ctr', 'name' => 'Low CTR Watchdog'],
            [
                'description' => 'Create a task for the team when CTR drops below 1%',
                'is_active' => true,
                'action_type' => 'create_task',
                'action_config' => [
                    'title' => 'Optimize Campaign: {{campaign_name}}',
                    'description' => 'Campaign CTR is critically low ({{ctr}}%). Review creatives.',
                    'priority' => 'High'
                ]
            ]
        );

        // 4. Creative Fatigue Alert (Frequency > 3)
        WorkflowRule::updateOrCreate(
            ['organization_id' => $org->id, 'event_type' => 'ads_creative_fatigue', 'name' => 'Creative Fatigue Alert'],
            [
                'description' => 'Alert when ad frequency exceeds 3 (Fatigue detected)',
                'is_active' => true,
                'action_type' => 'send_notification',
                'action_config' => [
                    'message' => 'Ad fatigue detected for {{ad_name}}! Frequency is {{frequency}}.'
                ]
            ]
        );

        // 5. High CPL Alert
        WorkflowRule::updateOrCreate(
            ['organization_id' => $org->id, 'event_type' => 'ads_high_cpl', 'name' => 'High CPL Watchdog'],
            [
                'description' => 'Alert when Cost Per Lead exceeds target',
                'is_active' => true,
                'action_type' => 'send_notification',
                'action_config' => [
                    'message' => 'High CPL alert on {{campaign_name}}! Current CPL is ${{cpl}} (Target: ${{threshold}}).'
                ]
            ]
        );
    }
}
