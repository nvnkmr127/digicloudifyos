<?php

namespace App\Services\Automation;

use App\Models\AutomationAction;
use App\Models\AutomationRule;
use App\Models\Client;
use App\Models\PerformanceAnomaly;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class AutomationEngine
{
    public function runForClient(string $orgId, string $clientId, string $date): void
    {
        $client = Client::where('organization_id', $orgId)->find($clientId);
        if (! $client) {
            return;
        }

        $rules = AutomationRule::where('organization_id', $orgId)
            ->where('is_active', true)
            ->where(function ($q) use ($clientId) {
                $q->whereNull('client_id')->orWhere('client_id', $clientId);
            })
            ->get();

        $anomalies = PerformanceAnomaly::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->whereDate('detected_at', $date)
            ->whereNull('resolved_at')
            ->get();

        foreach ($rules as $rule) {
            if ($rule->trigger_type === 'anomaly') {
                $types = (array) ($rule->trigger_config['anomaly_types'] ?? []);
                $match = $anomalies->first(fn ($a) => in_array($a->anomaly_type, $types, true));
                if (! $match) {
                    continue;
                }
                $this->executeRule($rule, $client, $match);
            }
        }
    }

    protected function executeRule(AutomationRule $rule, Client $client, PerformanceAnomaly $anomaly): void
    {
        if ($rule->action_type === 'create_task') {
            $title = (string) ($rule->action_config['title'] ?? 'Investigate performance issue');
            $priority = (string) ($rule->action_config['priority'] ?? 'high');
            $description = (string) ($rule->action_config['description'] ?? '');

            Task::create([
                'organization_id' => $client->organization_id,
                'client_id' => $client->id,
                'campaign_id' => null,
                'title' => $title,
                'description' => $description !== '' ? $description : ('Triggered by anomaly: '.$anomaly->anomaly_type),
                'status' => 'pending',
                'priority' => $priority,
                'deadline' => now()->addDays(2),
                'created_by' => Auth::id(),
            ]);

            return;
        }

        if ($rule->action_type === 'propose_change') {
            AutomationAction::create([
                'organization_id' => $client->organization_id,
                'client_id' => $client->id,
                'campaign_id' => null,
                'automation_rule_id' => $rule->id,
                'channel_type' => $rule->channel_type,
                'action_type' => (string) ($rule->action_config['action_type'] ?? 'pause_campaign'),
                'payload' => [
                    'reason' => 'Triggered by anomaly: '.$anomaly->anomaly_type,
                    'anomaly_id' => $anomaly->id,
                    'suggested' => $rule->action_config,
                ],
                'status' => $rule->requires_approval ? 'proposed' : 'approved',
                'requested_by' => Auth::id(),
                'approved_at' => $rule->requires_approval ? null : now(),
                'approved_by' => $rule->requires_approval ? null : Auth::id(),
            ]);
        }
    }
}
