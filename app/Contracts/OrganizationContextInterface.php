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

    /**
     * Set a manual organization ID for the current context (e.g., in background jobs).
     */
    public function setManualOrganizationId(?string $orgId): void;
}
