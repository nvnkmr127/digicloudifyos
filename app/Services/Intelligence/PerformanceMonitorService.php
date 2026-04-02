<?php

namespace App\Services\Intelligence;

use App\Models\Client;
use App\Models\ClientHealthScore;
use App\Models\GoogleMerchantCenterDailyMetric;
use App\Models\Organization;
use App\Models\PerformanceAnomaly;
use App\Models\PerformanceSnapshot;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PerformanceMonitorService
{
    public function __construct(
        protected ChannelDataAggregatorService $aggregator,
        protected AnomalyDetectionService $detector
    ) {}

    /**
     * Runs monitoring for all clients in an organization.
     */
    public function runForOrganization(string $orgId, ?string $date = null): void
    {
        $date = $date ?? today()->toDateString();
        $clients = Client::where('organization_id', $orgId)->active()->get();

        Log::info("Starting performance monitoring for organization {$orgId} on {$date}. Clients: ".$clients->count());

        foreach ($clients as $client) {
            try {
                $this->runForClient($client->id, $orgId, $date);
            } catch (\Exception $e) {
                Log::error("Failed to run monitoring for client {$client->id}: ".$e->getMessage());
            }
        }
    }

    /**
     * Orchestrates the aggregation and detection for a single client.
     */
    public function runForClient(string $clientId, string $orgId, string $date): void
    {
        $channelsData = $this->aggregator->aggregateAll($clientId, $orgId, $date);

        foreach ($channelsData as $channel => $metrics) {
            $baselines = $this->calculateBaselines($clientId, $channel, $date);

            $snapshot = PerformanceSnapshot::updateOrCreate(
                [
                    'organization_id' => $orgId,
                    'client_id' => $clientId,
                    'channel_type' => $channel,
                    'snapshot_date' => $date,
                ],
                array_merge($metrics, [
                    'baseline_ctr' => $baselines['ctr'] ?? null,
                    'baseline_cpc' => $baselines['cpc'] ?? null,
                    'baseline_roas' => $baselines['roas'] ?? null,
                    'baseline_leads' => $baselines['leads'] ?? null,
                ])
            );

            // Run Anomaly Detection
            $anomalies = $this->detector->detect($metrics, $baselines, $clientId, $orgId, $channel);

            $anomalyTypes = [];
            foreach ($anomalies as $anomalyData) {
                $anomalyTypes[] = $anomalyData['anomaly_type'];

                // [TASK-063] Duplicate prevention (one specific anomaly type per channel per day)
                PerformanceAnomaly::updateOrCreate(
                    [
                        'organization_id' => $orgId,
                        'client_id' => $clientId,
                        'channel_type' => $channel,
                        'anomaly_type' => $anomalyData['anomaly_type'],
                        'snapshot_id' => $snapshot->id, // tied to current date snapshot
                    ],
                    array_merge($anomalyData, [
                        'detected_at' => Carbon::parse($date)->endOfDay(),
                    ])
                );
            }

            // Update snapshot with anomaly flags
            $snapshot->update(['anomaly_flags' => $anomalyTypes]);

            $this->detectBenchmarkAnomalies($snapshot, $orgId, $clientId, $date, $channel);
            $this->detectMerchantCenterHealthAnomalies($snapshot, $orgId, $clientId, $date, $channel);

            $snapshot->update([
                'anomaly_flags' => PerformanceAnomaly::where('snapshot_id', $snapshot->id)
                    ->pluck('anomaly_type')
                    ->unique()
                    ->values()
                    ->all(),
            ]);
        }

        $this->calculateHealthScore($clientId, $orgId, $date);

        // [TASK-064] Auto-resolve stale anomalies
        $this->autoResolveAnomalies($clientId, $orgId, $date);

        // Make it "More Powerful": Predictive Projections
        $this->calculateProjections($clientId, $orgId, $date);
    }

    protected function detectBenchmarkAnomalies(PerformanceSnapshot $snapshot, string $orgId, string $clientId, string $date, string $channel): void
    {
        $bench = $snapshot->raw_data['benchmarks']['deltas'] ?? null;
        if (! is_array($bench) || empty($bench)) {
            return;
        }

        $candidates = [
            ['metric' => 'ctr', 'better' => 'higher', 'threshold' => -15, 'severity' => 'medium'],
            ['metric' => 'roas', 'better' => 'higher', 'threshold' => -20, 'severity' => 'high'],
            ['metric' => 'cpc', 'better' => 'lower', 'threshold' => 20, 'severity' => 'medium'],
            ['metric' => 'engagement_rate', 'better' => 'higher', 'threshold' => -20, 'severity' => 'medium'],
        ];

        foreach ($candidates as $c) {
            $metric = $c['metric'];
            if (! isset($bench[$metric]['diff_pct'])) {
                continue;
            }

            $diffPct = $bench[$metric]['diff_pct'];
            if ($diffPct === null) {
                continue;
            }
            $diffPct = (float) $diffPct;

            $isBad = $c['better'] === 'higher'
                ? ($diffPct <= (float) $c['threshold'])
                : ($diffPct >= (float) $c['threshold']);

            if (! $isBad) {
                continue;
            }

            $median = (float) ($bench[$metric]['median'] ?? 0);
            $current = (float) ($bench[$metric]['current'] ?? 0);

            PerformanceAnomaly::updateOrCreate(
                [
                    'organization_id' => $orgId,
                    'client_id' => $clientId,
                    'channel_type' => $channel,
                    'anomaly_type' => 'below_industry_median',
                    'snapshot_id' => $snapshot->id,
                    'metric_name' => strtoupper($metric).' vs industry median',
                ],
                [
                    'current_value' => $current,
                    'baseline_value' => $median,
                    'deviation_percentage' => $diffPct,
                    'severity' => $c['severity'],
                    'detected_at' => Carbon::parse($date)->endOfDay(),
                    'context' => [
                        'benchmark_metric' => $metric,
                        'benchmark_diff_pct' => $diffPct,
                        'benchmark_median' => $median,
                        'benchmark_current' => $current,
                        'benchmark_type' => 'industry_median',
                    ],
                ]
            );
        }
    }

    /**
     * Calculates projected EOM metrics based on current month progress.
     */
    protected function calculateProjections(string $clientId, string $orgId, string $date): void
    {
        $snapshot = PerformanceSnapshot::where('client_id', $clientId)
            ->where('snapshot_date', $date)
            ->first();

        if (! $snapshot) {
            return;
        }

        $targetDate = Carbon::parse($date);
        $daysInMonth = $targetDate->daysInMonth;
        $dayOfMonth = $targetDate->day;
        $remainingDays = $daysInMonth - $dayOfMonth;

        // Get 3-day average spend to account for weekend spikes
        $recentSpend = PerformanceSnapshot::where('client_id', $clientId)
            ->where('snapshot_date', '<=', $date)
            ->orderBy('snapshot_date', 'desc')
            ->limit(3)
            ->avg('spend');

        $currentMonthSpend = PerformanceSnapshot::where('client_id', $clientId)
            ->whereBetween('snapshot_date', [$targetDate->startOfMonth()->toDateString(), $date])
            ->sum('spend');

        $projectedSpend = $currentMonthSpend + ($recentSpend * $remainingDays);

        $rawData = $snapshot->raw_data ?? [];
        $rawData['projections'] = [
            'eom_spend' => round($projectedSpend, 2),
            'monthly_pace_pct' => $dayOfMonth > 0 ? round(($dayOfMonth / $daysInMonth) * 100, 2) : 0,
            'is_pacing_over' => false, // Will be checked vs budget later
        ];

        $snapshot->update(['raw_data' => $rawData]);
    }

    /**
     * Auto-resolve anomalies that have not been detected for 3 consecutive days.
     */
    protected function autoResolveAnomalies(string $clientId, string $orgId, string $date): void
    {
        $unresolved = PerformanceAnomaly::where('client_id', $clientId)
            ->whereNull('resolved_at')
            ->get();

        foreach ($unresolved as $anomaly) {
            /** @var PerformanceAnomaly $anomaly */
            // Check if this anomaly type was NOT detected in the last 3 days snapshots
            $recentDetections = PerformanceSnapshot::where('client_id', $clientId)
                ->where('channel_type', $anomaly->channel_type)
                ->where('snapshot_date', '>=', now()->subDays(3)->toDateString())
                ->where('anomaly_flags', 'LIKE', '%"'.$anomaly->anomaly_type.'"%')
                ->count();

            if ($recentDetections === 0) {
                $anomaly->resolve();
                Log::info("Auto-resolved stale anomaly: {$anomaly->anomaly_type} for client {$clientId} on channel {$anomaly->channel_type}");
            }
        }
    }

    /**
     * Calculates 7-day rolling baselines for a channel.
     */
    public function calculateBaselines(string $clientId, string $channel, string $date): array
    {
        $snapshots = PerformanceSnapshot::where('client_id', $clientId)
            ->where('channel_type', $channel)
            ->where('snapshot_date', '<', $date)
            ->orderBy('snapshot_date', 'desc')
            ->limit(7)
            ->get();

        if ($snapshots->isEmpty()) {
            return [];
        }

        return [
            'ctr' => $snapshots->avg('ctr'),
            'cpc' => $snapshots->avg('cpc'),
            'roas' => $snapshots->avg('roas'),
            'leads' => $snapshots->avg('leads'),
            'engagement_rate' => $snapshots->avg('engagement_rate'),
        ];
    }

    /**
     * Calculates and persists the client's health score for the date.
     */
    public function calculateHealthScore(string $clientId, string $orgId, string $date): void
    {
        $snapshots = PerformanceSnapshot::where('client_id', $clientId)
            ->where('snapshot_date', $date)
            ->get();

        $anomalies = PerformanceAnomaly::where('client_id', $clientId)
            ->whereDate('detected_at', $date)
            ->get();

        $weights = $this->getHealthScoreWeights();

        // Logical scores (0-100)
        $adPerf = 100;
        $conversion = 100;
        $organic = 100;
        $budget = 100;

        // Deduct for anomalies
        foreach ($anomalies as $anomaly) {
            $deduction = match ($anomaly->severity) {
                'critical' => 30,
                'high' => 15,
                'medium' => 5,
                'low' => 2,
            };

            if (in_array($anomaly->anomaly_type, ['ctr_drop', 'cpc_spike', 'roas_decline'])) {
                $adPerf -= $deduction;
            }
            if (in_array($anomaly->anomaly_type, ['lead_drop'])) {
                $conversion -= $deduction;
            }
            if (in_array($anomaly->anomaly_type, ['engagement_drop'])) {
                $organic -= $deduction;
            }
            if (in_array($anomaly->anomaly_type, ['budget_overrun', 'budget_underpace'])) {
                $budget -= $deduction;
            }
        }

        // Clamp values
        $adPerf = max(0, $adPerf);
        $conversion = max(0, $conversion);
        $organic = max(0, $organic);
        $budget = max(0, $budget);

        $overall = ($adPerf * $weights['ad_performance']) +
                   ($conversion * $weights['conversion']) +
                   ($organic * $weights['organic']) +
                   ($budget * $weights['budget']);

        // Determine Trend
        $lastScore = ClientHealthScore::where('client_id', $clientId)
            ->where('score_date', '<', $date)
            ->orderBy('score_date', 'desc')
            ->first();

        $trend = 'stable';
        if ($lastScore) {
            if ($overall > ($lastScore->overall_score + 5)) {
                $trend = 'improving';
            } elseif ($overall < ($lastScore->overall_score - 5)) {
                $trend = 'declining';
            }
        }

        ClientHealthScore::updateOrCreate(
            ['client_id' => $clientId, 'score_date' => $date],
            [
                'organization_id' => $orgId,
                'overall_score' => (int) round($overall),
                'ad_performance_score' => $adPerf,
                'organic_score' => $organic,
                'conversion_score' => $conversion,
                'budget_efficiency_score' => $budget,
                'trend' => $trend,
                'score_breakdown' => [
                    'ad_perf' => $adPerf,
                    'conversion' => $conversion,
                    'organic' => $organic,
                    'budget' => $budget,
                ],
            ]
        );
    }

    public function getHealthScoreWeights(): array
    {
        return [
            'ad_performance' => 0.40,
            'conversion' => 0.30,
            'organic' => 0.20,
            'budget' => 0.10,
        ];
    }

    protected function detectMerchantCenterHealthAnomalies(PerformanceSnapshot $snapshot, string $orgId, string $clientId, string $date, string $channel): void
    {
        if ($channel !== 'google_merchant_center') {
            return;
        }

        $raw = $snapshot->raw_data ?? [];
        $itemsChecked = (int) ($raw['items_checked'] ?? 0);
        $itemsDisapproved = (int) ($raw['items_disapproved'] ?? 0);
        $issueCount = (int) ($raw['issue_count'] ?? 0);

        if ($itemsChecked > 0) {
            $rate = $itemsDisapproved / $itemsChecked;
            if ($rate >= 0.15 && $itemsDisapproved >= 25) {
                PerformanceAnomaly::updateOrCreate(
                    [
                        'organization_id' => $orgId,
                        'client_id' => $clientId,
                        'channel_type' => $channel,
                        'anomaly_type' => 'merchant_disapproval_rate_high',
                        'snapshot_id' => $snapshot->id,
                        'metric_name' => 'Disapproval Rate',
                    ],
                    [
                        'current_value' => $rate,
                        'baseline_value' => 0,
                        'deviation_percentage' => 0,
                        'severity' => $rate >= 0.30 ? 'critical' : 'high',
                        'detected_at' => Carbon::parse($date)->endOfDay(),
                        'context' => [
                            'items_checked' => $itemsChecked,
                            'items_disapproved' => $itemsDisapproved,
                            'disapproval_rate' => $rate,
                            'issue_breakdown' => $raw['issue_breakdown'] ?? null,
                            'top_issue_examples' => $raw['top_issue_examples'] ?? null,
                        ],
                    ]
                );
            }
        }

        $baselineIssues = GoogleMerchantCenterDailyMetric::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->where('metric_date', '<', $date)
            ->orderBy('metric_date', 'desc')
            ->limit(7)
            ->avg('issue_count');

        if ($baselineIssues !== null && $baselineIssues > 0 && $issueCount > ($baselineIssues * 1.5) && $issueCount >= 50) {
            $deviation = (($issueCount - $baselineIssues) / $baselineIssues) * 100;

            PerformanceAnomaly::updateOrCreate(
                [
                    'organization_id' => $orgId,
                    'client_id' => $clientId,
                    'channel_type' => $channel,
                    'anomaly_type' => 'merchant_issue_spike',
                    'snapshot_id' => $snapshot->id,
                    'metric_name' => 'Item Issues',
                ],
                [
                    'current_value' => $issueCount,
                    'baseline_value' => $baselineIssues,
                    'deviation_percentage' => $deviation,
                    'severity' => $issueCount > ($baselineIssues * 2) ? 'high' : 'medium',
                    'detected_at' => Carbon::parse($date)->endOfDay(),
                    'context' => [
                        'issue_breakdown' => $raw['issue_breakdown'] ?? null,
                        'top_issue_examples' => $raw['top_issue_examples'] ?? null,
                        'feed_statuses' => $raw['feed_statuses'] ?? null,
                        'feed_issue_count' => $raw['feed_issue_count'] ?? null,
                    ],
                ]
            );
        }
    }
}
