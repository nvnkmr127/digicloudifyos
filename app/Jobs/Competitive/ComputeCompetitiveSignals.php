<?php

namespace App\Jobs\Competitive;

use App\Models\Client;
use App\Models\ClientCompetitor;
use App\Models\MetaAdLibraryDailySummary;
use App\Models\PerformanceAnomaly;
use App\Models\SocialListeningEvent;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ComputeCompetitiveSignals implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?string $date = null)
    {
        $this->onQueue('intelligence');
    }

    public function handle(): void
    {
        $date = $this->date ?? now()->subDay()->toDateString();

        $clients = Client::where('status', 'ACTIVE')->get(['id', 'organization_id', 'name']);

        foreach ($clients as $client) {
            $this->computeMetaAdsSignals($client->organization_id, $client->id, $date);
            $this->computeSocialListeningSignals($client->organization_id, $client->id, $date);
        }
    }

    protected function computeMetaAdsSignals(string $orgId, string $clientId, string $date): void
    {
        $competitors = ClientCompetitor::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->where('platform', 'meta_page')
            ->where('is_active', true)
            ->get(['id', 'label', 'identifier']);

        if ($competitors->isEmpty()) {
            return;
        }

        $today = MetaAdLibraryDailySummary::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->whereDate('metric_date', $date)
            ->get();

        $todayActiveTotal = (int) $today->sum('active_ads_count');
        $todayNewTotal = (int) $today->sum('new_ads_count');

        $baseline = MetaAdLibraryDailySummary::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->whereBetween('metric_date', [now()->subDays(7)->toDateString(), now()->subDay()->toDateString()])
            ->get();

        $baselineTotalByDay = $baseline->groupBy('metric_date')->map(fn ($g) => (int) $g->sum('active_ads_count'));
        $baselineAvg = $baselineTotalByDay->count() > 0 ? ($baselineTotalByDay->sum() / $baselineTotalByDay->count()) : null;

        if ($baselineAvg !== null && $baselineAvg > 0 && $todayActiveTotal >= ($baselineAvg * 1.5) && ($todayActiveTotal - $baselineAvg) >= 10) {
            $diffPct = (($todayActiveTotal - $baselineAvg) / $baselineAvg) * 100;

            PerformanceAnomaly::updateOrCreate(
                [
                    'organization_id' => $orgId,
                    'client_id' => $clientId,
                    'channel_type' => 'competitive',
                    'anomaly_type' => 'competitor_ads_spike',
                    'snapshot_id' => null,
                    'metric_name' => 'Competitor Ad Activity',
                ],
                [
                    'current_value' => $todayActiveTotal,
                    'baseline_value' => $baselineAvg,
                    'deviation_percentage' => $diffPct,
                    'severity' => $todayActiveTotal >= ($baselineAvg * 2) ? 'high' : 'medium',
                    'detected_at' => Carbon::parse($date)->endOfDay(),
                    'context' => [
                        'active_ads_total' => $todayActiveTotal,
                        'new_ads_total' => $todayNewTotal,
                        'baseline_active_ads_avg_7d' => $baselineAvg,
                        'competitors' => $competitors->map(fn ($c) => [
                            'id' => $c->id,
                            'label' => $c->label,
                            'page_id' => $c->identifier,
                        ])->values()->all(),
                    ],
                ]
            );
        }

        foreach ($today as $row) {
            if ((int) $row->new_ads_count < 5) {
                continue;
            }

            $competitor = $competitors->firstWhere('id', $row->client_competitor_id);
            $label = $competitor?->label ?: 'Competitor';

            PerformanceAnomaly::updateOrCreate(
                [
                    'organization_id' => $orgId,
                    'client_id' => $clientId,
                    'channel_type' => 'competitive',
                    'anomaly_type' => 'competitor_new_ads',
                    'snapshot_id' => null,
                    'metric_name' => 'New Competitor Ads',
                ],
                [
                    'current_value' => (int) $row->new_ads_count,
                    'baseline_value' => 0,
                    'deviation_percentage' => 0,
                    'severity' => (int) $row->new_ads_count >= 15 ? 'high' : 'medium',
                    'detected_at' => Carbon::parse($date)->endOfDay(),
                    'context' => [
                        'competitor' => [
                            'id' => $row->client_competitor_id,
                            'label' => $label,
                        ],
                        'new_ads_count' => (int) $row->new_ads_count,
                        'active_ads_count' => (int) $row->active_ads_count,
                    ],
                ]
            );
        }
    }

    protected function computeSocialListeningSignals(string $orgId, string $clientId, string $date): void
    {
        $todayCount = (int) SocialListeningEvent::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->whereDate('event_date', $date)
            ->count();

        $baselineCount = (int) SocialListeningEvent::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->whereBetween('event_date', [now()->subDays(7)->toDateString(), now()->subDay()->toDateString()])
            ->count();

        $baselineAvg = $baselineCount / 7;

        if ($baselineAvg > 0 && $todayCount >= ($baselineAvg * 2) && $todayCount >= 10) {
            $diffPct = (($todayCount - $baselineAvg) / $baselineAvg) * 100;

            PerformanceAnomaly::updateOrCreate(
                [
                    'organization_id' => $orgId,
                    'client_id' => $clientId,
                    'channel_type' => 'competitive',
                    'anomaly_type' => 'social_mention_spike',
                    'snapshot_id' => null,
                    'metric_name' => 'Social Mentions',
                ],
                [
                    'current_value' => $todayCount,
                    'baseline_value' => $baselineAvg,
                    'deviation_percentage' => $diffPct,
                    'severity' => $todayCount >= ($baselineAvg * 3) ? 'high' : 'medium',
                    'detected_at' => Carbon::parse($date)->endOfDay(),
                    'context' => [
                        'mentions_today' => $todayCount,
                        'baseline_avg_7d' => $baselineAvg,
                    ],
                ]
            );
        }
    }
}
