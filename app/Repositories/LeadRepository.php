<?php

namespace App\Repositories;

use App\Models\Lead;
use Illuminate\Support\Collection;

class LeadRepository
{
    public function getByOrganization(string $organizationId, array $filters = []): Collection
    {
        $query = Lead::query()
            ->where('organization_id', $organizationId);

        if (isset($filters['source']) && $filters['source'] !== 'all') {
            $query->where('source', $filters['source']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('email', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('phone', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function find(string $id): ?Lead
    {
        return Lead::find($id);
    }

    public function create(array $data): Lead
    {
        return Lead::create($data);
    }

    public function update(Lead $lead, array $data): Lead
    {
        $lead->update($data);
        return $lead->fresh();
    }

    public function delete(Lead $lead): bool
    {
        return $lead->delete();
    }
}
