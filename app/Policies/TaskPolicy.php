<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        // Only allow if user belongs to an organization (B015 partial)
        return $user->organization_id !== null;
    }

    public function view(User $user, Task $task): bool
    {
        return $user->organization_id === $task->organization_id;
    }

    public function create(User $user): bool
    {
        // Prevent VIEWERS from creating tasks (B015)
        return ! $user->hasRole('VIEWER');
    }

    public function update(User $user, Task $task): bool
    {
        // Must own the task and NOT be a viewer (B015)
        return $user->organization_id === $task->organization_id && ! $user->hasRole('VIEWER');
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->organization_id === $task->organization_id && $user->hasRole(['OWNER', 'ADMIN']);
    }
}
