<?php

namespace App\Services\Intelligence;

use App\Models\Campaign;

class AnomalyDetectionService
{
    protected array $thresholds;

    public function __construct()
    {
        $this->thresholds = config('intelligence.thresholds', []);
    }

    /**
     * Entry point to detect all anomalies in a snapshot compared to baselines.
     */
    public function detect(array $snapshot, array $baselines, string $clientId, string $orgId, string $channelType): array
    {
        $anomalies = [];

        // Ad Performance Anomaly Detectors
        if (isset($snapshot['ctr']) && isset($baselines['ctr'])) {
            if ($anomaly = $this->detectCtrDrop($snapshot, $baselines, $clientId, $orgId, $channelType)) {
                $anomalies[] = $anomaly;
            }
        }

        if (isset($snapshot['cpc']) && isset($baselines['cpc'])) {
            if ($anomaly = $this->detectCpcSpike($snapshot, $baselines, $clientId, $orgId, $channelType)) {
                $anomalies[] = $anomaly;
            }
        }

        if (isset($snapshot['roas'])) {
            if ($anomaly = $this->detectRoasDecline($snapshot, $baselines, $clientId, $orgId, $channelType)) {
                $anomalies[] = $anomaly;
            }
        }

        // Spend / Budget Anomaly Detectors
        if (isset($snapshot['spend'])) {
            if ($anomaly = $this->detectBudgetOverpace($snapshot, $clientId, $orgId, $channelType)) {
                $anomalies[] = $anomaly;
            }
            if ($anomaly = $this->detectBudgetUnderpace($snapshot, $clientId, $orgId, $channelType)) {
                $anomalies[] = $anomaly;
            }
        }

        // Engagement Detectors
        if (isset($snapshot['engagement_rate']) && isset($baselines['engagement_rate'])) {
            if ($anomaly = $this->detectEngagementDrop($snapshot, $baselines, $clientId, $orgId, $channelType)) {
                $anomalies[] = $anomaly;
            }
        }

        // Lead Detectors
        if (isset($snapshot['leads']) && isset($baselines['leads'])) {
            if ($anomaly = $this->detectLeadDrop($snapshot, $baselines, $clientId, $orgId, $channelType)) {
                $anomalies[] = $anomaly;
            }
        }

        // Multi-Metric Correlation (Making it "More Powerful")
        if ($fatigue = $this->detectCreativeFatigue($snapshot, $baselines, $clientId, $orgId, $channelType)) {
            $anomalies[] = $fatigue;
        }

        if ($friction = $this->detectFunnelFriction($snapshot, $baselines, $clientId, $orgId, $channelType)) {
            $anomalies[] = $friction;
        }

        return $anomalies;
    }

    /**
     * Correlates CTR drop with CPC spike to detect creative exhaustion.
     */
    public function detectCreativeFatigue(array $snapshot, array $baselines, $clientId, $orgId, $channel): ?array
    {
        if (! isset($snapshot['ctr'], $snapshot['cpc'], $baselines['ctr'], $baselines['cpc'])) {
            return null;
        }

        $ctrDeviation = $baselines['ctr'] > 0 ? (($snapshot['ctr'] - $baselines['ctr']) / $baselines['ctr']) * 100 : 0;
        $cpcDeviation = $baselines['cpc'] > 0 ? (($snapshot['cpc'] - $baselines['cpc']) / $baselines['cpc']) * 100 : 0;

        // If CTR is down > 20% AND CPC is up > 15%
        if ($ctrDeviation < -20 && $cpcDeviation > 15) {
            return $this->buildAnomalyArray($clientId, $orgId, $channel, 'creative_fatigue', 'CTR & CPC Correlation', $snapshot['ctr'], $baselines['ctr'], $ctrDeviation, [
                'cpc_increase' => round($cpcDeviation, 2),
                'technical_diagnosis' => 'High probability of creative saturation in target audience.',
            ]);
        }

        return null;
    }

    /**
     * Detects when traffic is stable but efficiency (CVR) collapses.
     */
    public function detectFunnelFriction(array $snapshot, array $baselines, $clientId, $orgId, $channel): ?array
    {
        if (! isset($snapshot['clicks'], $snapshot['conversions'], $baselines['clicks'], $baselines['conversions'])) {
            return null;
        }

        $clickDeviation = $baselines['clicks'] > 0 ? (($snapshot['clicks'] - $baselines['clicks']) / $baselines['clicks']) * 100 : 0;

        $currentCvr = $snapshot['clicks'] > 0 ? ($snapshot['conversions'] / $snapshot['clicks']) : 0;
        $baselineCvr = $baselines['clicks'] > 0 ? ($baselines['conversions'] / $baselines['clicks']) : 0;
        $cvrDeviation = $baselineCvr > 0 ? (($currentCvr - $baselineCvr) / $baselineCvr) * 100 : 0;

        // If clicks are stable (+/- 15%) but CVR collapsed > 30%
        if (abs($clickDeviation) < 15 && $cvrDeviation < -30) {
            return $this->buildAnomalyArray($clientId, $orgId, $channel, 'funnel_friction', 'Traffic/Conversion Gap', $currentCvr, $baselineCvr, $cvrDeviation, [
                'traffic_stability' => 'Stable',
                'technical_diagnosis' => 'Potential landing page error, tracking pixel failure, or conversion path blockage.',
            ]);
        }

        return null;
    }

