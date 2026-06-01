<?php

namespace App\Services;

use App\Contracts\OrganizationContextInterface;
use Illuminate\Support\Facades\Auth;

class AuthOrganizationContext implements OrganizationContextInterface
{
    protected ?string $manualOrganizationId = null;

    /**
     * Determine if the current context has an active organization.
     */
    public function hasCurrentOrganization(): bool
    {
        if ($this->manualOrganizationId !== null) {
            return true;
        }

        return app()->bound('auth') && Auth::hasUser() && Auth::user()->organization_id !== null;
    }

    /**
     * Get the current organization ID.
     */
    public function getCurrentOrganizationId(): ?string
    {
        if ($this->manualOrganizationId !== null) {
            return $this->manualOrganizationId;
        }

        if ($this->hasCurrentOrganization()) {
            return Auth::user()->organization_id;
        }

        return null;
    }

    /**
     * Set a manual organization ID for the current context (e.g., in background jobs).
     */
    public function setManualOrganizationId(?string $orgId): void
    {
        $this->manualOrganizationId = $orgId;
    }
}
