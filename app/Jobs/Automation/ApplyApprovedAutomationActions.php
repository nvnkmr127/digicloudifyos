<?php

namespace App\Jobs\Automation;

use App\Models\AutomationAction;
use App\Models\Campaign;
use App\Services\AuditService;
use App\Services\Integrations\IntegrationAlertService;
use App\Services\MetaAdsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ApplyApprovedAutomationActions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?int $limit = 100)
    {
        $this->onQueue('intelligence');
    }

    public function handle(MetaAdsService $meta, IntegrationAlertService $alerts, AuditService $audit): void
    {
        $actions = AutomationAction::where('status', 'approved')
            ->orderBy('created_at')
            ->limit($this->limit ?? 100)
            ->get();

        foreach ($actions as $action) {
            try {
                $result = $this->applyOne($action, $meta);

                $action->update([
                    'status' => 'applied',
                    'applied_at' => now(),
                    'result' => $result,
                    'error_message' => null,
                ]);

                $audit->log($action->organization_id, $action->approved_by, 'automation.applied', $action, [
                    'channel_type' => $action->channel_type,
                    'action_type' => $action->action_type,
                ]);
            } catch (\Throwable $e) {
                $action->update([
                    'status' => 'failed',
                    'applied_at' => now(),
                    'error_message' => $e->getMessage(),
                ]);

                $alerts->notifySyncFailure(
                    $action->organization_id,
                    $action->client_id ?: '',
                    'automation',
                    now()->toDateString(),
                    $e->getMessage()
                );
            }
        }
    }

    protected function applyOne(AutomationAction $action, MetaAdsService $meta): array
    {
        $type = $action->action_type;

        if ($type === 'pause_meta_campaign') {
            $campaignId = $action->payload['campaign_id'] ?? null;
            if (! is_string($campaignId) || $campaignId === '') {
                throw new \RuntimeException('Missing campaign_id payload.');
            }

            $campaign = Campaign::where('organization_id', $action->organization_id)->find($campaignId);
            if (! $campaign) {
                throw new \RuntimeException('Campaign not found.');
            }

            $ok = $meta->pauseCampaign($campaign);
            if (! $ok) {
                throw new \RuntimeException('Meta campaign pause failed.');
            }

            return [
                'applied' => true,
                'campaign_id' => $campaignId,
            ];
        }

        throw new \RuntimeException('Unsupported automation action type: '.$type);
    }
}
