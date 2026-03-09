<?php

namespace App\Services;

use App\Models\Campaign;
use App\Repositories\CampaignRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CampaignService
{
    public function __construct(
        protected CampaignRepository $repository
    ) {}

    public function getAllForOrganization(string $organizationId, array $filters = []): Collection
    {
        $cacheKey = "campaigns.org.{$organizationId}.".md5(serialize($filters));

        return Cache::remember($cacheKey, 300, function () use ($organizationId, $filters) {
            return $this->repository->getByOrganization($organizationId, $filters);
        });
    }

    public function create(array $data): Campaign
    {
        try {
            DB::beginTransaction();

            $campaign = $this->repository->create($data);

            event(new \App\Events\CampaignCreated($campaign));

            $this->clearCache($campaign->organization_id);

            DB::commit();

            return $campaign;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Campaign creation failed', ['error' => $e->getMessage(), 'data' => $data]);
            throw $e;
        }
    }

    public function update(Campaign $campaign, array $data): Campaign
    {
        try {
            DB::beginTransaction();

            $oldStatus = $campaign->status;
            $campaign = $this->repository->update($campaign, $data);

            if (isset($data['status']) && $oldStatus !== $data['status']) {
                event(new \App\Events\CampaignStatusChanged($campaign, $oldStatus));
            }

            $this->clearCache($campaign->organization_id);

            DB::commit();

            return $campaign;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Campaign update failed', ['campaign_id' => $campaign->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function delete(Campaign $campaign): bool
    {
        try {
            DB::beginTransaction();

            $organizationId = $campaign->organization_id;
            $result = $this->repository->delete($campaign);

            event(new \App\Events\CampaignDeleted($campaign));

            $this->clearCache($organizationId);

            DB::commit();

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Campaign deletion failed', ['campaign_id' => $campaign->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getMetrics(Campaign $campaign): array
    {
        $cacheKey = "campaign.metrics.{$campaign->id}";

        return Cache::remember($cacheKey, 300, function () use ($campaign) {
            return [
                'total_spend' => $campaign->dailyMetrics()->sum('spend'),
                'total_impressions' => $campaign->dailyMetrics()->sum('impressions'),
                'total_clicks' => $campaign->dailyMetrics()->sum('clicks'),
                'total_conversions' => $campaign->dailyMetrics()->sum('conversions'),
                'avg_ctr' => $campaign->dailyMetrics()->avg('ctr'),
                'avg_cpc' => $campaign->dailyMetrics()->avg('cpc'),
                'avg_cpm' => $campaign->dailyMetrics()->avg('cpm'),
                'tasks_count' => $campaign->tasks()->count(),
                'creative_requests_count' => $campaign->creativeRequests()->count(),
                'alerts_count' => $campaign->alerts()->count(),
            ];
        });
    }

    protected function clearCache(string $organizationId): void
    {
        Cache::tags(['campaigns', "org.{$organizationId}"])->flush();
    }
}
