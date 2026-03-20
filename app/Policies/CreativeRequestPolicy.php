<?php

namespace App\Policies;

use App\Models\CreativeRequest;
use App\Models\User;

class CreativeRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, CreativeRequest $request): bool
    {
        return $user->organization_id === $request->organization_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, CreativeRequest $request): bool
    {
        return $user->organization_id === $request->organization_id;
    }

    public function delete(User $user, CreativeRequest $request): bool
    {
        return $user->organization_id === $request->organization_id && $user->hasRole(['OWNER', 'ADMIN']);
    }
}
