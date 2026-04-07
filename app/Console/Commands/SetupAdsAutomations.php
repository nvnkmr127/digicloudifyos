<?php

namespace App\Console\Commands;

use App\Models\Organization;
use App\Models\WorkflowAction;
use App\Models\WorkflowRule;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class SetupAdsAutomations extends Command
{
    protected $signature = 'ads:setup-automations {organization_id?}';

    protected $description = 'Setup default automation rules for ads and leads';

    public function handle()
    {
        $orgId = $this->argument('organization_id');
        /** @var Collection<int, Organization> $orgs */
        $orgs = $orgId ? Organization::where('id', $orgId)->get() : Organization::all();

        foreach ($orgs as $org) {
            $this->setupForOrg($org);
        }

        $this->info('Automations setup successfully.');
    }

    private function setupForOrg(Organization $org)
    {
        $lowCtrPercent = (float) config('performance.automation.low_ctr_percent', 1);
        $fatigueFrequency = (float) config('performance.automation.creative_fatigue_frequency', 3);

        // 1. WhatsApp for New Lead
        $leadRule = WorkflowRule::updateOrCreate(
            ['organization_id' => $org->id, 'event_type' => 'lead_captured', 'name' => 'Instant WhatsApp Welcome'],
            [
                'description' => 'Send a WhatsApp message when a new lead arrives.',
                'is_active' => true,
                'action_type' => 'send_whatsapp',
                'action_config' => [],
            ]
        );

        WorkflowAction::updateOrCreate(
            ['workflow_rule_id' => $leadRule->id, 'action_type' => 'send_whatsapp'],
            [
                'config' => [
                    'url' => 'https://api.whatsapp-provider.com/send',
                    'message' => 'Hello {{full_name}}, thanks for your interest in {{form_name}}! We will contact you soon.',
                ],
            ]
        );

        // 2. Sales Assignment
        WorkflowAction::updateOrCreate(
            ['workflow_rule_id' => $leadRule->id, 'action_type' => 'assign_sales'],
            [
                'config' => ['user_id' => 'round_robin'],
            ]
        );

        // 3. Low CTR Alert
        $lowCtrRule = WorkflowRule::updateOrCreate(
            ['organization_id' => $org->id, 'event_type' => 'ads_low_ctr', 'name' => 'Low CTR Watchdog'],
            [
                'description' => "Create a task for the team when CTR drops below {$lowCtrPercent}%",
                'is_active' => true,
                'action_type' => 'create_task',
                'action_config' => [
                    'title' => 'Optimize Campaign: {{campaign_name}}',
                    'description' => 'Campaign CTR is critically low ({{ctr}}%). Review creatives.',
                    'priority' => 'High',
                ],
            ]
        );

        WorkflowAction::updateOrCreate(
            ['workflow_rule_id' => $lowCtrRule->id, 'action_type' => 'create_task'],
            [
                'config' => [
                    'title' => 'Optimize Campaign: {{campaign_name}}',
                    'description' => "Campaign CTR is critically low ({{ctr}}%). Review creatives. (Threshold: {$lowCtrPercent}%)",
                    'priority' => 'High',
                    'status' => 'pending',
                ],
            ]
        );

        // 4. Creative Fatigue Alert
        $fatigueRule = WorkflowRule::updateOrCreate(
            ['organization_id' => $org->id, 'event_type' => 'ads_creative_fatigue', 'name' => 'Creative Fatigue Alert'],
            [
                'description' => "Alert when ad frequency exceeds {$fatigueFrequency} (Fatigue detected)",
                'is_active' => true,
                'action_type' => 'send_notification',
                'action_config' => [
                    'message' => 'Ad fatigue detected for {{ad_name}}! Frequency is {{frequency}}.',
                ],
            ]
        );

        WorkflowAction::updateOrCreate(
            ['workflow_rule_id' => $fatigueRule->id, 'action_type' => 'send_notification'],
            [
                'config' => [
                    'message' => "Ad fatigue detected for {{ad_name}}! Frequency is {{frequency}}. (Threshold: {$fatigueFrequency})",
                    'title' => 'Creative fatigue',
                    'channels' => 'WEB',
                ],
            ]
        );

        // 5. High CPL Alert
        $cplRule = WorkflowRule::updateOrCreate(
            ['organization_id' => $org->id, 'event_type' => 'ads_high_cpl', 'name' => 'High CPL Watchdog'],
            [
                'description' => 'Alert when Cost Per Lead exceeds target',
                'is_active' => true,
                'action_type' => 'send_notification',
                'action_config' => [
                    'message' => 'High CPL alert on {{campaign_name}}! Current CPL is ${{cpl}} (Target: ${{threshold}}).',
                ],
            ]
        );

        WorkflowAction::updateOrCreate(
            ['workflow_rule_id' => $cplRule->id, 'action_type' => 'send_notification'],
            [
                'config' => [
                    'message' => 'High CPL alert on {{campaign_name}}! Current CPL is ${{cpl}} (Target: ${{threshold}}).',
                    'title' => 'High CPL',
                    'channels' => 'WEB',
                ],
            ]
        );
        // 6. High CPC Alert
        $cpcRule = WorkflowRule::updateOrCreate(
            ['organization_id' => $org->id, 'event_type' => 'ads_high_cpc', 'name' => 'High CPC Watchdog'],
            [
                'description' => 'Alert when Cost Per Click exceeds target',
                'is_active' => true,
                'action_type' => 'send_notification',
                'action_config' => [
                    'message' => 'High CPC alert on {{campaign_name}}! Current CPC is ${{cpc}} (Target: ${{threshold}}).',
                ],
            ]
        );

        WorkflowAction::updateOrCreate(
            ['workflow_rule_id' => $cpcRule->id, 'action_type' => 'send_notification'],
            [
                'config' => [
                    'message' => 'High CPC alert on {{campaign_name}}! Current CPC is ${{cpc}} (Target: ${{threshold}}).',
                    'title' => 'High CPC',
                    'channels' => 'WEB',
                ],
            ]
        );
    }
}
