<?php

namespace App\Services;

use App\Models\Lead;
use App\Repositories\LeadRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeadService
{
    public function __construct(
        protected LeadRepository $repository
    ) {}

    public function getAllForOrganization(string $organizationId, array $filters = []): Collection
    {
        $cacheKey = "leads.org.{$organizationId}.".md5(serialize($filters));

        return $this->withCache($cacheKey, ["org.{$organizationId}", 'leads'], function () use ($organizationId, $filters) {
            return $this->repository->getByOrganization($organizationId, $filters);
        });
    }

    public function create(array $data): Lead
    {
        try {
            DB::beginTransaction();

            $lead = $this->repository->create($data);

            $this->clearCache($lead->organization_id);

            DB::commit();

            return $lead;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lead creation failed', ['error' => $e->getMessage(), 'data' => $data]);
            throw $e;
        }
    }

    public function update(Lead $lead, array $data): Lead
    {
        try {
            DB::beginTransaction();

            $lead = $this->repository->update($lead, $data);

            $this->clearCache($lead->organization_id);

            DB::commit();

            return $lead;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lead update failed', ['lead_id' => $lead->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateStatus(Lead $lead, string $newStatus): Lead
    {
        return $this->update($lead, ['status' => $newStatus]);
    }

    protected function clearCache(string $organizationId): void
    {
        if (config('cache.default') !== 'file' && config('cache.default') !== 'database') {
            Cache::tags(['leads', "org.{$organizationId}"])->flush();
        } else {
            // Log warning or use fallback invalidation if needed
            Log::debug('Cache tags not supported for current driver. Skipping tag-based flush.');
        }
    }

    protected function withCache(string $key, array $tags, \Closure $callback)
    {
        if (config('cache.default') !== 'file' && config('cache.default') !== 'database') {
            return Cache::tags($tags)->remember($key, 300, $callback);
        }

        return Cache::remember($key, 300, $callback);
    }
}
