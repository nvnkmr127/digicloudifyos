<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\Lead;
use App\Models\Task;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function getDashboardMetrics(string $organizationId, ?string $period = '30days'): array
    {
        $cacheKey = "analytics.dashboard.{$organizationId}.{$period}";

        return Cache::remember($cacheKey, 600, function () use ($organizationId, $period) {
            [$startDate, $endDate] = $this->getPeriodDates($period);

            return [
                'campaigns' => $this->getCampaignMetrics($organizationId, $startDate, $endDate),
                'leads' => $this->getLeadMetrics($organizationId, $startDate, $endDate),
                'tasks' => $this->getTaskMetrics($organizationId, $startDate, $endDate),
                'performance' => $this->getPerformanceMetrics($organizationId, $startDate, $endDate),
                'trends' => $this->getTrendData($organizationId, $startDate, $endDate),
            ];
        });
    }

    protected function getCampaignMetrics(string $organizationId, $startDate, $endDate): array
    {
        $campaigns = Campaign::where('organization_id', $organizationId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        return [
            'total' => $campaigns->count(),
            'running' => $campaigns->where('status', 'running')->count(),
            'completed' => $campaigns->where('status', 'completed')->count(),
            'planning' => $campaigns->where('status', 'planning')->count(),
            'by_status' => $campaigns->groupBy('status')->map->count(),
            'by_objective' => $campaigns->groupBy('objective')->map->count(),
            'total_budget' => $campaigns->sum('lifetime_budget') + $campaigns->sum('daily_budget') * 30,
        ];
    }

    protected function getLeadMetrics(string $organizationId, $startDate, $endDate): array
    {
        $leads = Lead::where('organization_id', $organizationId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        return [
            'total' => $leads->count(),
            'new' => $leads->where('status', 'new')->count(),
            'qualified' => $leads->where('status', 'qualified')->count(),
            'converted' => $leads->where('status', 'converted')->count(),
            'by_status' => $leads->groupBy('status')->map->count(),
            'by_source' => $leads->groupBy('source')->map->count(),
            'conversion_rate' => $leads->count() > 0
                ? ($leads->where('status', 'converted')->count() / $leads->count()) * 100
                : 0,
        ];
    }

    protected function getTaskMetrics(string $organizationId, $startDate, $endDate): array
    {
        $tasks = Task::where('organization_id', $organizationId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        return [
            'total' => $tasks->count(),
            'pending' => $tasks->where('status', 'pending')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'completed' => $tasks->where('status', 'completed')->count(),
            'overdue' => $tasks->where('due_date', '<', now())->where('status', '!=', 'completed')->count(),
            'by_priority' => $tasks->groupBy('priority')->map->count(),
            'completion_rate' => $tasks->count() > 0
                ? ($tasks->where('status', 'completed')->count() / $tasks->count()) * 100
                : 0,
        ];
    }

    protected function getPerformanceMetrics(string $organizationId, $startDate, $endDate): array
    {
        $campaigns = Campaign::where('organization_id', $organizationId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('dailyMetrics')
            ->get();

        $totalMetrics = [
            'total_spend' => 0,
            'total_impressions' => 0,
            'total_clicks' => 0,
            'total_conversions' => 0,
        ];

        foreach ($campaigns as $campaign) {
            $totalMetrics['total_spend'] += $campaign->dailyMetrics->sum('spend');
            $totalMetrics['total_impressions'] += $campaign->dailyMetrics->sum('impressions');
            $totalMetrics['total_clicks'] += $campaign->dailyMetrics->sum('clicks');
            $totalMetrics['total_conversions'] += $campaign->dailyMetrics->sum('conversions');
        }

        $totalMetrics['avg_ctr'] = $totalMetrics['total_impressions'] > 0
            ? ($totalMetrics['total_clicks'] / $totalMetrics['total_impressions']) * 100
            : 0;

        $totalMetrics['avg_cpc'] = $totalMetrics['total_clicks'] > 0
            ? $totalMetrics['total_spend'] / $totalMetrics['total_clicks']
            : 0;

        $totalMetrics['avg_conversion_rate'] = $totalMetrics['total_clicks'] > 0
            ? ($totalMetrics['total_conversions'] / $totalMetrics['total_clicks']) * 100
            : 0;

        return $totalMetrics;
    }

    protected function getTrendData(string $organizationId, $startDate, $endDate): array
    {
        $campaigns = Campaign::where('organization_id', $organizationId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $leads = Lead::where('organization_id', $organizationId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'campaigns_over_time' => $campaigns,
            'leads_over_time' => $leads,
        ];
    }

    protected function getPeriodDates(string $period): array
    {
        return match ($period) {
            '7days' => [now()->subDays(7), now()],
            '30days' => [now()->subDays(30), now()],
            '90days' => [now()->subDays(90), now()],
            'thisMonth' => [now()->startOfMonth(), now()->endOfMonth()],
            'lastMonth' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'thisYear' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->subDays(30), now()],
        };
    }

    public function clearCache(string $organizationId): void
    {
        $periods = ['7days', '30days', '90days', 'thisMonth', 'lastMonth', 'thisYear'];

        foreach ($periods as $period) {
            Cache::forget("analytics.dashboard.{$organizationId}.{$period}");
        }
    }
}