    public function detectCtrDrop(array $snapshot, array $baselines, $clientId, $orgId, $channel): ?array
    {
        $threshold = $this->thresholds['ctr_drop'] ?? 20;
        $current = $snapshot['ctr'];
        $baseline = $baselines['ctr'];

        if ($baseline > 0 && $current < ($baseline * (1 - $threshold / 100))) {
            $deviation = (($current - $baseline) / $baseline) * 100;

            return $this->buildAnomalyArray($clientId, $orgId, $channel, 'ctr_drop', 'CTR', $current, $baseline, $deviation);
        }

        return null;
    }

    public function detectCpcSpike(array $snapshot, array $baselines, $clientId, $orgId, $channel): ?array
    {
        $threshold = $this->thresholds['cpc_spike'] ?? 30;
        $current = $snapshot['cpc'];
        $baseline = $baselines['cpc'];

        if ($baseline > 0 && $current > ($baseline * (1 + $threshold / 100))) {
            $deviation = (($current - $baseline) / $baseline) * 100;

            return $this->buildAnomalyArray($clientId, $orgId, $channel, 'cpc_spike', 'CPC', $current, $baseline, $deviation);
        }

        return null;
    }

    public function detectRoasDecline(array $snapshot, array $baselines, $clientId, $orgId, $channel): ?array
    {
        $minThreshold = $this->thresholds['roas_min'] ?? 1.5;
        $current = $snapshot['roas'];
        $baseline = $baselines['roas'] ?? 0;

        if ($current < $minThreshold) {
            $deviation = $baseline > 0 ? ((($current - $baseline) / $baseline) * 100) : -100;

            return $this->buildAnomalyArray($clientId, $orgId, $channel, 'roas_decline', 'ROAS', $current, $baseline, $deviation);
        }

        return null;
    }

    public function detectBudgetOverpace(array $snapshot, $clientId, $orgId, $channel): ?array
    {
        if (! in_array($channel, ['meta_ads', 'google_ads'])) {
            return null;
        }

        $threshold = $this->thresholds['budget_overrun'] ?? 10;
        $spend = $snapshot['spend'];

        $targetBudget = Campaign::where('client_id', $clientId)
            ->where('organization_id', $orgId)
            ->whereIn('status', ['running', 'active', 'ACTIVE'])
            ->sum('daily_budget');

        if ($targetBudget > 0 && $spend > ($targetBudget * (1 + $threshold / 100))) {
            $deviation = (($spend - $targetBudget) / $targetBudget) * 100;

            return $this->buildAnomalyArray($clientId, $orgId, $channel, 'budget_overrun', 'Daily Spend', $spend, $targetBudget, $deviation);
        }

        return null;
    }

    public function detectBudgetUnderpace(array $snapshot, $clientId, $orgId, $channel): ?array
    {
        if (! in_array($channel, ['meta_ads', 'google_ads'])) {
            return null;
        }

        $threshold = $this->thresholds['budget_underpace'] ?? 40;
        $spend = $snapshot['spend'];

        $targetBudget = Campaign::where('client_id', $clientId)
            ->where('organization_id', $orgId)
            ->whereIn('status', ['running', 'active', 'ACTIVE'])
            ->sum('daily_budget');

        if ($targetBudget > 0 && $spend < ($targetBudget * (1 - $threshold / 100))) {
            $deviation = (($spend - $targetBudget) / $targetBudget) * 100;

            return $this->buildAnomalyArray($clientId, $orgId, $channel, 'budget_underpace', 'Daily Spend', $spend, $targetBudget, $deviation);
        }

        return null;
    }

    public function detectEngagementDrop(array $snapshot, array $baselines, $clientId, $orgId, $channel): ?array
    {
        $threshold = $this->thresholds['engagement_drop'] ?? 25;
        $current = $snapshot['engagement_rate'];
        $baseline = $baselines['engagement_rate'];

        if ($baseline > 0 && $current < ($baseline * (1 - $threshold / 100))) {
            $deviation = (($current - $baseline) / $baseline) * 100;

            return $this->buildAnomalyArray($clientId, $orgId, $channel, 'engagement_drop', 'Engagement Rate', $current, $baseline, $deviation);
        }

        return null;
    }

    public function detectLeadDrop(array $snapshot, array $baselines, $clientId, $orgId, $channel): ?array
    {
        $threshold = $this->thresholds['lead_drop'] ?? 30;
        $current = $snapshot['leads'];
        $baseline = $baselines['leads'];

        if ($baseline > 0 && $current < ($baseline * (1 - $threshold / 100))) {
            $deviation = (($current - $baseline) / $baseline) * 100;

            return $this->buildAnomalyArray($clientId, $orgId, $channel, 'lead_drop', 'Leads', $current, $baseline, $deviation);
        }

        return null;
    }

    protected function calculateSeverity(float $deviationPct): string
    {
        $abs = abs($deviationPct);
        if ($abs >= 50) {
            return 'critical';
        }
        if ($abs >= 30) {
            return 'high';
        }
        if ($abs >= 20) {
            return 'medium';
        }

        return 'low';
    }

    protected function buildAnomalyArray($clientId, $orgId, $channel, $type, $metric, $current, $baseline, $deviation, array $context = []): array
    {
        return [
            'organization_id' => $orgId,
            'client_id' => $clientId,
            'anomaly_type' => $type,
            'channel_type' => $channel,
            'metric_name' => $metric,
            'current_value' => $current,
            'baseline_value' => $baseline,
            'deviation_percentage' => round($deviation, 2),
            'severity' => $this->calculateSeverity($deviation),
            'detected_at' => now(),
            'context' => $context,
        ];
    }
}
