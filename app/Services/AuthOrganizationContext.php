<?php

namespace App\Services;

use App\Contracts\OrganizationContextInterface;
use Illuminate\Support\Facades\Auth;

class AuthOrganizationContext implements OrganizationContextInterface
{
    /**
     * Determine if the current context has an active organization.
     */
    public function hasCurrentOrganization(): bool
    {
        return app()->bound('auth') && Auth::hasUser() && Auth::user()->organization_id !== null;
    }

    /**
     * Get the current organization ID.
     */
    public function getCurrentOrganizationId(): ?string
    {
        if ($this->hasCurrentOrganization()) {
            return Auth::user()->organization_id;
        }
        return null;
    }
}
