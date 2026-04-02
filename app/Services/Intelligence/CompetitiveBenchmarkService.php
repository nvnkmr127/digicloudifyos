<?php

namespace App\Services\Intelligence;

use App\Models\Client;
use App\Models\PerformanceSnapshot;

class CompetitiveBenchmarkService
{
    public function runForOrganization(string $orgId, string $date): void
    {
        $clients = Client::where('organization_id', $orgId)->active()->get(['id', 'industry']);

        $snapshots = PerformanceSnapshot::where('organization_id', $orgId)
            ->where('snapshot_date', $date)
            ->get();

        if ($snapshots->isEmpty()) {
            return;
        }

        $byIndustry = $clients->keyBy('id')->map(fn ($c) => $c->industry ?: 'unknown');

        $channels = $snapshots->groupBy('channel_type');

        foreach ($channels as $channelType => $channelSnapshots) {
            $this->applyBenchmarksForChannel($channelType, $channelSnapshots, $byIndustry);
        }
    }

    protected function applyBenchmarksForChannel(string $channelType, $channelSnapshots, $clientIndustryMap): void
    {
        $metrics = [
            'ctr',
            'cpc',
            'roas',
            'spend',
            'revenue',
            'conversions',
            'leads',
            'engagement_rate',
            'reach',
            'impressions',
            'clicks',
        ];

        $industryGroups = $channelSnapshots->groupBy(function ($s) use ($clientIndustryMap) {
            return $clientIndustryMap[$s->client_id] ?? 'unknown';
        });

        foreach ($industryGroups as $industry => $group) {
            $bench = [];

            foreach ($metrics as $metric) {
                $values = $group->pluck($metric)->filter(fn ($v) => $v !== null)->map(fn ($v) => (float) $v)->values();
                if ($values->isEmpty()) {
                    continue;
                }
                $bench[$metric] = $values->median();
            }

            foreach ($group as $snapshot) {
                $raw = $snapshot->raw_data ?? [];
                $raw['benchmarks'] = array_merge($raw['benchmarks'] ?? [], [
                    'industry' => $industry,
                    'channel_type' => $channelType,
                    'medians' => $bench,
                    'deltas' => $this->buildDeltas($snapshot, $bench),
                ]);

                $snapshot->update([
                    'raw_data' => $raw,
                ]);
            }
        }
    }

    protected function buildDeltas(PerformanceSnapshot $snapshot, array $bench): array
    {
        $deltas = [];

        foreach ($bench as $metric => $median) {
            $current = $snapshot->{$metric};
            if ($current === null) {
                continue;
            }
            $median = (float) $median;
            $current = (float) $current;

            $deltas[$metric] = [
                'current' => $current,
                'median' => $median,
                'diff' => $current - $median,
                'diff_pct' => $median != 0.0 ? (($current - $median) / $median) * 100 : null,
            ];
        }

        return $deltas;
    }
}
