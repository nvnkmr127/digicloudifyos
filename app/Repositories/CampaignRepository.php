<?php

namespace App\Repositories;

use App\Models\Campaign;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
            $term = trim((string) $filters['search']);
            if ($term !== '') {
                $query->where(function ($q) use ($term) {
                    $this->whereInsensitiveLike($q, 'name', $term);
                    $q->orWhereHas('client', function ($clientQuery) use ($term) {
                        $this->whereInsensitiveLike($clientQuery, 'name', $term);
                    });
                });
            }
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('start_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('start_date', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    protected function whereInsensitiveLike(Builder $query, string $column, string $term): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            $query->where($column, 'ilike', '%'.$term.'%');

            return;
        }

        $query->whereRaw('LOWER('.$column.') LIKE ?', ['%'.mb_strtolower($term).'%']);
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
