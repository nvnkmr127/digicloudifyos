<?php

namespace App\Repositories;

use App\Models\Campaign;
use Illuminate\Support\Collection;

class CampaignRepository
{
    public function getByOrganization(string $organizationId, array $filters = []): Collection
    {
        $query = Campaign::query()
            ->where('organization_id', $organizationId)
            ->with(['client:id,name', 'adAccount:id,account_name,platform']);

        if (isset($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'ilike', '%'.$filters['search'].'%')
                    ->orWhereHas('client', function ($clientQuery) use ($filters) {
                        $clientQuery->where('name', 'ilike', '%'.$filters['search'].'%');
                    });
            });
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('start_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('start_date', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function find(string $id): ?Campaign
    {
        return Campaign::with(['client', 'adAccount', 'tasks', 'creativeRequests', 'alerts'])
            ->find($id);
    }

    public function create(array $data): Campaign
    {
        return Campaign::create($data);
    }

    public function update(Campaign $campaign, array $data): Campaign
    {
        $campaign->update($data);

        return $campaign->fresh();
    }

    public function delete(Campaign $campaign): bool
    {
        return $campaign->delete();
    }

    public function getActive(string $organizationId): Collection
    {
        return Campaign::where('organization_id', $organizationId)
            ->active()
            ->get();
    }

    public function getRunning(string $organizationId): Collection
    {
        return Campaign::where('organization_id', $organizationId)
            ->running()
            ->get();
    }
}
