<?php

namespace App\Livewire\Ads;

use App\Models\AdInsight;
use App\Models\AudienceInsight;
use App\Models\Campaign;
use App\Models\Creative;
use App\Models\FacebookLead;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Analytics extends Component
{
    public $dateRange = 30; // Last 30 days

    public function render()
    {
        $organizationId = Auth::user()->organization_id;
        $startDate = now()->subDays($this->dateRange)->toDateString();

        // 1. Account Overview Stats
        $overviewStats = AdInsight::where('organization_id', $organizationId)
            ->where('level', 'account')
            ->where('date', '>=', $startDate)
            ->selectRaw('SUM(spend) as total_spend, SUM(revenue) as total_revenue, SUM(impressions) as total_impressions, SUM(clicks) as total_clicks, SUM(conversions) as total_conversions, (CASE WHEN SUM(spend) > 0 THEN SUM(revenue) / SUM(spend) ELSE 0 END) as blended_roas')
            ->first();

        $totalLeads = FacebookLead::where('organization_id', $organizationId)
            ->where('created_at', '>=', $startDate)
            ->count();

        // 2. Campaign Performance
        $leadsByCampaign = FacebookLead::where('organization_id', $organizationId)
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('campaign_id')
            ->groupBy('campaign_id')
            ->selectRaw('campaign_id, COUNT(*) as cnt')
            ->pluck('cnt', 'campaign_id');

        $campaigns = Campaign::where('organization_id', $organizationId)
            ->with([
                'adInsights' => function ($query) use ($startDate) {
                    $query->where('date', '>=', $startDate)->where('level', 'campaign');
                },
            ])
            ->get()
            ->map(function ($campaign) use ($leadsByCampaign) {
                $insights = $campaign->adInsights;
                $spend = $insights->sum('spend');
                $clicks = $insights->sum('clicks');
                $impressions = $insights->sum('impressions');

                $leadsCount = (int) ($leadsByCampaign[$campaign->id] ?? 0);

                return [
                    'name' => $campaign->name,
                    'spend' => $spend,
                    'leads' => $leadsCount,
                    'cpl' => $leadsCount > 0 ? $spend / $leadsCount : 0,
                    'ctr' => $impressions > 0 ? ($clicks / $impressions) * 100 : 0,
                    'conversions' => $insights->sum('conversions'),
                ];
            })->sortByDesc('spend');

        // 3. Creative Performance Engine (Phase 16)
        $leadsByAd = FacebookLead::where('organization_id', $organizationId)
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('ad_id')
            ->groupBy('ad_id')
            ->selectRaw('ad_id, COUNT(*) as cnt')
            ->pluck('cnt', 'ad_id');

        $creatives = Creative::where('organization_id', $organizationId)
            ->with([
                'ad' => function ($query) use ($startDate) {
                    $query->with([
                        'adInsights' => function ($i) use ($startDate) {
                            $i->where('date', '>=', $startDate)->where('level', 'ad');
                        },
                    ]);
                },
            ])
            ->get()
            ->map(function ($creative) use ($leadsByAd) {
                $ad = $creative->ad;
                if (! $ad) {
                    return null;
                }

                $insights = $ad->adInsights;
                $spend = $insights->sum('spend');
                $clicks = $insights->sum('clicks');
                $impressions = $insights->sum('impressions');
                $leadsCount = (int) ($leadsByAd[$ad->id] ?? 0);

                return [
                    'asset_name' => $creative->headline ?: $creative->creative_id,
                    'headline' => $creative->headline,
                    'image_url' => $creative->image_url,
                    'video_id' => $creative->video_id,
                    'ctr' => $impressions > 0 ? ($clicks / $impressions) * 100 : 0,
                    'cpl' => $leadsCount > 0 ? $spend / $leadsCount : 0,
                    'engagement_rate' => $impressions > 0 ? ($clicks / $impressions) * 100 : 0, // Simplified engagement
                    'leads' => $leadsCount,
                    'spend' => $spend,
                    'impressions' => $impressions,
                ];
            })
            ->filter()
            ->filter(fn ($c) => $c['spend'] > 0 || $c['impressions'] > 0)
            ->sortByDesc('ctr');

        // 4. Audience Intelligence (Breakdowns)
        $processBreakdown = function ($type, string $groupCol) use ($organizationId, $startDate) {
            $types = is_array($type) ? $type : [$type];

            $rows = AudienceInsight::where('organization_id', $organizationId)
                ->where('date', '>=', $startDate)
                ->whereIn('breakdown_type', $types)
                ->whereNotNull($groupCol)
                ->where($groupCol, '!=', '')
                ->groupBy($groupCol)
                ->selectRaw($groupCol.' as k, SUM(spend) as spend, SUM(leads) as leads')
                ->orderByDesc('leads')
                ->get();

            return $rows->mapWithKeys(function ($row) {
                $key = (string) $row->k;
                $spend = (float) $row->spend;
                $leads = (int) $row->leads;

                return [$key => [
                    'spend' => $spend,
                    'leads' => $leads,
                    'cpl' => $leads > 0 ? $spend / $leads : 0,
                ]];
            });
        };

        return view('livewire.ads.analytics', [
            'overview' => $overviewStats,
            'totalLeads' => $totalLeads,
            'campaigns' => $campaigns,
            'creatives' => $creatives,
            'ageStats' => $processBreakdown('age', 'age'),
            'genderStats' => $processBreakdown('gender', 'gender'),
            'deviceStats' => $processBreakdown('device_platform', 'device'),
            'placementStats' => $processBreakdown('publisher_platform,platform_position', 'placement'),
            'cityStats' => $processBreakdown(['region', 'city'], 'city'),
        ])->layout('layouts.app');
    }
}
