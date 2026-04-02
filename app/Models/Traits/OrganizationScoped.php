<?php

namespace App\Models\Traits;

use App\Contracts\OrganizationContextInterface;
use Illuminate\Database\Eloquent\Builder;

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
            if (empty($model->organization_id) && app()->bound(OrganizationContextInterface::class)) {
                $context = app(OrganizationContextInterface::class);
                if ($context->hasCurrentOrganization()) {
                    $model->organization_id = $context->getCurrentOrganizationId();
                }
            }
        });

        static::addGlobalScope('organization', function (Builder $builder) {
            if (app()->bound(OrganizationContextInterface::class)) {
                $context = app(OrganizationContextInterface::class);
                if ($context->hasCurrentOrganization()) {
                    $builder->where($builder->getModel()->getTable().'.organization_id', $context->getCurrentOrganizationId());
                }
            }
        });
    }
}
