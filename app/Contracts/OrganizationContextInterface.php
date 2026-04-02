<?php

namespace App\Contracts;

interface OrganizationContextInterface
{
    /**
     * Determine if the current context has an active organization.
     */
    public function hasCurrentOrganization(): bool;

    /**
     * Get the current organization ID.
     */
    public function getCurrentOrganizationId(): ?string;
}
