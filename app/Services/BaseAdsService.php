<?php

namespace App\Services;

use App\Models\AdAccount;
use App\Models\Campaign;
use App\Models\AdSet;
use App\Models\Ad;
use App\Models\DailyMetric;
use Illuminate\Support\Collection;

abstract class BaseAdsService
{
    abstract public function getAuthUrl(): string;
    abstract public function handleCallback(array $data, string $organizationId, string $clientId): AdAccount;
    abstract public function syncCampaigns(\App\Models\AdAccount $adAccount): Collection;
    abstract public function syncAdSets(\App\Models\Campaign $campaign): Collection;
    abstract public function syncAds(\App\Models\AdSet $adSet): Collection;
    abstract public function syncMetrics(\App\Models\Campaign $campaign, string $startDate, string $endDate): Collection;
    abstract public function syncInsights(AdAccount $adAccount, string $startDate, string $endDate, string $level): Collection;
    public function syncBreakdowns(AdAccount $adAccount, string $startDate, string $endDate, string $level = 'campaign', array $breakdowns = []): Collection
    {
        return collect();
    }

    public function syncFullHierarchy(AdAccount $adAccount): void
    {
        // 1. Sync Campaigns
        $campaigns = $this->syncCampaigns($adAccount);

        // 2. Sync AdSets and Ads
        foreach ($campaigns as $campaign) {
            $adSets = $this->syncAdSets($campaign);
            foreach ($adSets as $adSet) {
                $this->syncAds($adSet);
            }
        }
    }

    abstract public function pauseCampaign(\App\Models\Campaign $campaign): bool;
    abstract public function archiveCampaign(\App\Models\Campaign $campaign): bool;
    abstract public function deleteCampaign(\App\Models\Campaign $campaign): bool;

    protected function refreshAccessToken(AdAccount $adAccount): void
    {
        // Implementation for refreshing token if expired
    }
}
