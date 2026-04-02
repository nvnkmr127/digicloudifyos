<?php

namespace App\Services\Intelligence;

use App\Models\AdInsight;
use App\Models\AmazonSpDailyMetric;
use App\Models\Client;
use App\Models\ConversionEvent;
use App\Models\DailyMetric;
use App\Models\FunnelMetric;
use App\Models\GoogleAnalyticsDailyMetric;
use App\Models\GoogleBusinessProfileDailyMetric;
use App\Models\GoogleMerchantCenterDailyMetric;
use App\Models\GoogleSearchConsoleDailyMetric;
use App\Models\InstagramDailyMetric;
use App\Models\Lead;
use App\Models\LinkedInOrganizationDailyMetric;
use App\Models\MetaPageDailyMetric;
use App\Models\ShopifyDailyMetric;
use App\Models\SocialPost;
use App\Models\TwitterDailyMetric;
use App\Models\WooCommerceDailyMetric;
use Illuminate\Support\Facades\DB;

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

        if (! $metrics || $metrics->total_impressions == 0) {
            return [];
        }

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

        if (! $metrics || $metrics->total_impressions == 0) {
            return [];
        }

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

        if ($posts->isEmpty()) {
            return [];
        }

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

    public function aggregateGa4(string $clientId, string $orgId, string $date): array
    {
        $metric = GoogleAnalyticsDailyMetric::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->whereDate('metric_date', $date)
            ->first();

        if (! $metric) {
            return [];
        }

        return [
            'conversions' => (int) $metric->conversions,
            'revenue' => (float) $metric->revenue,
            'raw_data' => [
                'sessions' => (int) $metric->sessions,
                'users' => (int) $metric->users,
                'new_users' => (int) $metric->new_users,
                'engaged_sessions' => (int) $metric->engaged_sessions,
            ],
        ];
    }

    public function aggregateSearchConsole(string $clientId, string $orgId, string $date): array
    {
        $metric = GoogleSearchConsoleDailyMetric::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->whereDate('metric_date', $date)
            ->first();

        if (! $metric) {
            return [];
        }

        return [
            'impressions' => (int) $metric->impressions,
            'clicks' => (int) $metric->clicks,
            'ctr' => $metric->ctr !== null ? (float) $metric->ctr : null,
            'raw_data' => [
                'avg_position' => $metric->avg_position !== null ? (float) $metric->avg_position : null,
                'site_url' => $metric->site_url,
            ],
        ];
    }

    public function aggregateShopify(string $clientId, string $orgId, string $date): array
    {
        $metric = ShopifyDailyMetric::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->whereDate('metric_date', $date)
            ->first();

        if (! $metric) {
            return [];
        }

        return [
            'conversions' => (int) $metric->orders_count,
            'revenue' => (float) $metric->net_sales,
            'raw_data' => [
                'orders_count' => (int) $metric->orders_count,
                'gross_sales' => (float) $metric->gross_sales,
                'refunds' => (float) $metric->refunds,
                'currency' => $metric->currency_code,
                'shop' => $metric->shop_domain,
            ],
        ];
    }

    public function aggregateWooCommerce(string $clientId, string $orgId, string $date): array
    {
        $metric = WooCommerceDailyMetric::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->whereDate('metric_date', $date)
            ->first();

        if (! $metric) {
            return [];
        }

        return [
            'conversions' => (int) $metric->orders_count,
            'revenue' => (float) $metric->net_sales,
            'raw_data' => [
                'orders_count' => (int) $metric->orders_count,
                'gross_sales' => (float) $metric->gross_sales,
                'refunds' => (float) $metric->refunds,
                'currency' => $metric->currency_code,
                'store_url' => $metric->store_url,
            ],
        ];
    }

    public function aggregateFacebookOrganic(string $clientId, string $orgId, string $date): array
    {
        $metric = MetaPageDailyMetric::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->whereDate('metric_date', $date)
            ->first();

        if (! $metric) {
            return [];
        }

        $impressions = (int) $metric->impressions;
        $engagements = (int) $metric->post_engagements;

        return [
            'impressions' => $impressions,
            'reach' => (int) $metric->reach,
            'engagement_rate' => $impressions > 0 ? ($engagements / $impressions) : 0,
            'raw_data' => [
                'page_id' => $metric->page_id,
                'page_name' => $metric->page_name,
                'engaged_users' => (int) $metric->engaged_users,
                'post_engagements' => $engagements,
            ],
        ];
    }

    public function aggregateInstagram(string $clientId, string $orgId, string $date): array
    {
        $metric = InstagramDailyMetric::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->whereDate('metric_date', $date)
            ->first();

        if (! $metric) {
            return [];
        }

        return [
            'impressions' => (int) $metric->impressions,
            'reach' => (int) $metric->reach,
            'raw_data' => [
                'instagram_account_id' => $metric->instagram_account_id,
                'profile_views' => (int) $metric->profile_views,
                'website_clicks' => (int) $metric->website_clicks,
            ],
        ];
    }

    public function aggregateTwitter(string $clientId, string $orgId, string $date): array
    {
        $metric = TwitterDailyMetric::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->whereDate('metric_date', $date)
            ->first();

        if (! $metric) {
            return [];
        }

        return [
            'raw_data' => [
                'followers' => (int) $metric->followers,
                'following' => (int) $metric->following,
                'tweets' => (int) $metric->tweets,
                'listed' => (int) $metric->listed,
                'username' => $metric->twitter_username,
            ],
        ];
    }

    public function aggregateLinkedInOrganic(string $clientId, string $orgId, string $date): array
    {
        $metric = LinkedInOrganizationDailyMetric::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->whereDate('metric_date', $date)
            ->first();

        if (! $metric) {
            return [];
        }

        $impressions = (int) $metric->impressions;
        $clicks = (int) $metric->clicks;

        return [
            'impressions' => $impressions,
            'clicks' => $clicks,
            'ctr' => $impressions > 0 ? ($clicks / $impressions) : null,
            'raw_data' => [
                'followers' => (int) $metric->followers,
                'likes' => (int) $metric->likes,
                'comments' => (int) $metric->comments,
                'shares' => (int) $metric->shares,
                'organization_urn' => $metric->linkedin_organization_urn,
            ],
        ];
    }

    public function aggregateMerchantCenter(string $clientId, string $orgId, string $date): array
    {
        $metric = GoogleMerchantCenterDailyMetric::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->whereDate('metric_date', $date)
            ->first();

        if (! $metric) {
            return [];
        }

        $checked = (int) $metric->items_checked;
        $disapproved = (int) $metric->items_disapproved;
        $disapprovedRate = $checked > 0 ? ($disapproved / $checked) : 0;

        return [
            'raw_data' => [
                'merchant_id' => $metric->merchant_id,
                'items_checked' => $checked,
                'items_disapproved' => $disapproved,
                'items_pending' => (int) $metric->items_pending,
                'items_approved' => (int) $metric->items_approved,
                'issue_count' => (int) $metric->issue_count,
                'issue_breakdown' => $metric->issue_breakdown,
                'top_issue_examples' => $metric->top_issue_examples,
                'feed_count' => (int) $metric->feed_count,
                'feed_issue_count' => (int) $metric->feed_issue_count,
                'feed_statuses' => $metric->feed_statuses,
                'disapproved_rate' => $disapprovedRate,
                'truncated' => (bool) $metric->truncated,
            ],
        ];
    }

    public function aggregateGoogleBusinessProfile(string $clientId, string $orgId, string $date): array
    {
        $metric = GoogleBusinessProfileDailyMetric::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->whereDate('metric_date', $date)
            ->first();

        if (! $metric) {
            return [];
        }

        $impressions = (int) ($metric->impressions_search_desktop + $metric->impressions_search_mobile + $metric->impressions_maps_desktop + $metric->impressions_maps_mobile);

        return [
            'impressions' => $impressions,
            'clicks' => (int) $metric->website_clicks,
            'leads' => (int) $metric->call_clicks,
            'conversions' => (int) $metric->call_clicks,
            'spend' => 0,
            'revenue' => 0,
            'raw_data' => [
                'website_clicks' => (int) $metric->website_clicks,
                'call_clicks' => (int) $metric->call_clicks,
                'directions_requests' => (int) $metric->directions_requests,
                'impressions_search_desktop' => (int) $metric->impressions_search_desktop,
                'impressions_search_mobile' => (int) $metric->impressions_search_mobile,
                'impressions_maps_desktop' => (int) $metric->impressions_maps_desktop,
                'impressions_maps_mobile' => (int) $metric->impressions_maps_mobile,
            ],
        ];
    }

    public function aggregateAmazon(string $clientId, string $orgId, string $date): array
    {
        $metric = AmazonSpDailyMetric::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->whereDate('metric_date', $date)
            ->first();

        if (! $metric) {
            return [];
        }

        return [
            'conversions' => (int) $metric->orders_count,
            'revenue' => (float) $metric->net_sales,
            'raw_data' => [
                'orders_count' => (int) $metric->orders_count,
                'gross_sales' => (float) $metric->gross_sales,
                'currency' => $metric->currency_code,
                'marketplace_id' => $metric->marketplace_id,
                'seller_id' => $metric->seller_id,
                'truncated' => (bool) $metric->truncated,
            ],
        ];
    }

    /**
     * Entry point to aggregate all channel data for a client.
     */
    public function aggregateAll(string $clientId, string $orgId, string $date): array
    {
        $results = [];

        $meta = $this->aggregateMetaAds($clientId, $orgId, $date);
        if (! empty($meta)) {
            $results['meta_ads'] = $meta;
        }

        $google = $this->aggregateGoogleAds($clientId, $orgId, $date);
        if (! empty($google)) {
            $results['google_ads'] = $google;
        }

        $social = $this->aggregateSocialOrganic($clientId, $orgId, $date);
        if (! empty($social)) {
            $results['social_organic'] = $social;
        }

        $leads = $this->aggregateLeads($clientId, $orgId, $date);
        if (! empty($leads) && $leads['leads'] > 0) {
            $results['leads'] = $leads;
        }

        $conversions = $this->aggregateConversions($clientId, $orgId, $date);
        if (! empty($conversions)) {
            $results['conversions'] = $conversions;
        }

        $ga4 = $this->aggregateGa4($clientId, $orgId, $date);
        if (! empty($ga4)) {
            $results['ga4'] = $ga4;
        }

        $gsc = $this->aggregateSearchConsole($clientId, $orgId, $date);
        if (! empty($gsc)) {
            $results['search_console'] = $gsc;
        }

        $shopify = $this->aggregateShopify($clientId, $orgId, $date);
        if (! empty($shopify)) {
            $results['shopify'] = $shopify;
        }

        $woo = $this->aggregateWooCommerce($clientId, $orgId, $date);
        if (! empty($woo)) {
            $results['woocommerce'] = $woo;
        }

        $fb = $this->aggregateFacebookOrganic($clientId, $orgId, $date);
        if (! empty($fb)) {
            $results['facebook_organic'] = $fb;
        }

        $ig = $this->aggregateInstagram($clientId, $orgId, $date);
        if (! empty($ig)) {
            $results['instagram'] = $ig;
        }

        $tw = $this->aggregateTwitter($clientId, $orgId, $date);
        if (! empty($tw)) {
            $results['twitter'] = $tw;
        }

        $li = $this->aggregateLinkedInOrganic($clientId, $orgId, $date);
        if (! empty($li)) {
            $results['linkedin_organic'] = $li;
        }

        $gbp = $this->aggregateGoogleBusinessProfile($clientId, $orgId, $date);
        if (! empty($gbp)) {
            $results['google_business_profile'] = $gbp;
        }

        $gmc = $this->aggregateMerchantCenter($clientId, $orgId, $date);
        if (! empty($gmc)) {
            $results['google_merchant_center'] = $gmc;
        }

        $amazon = $this->aggregateAmazon($clientId, $orgId, $date);
        if (! empty($amazon)) {
            $results['amazon'] = $amazon;
        }

        return $results;
    }
}
