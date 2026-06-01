<?php

namespace App\Policies;

use App\Models\Proposal;
use App\Models\User;

class ProposalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->organization_id !== null;
    }

    public function view(User $user, Proposal $proposal): bool
    {
        return $user->organization_id === $proposal->organization_id;
    }

    public function create(User $user): bool
    {
        return ! $user->hasRole('VIEWER');
    }

    public function update(User $user, Proposal $proposal): bool
    {
        return $user->organization_id === $proposal->organization_id && ! $user->hasRole('VIEWER');
    }

    public function delete(User $user, Proposal $proposal): bool
    {
        return $user->organization_id === $proposal->organization_id && $user->hasRole(['OWNER', 'ADMIN']);
    }
}
