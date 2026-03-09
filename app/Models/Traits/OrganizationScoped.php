<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait OrganizationScoped
{
    /**
     * Scope a query to only include records for the given organization.
     */
    public function scopeForOrganization(Builder $query, string $organizationId): Builder
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Scope a query to only include active records.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'ACTIVE');
    }

    /**
     * Scope a query to only include inactive records.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', 'INACTIVE');
    }

    /**
     * Check if the model belongs to the given organization.
     */
    public function belongsToOrganization(string $organizationId): bool
    {
        return $this->organization_id === $organizationId;
    }

    /**
     * Boot the organization scoped trait.
     */
    protected static function bootOrganizationScoped(): void
    {
        static::creating(function ($model) {
            if (empty($model->organization_id) && Auth::check() && Auth::user()) {
                $model->organization_id = Auth::user()->organization_id;
            }
        });

        static::addGlobalScope('organization', function (Builder $builder) {
            if (Auth::check() && Auth::user()) {
                $user = Auth::user();
                // Only apply organization scope for non-admin users
                if (! in_array($user->role ?? '', ['OWNER', 'ADMIN'])) {
                    $builder->where('organization_id', $user->organization_id);
                }
            }
        });
    }
}
