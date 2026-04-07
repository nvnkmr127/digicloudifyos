<?php

namespace App\Jobs\Intelligence;

use App\Models\AdInsight;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\Organization;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DetectCreativeFatigue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?string $date = null)
    {
        $this->onQueue('intelligence');
    }

    public function handle(): void
    {
        $date = $this->date ?? now()->subDay()->toDateString();
        $end = Carbon::parse($date)->endOfDay();

        foreach (Organization::lazy() as $org) {
            $this->runForOrg($org->id, $end);
        }
    }

    protected function runForOrg(string $orgId, Carbon $end): void
    {
        $clients = Client::where('organization_id', $orgId)->active()->get(['id']);

        foreach ($clients as $client) {
            $campaigns = Campaign::where('organization_id', $orgId)
                ->where('client_id', $client->id)
                ->whereNotNull('external_campaign_id')
                ->get(['id', 'name', 'client_id']);

            foreach ($campaigns as $campaign) {
                $this->checkCampaign($orgId, $campaign, $end);
            }
        }
    }

    protected function checkCampaign(string $orgId, Campaign $campaign, Carbon $end): void
    {
        $recentStart = $end->copy()->subDays(3)->startOfDay();
        $baselineStart = $end->copy()->subDays(10)->startOfDay();
        $baselineEnd = $end->copy()->subDays(4)->endOfDay();

        $recent = AdInsight::where('organization_id', $orgId)
            ->where('campaign_id', $campaign->id)
            ->where('level', 'ad')
            ->whereBetween('date', [$recentStart->toDateString(), $end->toDateString()])
            ->selectRaw('AVG(ctr) as ctr_avg, SUM(spend) as spend_sum')
            ->first();

        $baseline = AdInsight::where('organization_id', $orgId)
            ->where('campaign_id', $campaign->id)
            ->where('level', 'ad')
            ->whereBetween('date', [$baselineStart->toDateString(), $baselineEnd->toDateString()])
            ->selectRaw('AVG(ctr) as ctr_avg, SUM(spend) as spend_sum')
            ->first();

        $recentCtr = (float) ($recent?->ctr_avg ?? 0);
        $baselineCtr = (float) ($baseline?->ctr_avg ?? 0);
        $recentSpend = (float) ($recent?->spend_sum ?? 0);

        if ($baselineCtr <= 0 || $recentSpend < 25) {
            return;
        }

        $dropPct = (($baselineCtr - $recentCtr) / $baselineCtr) * 100;

        if ($dropPct < 30) {
            return;
        }

        $exists = Task::where('organization_id', $orgId)
            ->where('campaign_id', $campaign->id)
            ->where('title', 'Creative fatigue check')
            ->where('status', '!=', 'completed')
            ->where('created_at', '>=', $end->copy()->subDays(3))
            ->exists();

        if ($exists) {
            return;
        }

        Task::create([
            'organization_id' => $orgId,
            'client_id' => $campaign->client_id,
            'campaign_id' => $campaign->id,
            'title' => 'Creative fatigue check',
            'description' => 'CTR dropped ~'.number_format($dropPct, 1).'% vs baseline. Consider refreshing creatives, offer, or audience.',
            'status' => 'pending',
            'priority' => $dropPct >= 50 ? 'high' : 'medium',
            'deadline' => $end->copy()->addDays(2),
        ]);
    }
}
