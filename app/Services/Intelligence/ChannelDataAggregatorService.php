<?php

namespace App\Services\Intelligence;

use App\Models\AdInsight;
use App\Models\Campaign;
use App\Models\FacebookLead;
use App\Models\SocialPost;
use App\Models\Client;
use App\Models\DailyMetric;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Lead;
use App\Models\ConversionEvent;
use App\Models\FunnelMetric;

class ChannelDataAggregatorService
{
    /**
     * Aggregates Meta Ads data for a specific client and date.
     */
    public function aggregateMetaAds(string $clientId, string $orgId, string $date): array
    {
        $metrics = AdInsight::where('organization_id', $orgId)
            ->whereHas('campaign', function ($query) use ($clientId) {
                $query->where('client_id', $clientId);
            })
            ->whereDate('date', $date)
            ->select(
                DB::raw('SUM(spend) as total_spend'),
                DB::raw('SUM(impressions) as total_impressions'),
                DB::raw('SUM(clicks) as total_clicks'),
                DB::raw('SUM(conversions) as total_conversions'),
                DB::raw('SUM(revenue) as total_revenue'),
                DB::raw('AVG(ctr) as avg_ctr'),
                DB::raw('AVG(cpc) as avg_cpc'),
                DB::raw('AVG(cpm) as avg_cpm'),
                DB::raw('AVG(roas) as avg_roas')
            )
            ->groupBy('date')
            ->first();

        if (!$metrics || $metrics->total_impressions == 0) return [];

        return [
            'impressions' => (int) $metrics->total_impressions,
            'clicks' => (int) $metrics->total_clicks,
            'spend' => (float) $metrics->total_spend,
            'conversions' => (float) $metrics->total_conversions,
            'revenue' => (float) $metrics->total_revenue,
            'ctr' => (float) $metrics->avg_ctr,
            'cpc' => (float) $metrics->avg_cpc,
            'cpm' => (float) $metrics->avg_cpm,
            'roas' => (float) $metrics->avg_roas,
        ];
    }

    /**
     * Aggregates Google Ads data for a specific client and date using DailyMetric model.
     */
    public function aggregateGoogleAds(string $clientId, string $orgId, string $date): array
    {
        $metrics = DailyMetric::whereHas('campaign', function ($query) use ($clientId, $orgId) {
                $query->where('client_id', $clientId)->where('organization_id', $orgId);
            })
            ->whereDate('date', $date)
            ->select(
                DB::raw('SUM(spend) as total_spend'),
                DB::raw('SUM(impressions) as total_impressions'),
                DB::raw('SUM(clicks) as total_clicks'),
                DB::raw('SUM(conversions) as total_conversions'),
                DB::raw('SUM(revenue) as total_revenue')
            )
            ->groupBy('date')
            ->first();

        if (!$metrics || $metrics->total_impressions == 0) return [];

        $impressions = (int) $metrics->total_impressions;
        $clicks = (int) $metrics->total_clicks;
        $spend = (float) $metrics->total_spend;

        return [
            'impressions' => $impressions,
            'clicks' => $clicks,
            'spend' => $spend,
            'conversions' => (float) $metrics->total_conversions,
            'revenue' => (float) $metrics->total_revenue,
            'ctr' => $impressions > 0 ? ($clicks / $impressions) : 0,
            'cpc' => $clicks > 0 ? ($spend / $clicks) : 0,
            'cpm' => $impressions > 0 ? ($spend / $impressions * 1000) : 0,
            'roas' => $spend > 0 ? ((float) $metrics->total_revenue / $spend) : 0,
        ];
    }

    /**
     * Aggregates organic social engagement data.
     */
    public function aggregateSocialOrganic(string $clientId, string $orgId, string $date): array
    {
        $posts = SocialPost::where('organization_id', $orgId)
            ->whereHas('campaign', function ($query) use ($clientId) {
                $query->where('client_id', $clientId);
            })
            ->whereDate('published_at', $date)
            ->get();

        if ($posts->isEmpty()) return [];

        $totalReach = 0;
        $totalEngagement = 0;
        $totalImpressions = 0;

        foreach ($posts as $post) {
            $m = $post->metrics ?? [];
            $totalReach += (int) ($m['reach'] ?? 0);
            $totalEngagement += (int) ($m['engagement'] ?? 0);
            $totalImpressions += (int) ($m['impressions'] ?? 0);
        }

        return [
            'reach' => $totalReach,
            'engagement_rate' => $totalImpressions > 0 ? ($totalEngagement / $totalImpressions) : 0,
            'impressions' => $totalImpressions,
        ];
    }

    /**
     * Aggregates lead volume with breakdown.
     */
    public function aggregateLeads(string $clientId, string $orgId, string $date): array
    {
        $totalLeads = Lead::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->whereDate('created_at', $date)
            ->count();

        $highIntent = Lead::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->whereDate('created_at', $date)
            ->where('intent_score', '>=', 70)
            ->count();

        return [
            'leads' => $totalLeads,
            'high_intent_leads' => $highIntent,
        ];
    }

    /**
     * Aggregates funnel conversions.
     */
    public function aggregateConversions(string $clientId, string $orgId, string $date): array
    {
        $conversions = ConversionEvent::where('client_id', $clientId)
            ->whereDate('event_date', $date)
            ->sum('event_count');

        $funnelStep = FunnelMetric::where('client_id', $clientId)
            ->whereDate('metric_date', $date)
            ->orderBy('step_order', 'desc')
            ->first();

        return [
            'conversions' => (int) $conversions,
            'bottom_funnel_completion' => $funnelStep?->conversion_rate ?? 0,
        ];
    }

    /**
     * Entry point to aggregate all channel data for a client.
     */
    public function aggregateAll(string $clientId, string $orgId, string $date): array
    {
        $results = [];
        
        $meta = $this->aggregateMetaAds($clientId, $orgId, $date);
        if (!empty($meta)) $results['meta_ads'] = $meta;

        $google = $this->aggregateGoogleAds($clientId, $orgId, $date);
        if (!empty($google)) $results['google_ads'] = $google;

        $social = $this->aggregateSocialOrganic($clientId, $orgId, $date);
        if (!empty($social)) $results['social_organic'] = $social;

        $leads = $this->aggregateLeads($clientId, $orgId, $date);
        if (!empty($leads) && $leads['leads'] > 0) $results['leads'] = $leads;

        $conversions = $this->aggregateConversions($clientId, $orgId, $date);
        if (!empty($conversions)) $results['conversions'] = $conversions;

        return $results;
    }
}
