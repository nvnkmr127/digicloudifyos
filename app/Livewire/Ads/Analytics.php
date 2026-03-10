<?php

namespace App\Livewire\Ads;

use App\Models\AdAccount;
use App\Models\AdInsight;
use App\Models\AudienceInsight;
use App\Models\Campaign;
use App\Models\FacebookLead;
use Livewire\Component;

class Analytics extends Component
{
    public $dateRange = 30; // Last 30 days

    public function render()
    {
        $organizationId = auth()->user()->organization_id;
        $startDate = now()->subDays($this->dateRange)->toDateString();

        // 1. Account Overview Stats
        $overviewStats = AdInsight::where('organization_id', $organizationId)
            ->where('level', 'account')
            ->where('date', '>=', $startDate)
            ->selectRaw('SUM(spend) as total_spend, SUM(impressions) as total_impressions, SUM(clicks) as total_clicks, SUM(conversions) as total_conversions, AVG(roas) as avg_roas')
            ->first();

        $totalLeads = FacebookLead::where('organization_id', $organizationId)
            ->where('created_at', '>=', $startDate)
            ->count();

        // 2. Campaign Performance
        $campaigns = Campaign::where('organization_id', $organizationId)
            ->with([
                'adInsights' => function ($query) use ($startDate) {
                    $query->where('date', '>=', $startDate)->where('level', 'campaign');
                }
            ])
            ->get()
            ->map(function ($campaign) {
                $insights = $campaign->adInsights;
                $spend = $insights->sum('spend');
                $clicks = $insights->sum('clicks');
                $impressions = $insights->sum('impressions');

                // Get leads for this campaign
                $leadsCount = FacebookLead::where('campaign_id', $campaign->id)->count();

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
        $creatives = \App\Models\Creative::where('organization_id', $organizationId)
            ->with([
                'ad' => function ($query) use ($startDate) {
                    $query->with([
                        'adInsights' => function ($i) use ($startDate) {
                            $i->where('date', '>=', $startDate)->where('level', 'ad');
                        }
                    ]);
                }
            ])
            ->get()
            ->map(function ($creative) {
                $ad = $creative->ad;
                if (!$ad)
                    return null;

                $insights = $ad->adInsights;
                $spend = $insights->sum('spend');
                $clicks = $insights->sum('clicks');
                $impressions = $insights->sum('impressions');
                $leadsCount = FacebookLead::where('ad_id', $ad->id)
                    ->where('created_at', '>=', now()->subDays($this->dateRange))
                    ->count();

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
            ->filter(fn($c) => $c['spend'] > 0 || $c['impressions'] > 0)
            ->sortByDesc('ctr');

        // 4. Audience Intelligence (Breakdowns)
        $audienceInsights = AudienceInsight::where('organization_id', $organizationId)
            ->where('date', '>=', $startDate)
            ->get();

        $processBreakdown = function ($type) use ($audienceInsights) {
            return $audienceInsights->where('breakdown_type', $type)
                ->groupBy('dimension_1')
                ->map(function ($group) {
                    $spend = $group->sum('spend');
                    $leads = $group->sum('conversions'); // Using conversions as Leads proxy for breakdowns
                    return [
                        'spend' => $spend,
                        'leads' => $leads,
                        'cpl' => $leads > 0 ? $spend / $leads : 0,
                    ];
                })->sortByDesc('leads');
        };

        return view('livewire.ads.analytics', [
            'overview' => $overviewStats,
            'totalLeads' => $totalLeads,
            'campaigns' => $campaigns,
            'creatives' => $creatives,
            'ageStats' => $processBreakdown('age'),
            'genderStats' => $processBreakdown('gender'),
            'deviceStats' => $processBreakdown('device_platform'),
            'placementStats' => $processBreakdown('publisher_platform,platform_position'),
            'cityStats' => $processBreakdown('city'),
        ])->layout('layouts.app');
    }
}
