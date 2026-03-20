<?php

namespace App\Policies;

use App\Models\Pipeline;
use App\Models\User;

class PipelinePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Pipeline $pipeline): bool
    {
        return $user->organization_id === $pipeline->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['OWNER', 'ADMIN']);
    }

    public function update(User $user, Pipeline $pipeline): bool
    {
        return $user->organization_id === $pipeline->organization_id && $user->hasRole(['OWNER', 'ADMIN']);
    }

    public function delete(User $user, Pipeline $pipeline): bool
    {
        return $user->organization_id === $pipeline->organization_id && $user->hasRole(['OWNER', 'ADMIN']);
    }
}
