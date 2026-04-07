<?php

namespace App\Policies;

use App\Enums\CampaignStatus;
use App\Models\Campaign;
use App\Models\User;

class CampaignPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view-campaigns');
    }

    public function view(User $user, Campaign $campaign): bool
    {
        return $user->organization_id === $campaign->organization_id
            && $user->hasPermissionTo('view-campaigns');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create-campaigns');
    }

    public function update(User $user, Campaign $campaign): bool
    {
        return $user->organization_id === $campaign->organization_id
            && $user->hasPermissionTo('edit-campaigns');
    }

    public function delete(User $user, Campaign $campaign): bool
    {
        $status = $campaign->status instanceof CampaignStatus ? $campaign->status->value : $campaign->status;

        if (in_array($status, CampaignStatus::activeValues(), true)) {
            return false;
        }

        return $user->organization_id === $campaign->organization_id
            && $user->hasPermissionTo('delete-campaigns');
    }

    public function restore(User $user, Campaign $campaign): bool
    {
        return $user->organization_id === $campaign->organization_id
            && $user->hasRole('ADMIN');
    }

    public function forceDelete(User $user, Campaign $campaign): bool
    {
        return $user->organization_id === $campaign->organization_id
            && $user->hasRole('ADMIN');
    }
}
