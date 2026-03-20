<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable, OrganizationScoped;

    private const ROLE_ALIASES = [
        'SUPER-ADMIN' => 'OWNER',
        'SUPER_ADMIN' => 'OWNER',
        'ADMINISTRATOR' => 'ADMIN',
    ];

    private const ROLE_PERMISSIONS = [
        'OWNER' => ['*'],
        'ADMIN' => [
            'manage-organization',
            'manage-users',
            'view-analytics',
            'manage-workflow',
            'view-campaigns',
            'create-campaigns',
            'edit-campaigns',
            'delete-campaigns',
        ],
        'ANALYST' => [
            'view-analytics',
            'view-campaigns',
        ],
        'OPERATOR' => [
            'manage-workflow',
            'view-campaigns',
            'create-campaigns',
            'edit-campaigns',
        ],
        'VIEWER' => [
            'view-campaigns',
        ],
    ];

    protected $fillable = [
        'organization_id',
        'email',
        'password',
        'full_name',
        'role',
        'status',
        'last_login_at',
        'email_verified_at',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function taskComments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    public function creativeRequests(): HasMany
    {
        return $this->hasMany(CreativeRequest::class, 'created_by');
    }

    public function isOwner(): bool
    {
        return $this->role === 'OWNER';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['OWNER', 'ADMIN']);
    }

    public function hasRole(string|array $role): bool
    {
        $currentRole = $this->normalizeRole((string) $this->role);

        if (is_array($role)) {
            foreach ($role as $candidateRole) {
                if ($currentRole === $this->normalizeRole((string) $candidateRole)) {
                    return true;
                }
            }

            return false;
        }

        return $currentRole === $this->normalizeRole($role);
    }

    public function hasPermissionTo(string $permission): bool
    {
        $role = $this->normalizeRole((string) $this->role);
        $permissions = self::ROLE_PERMISSIONS[$role] ?? [];

        if (in_array('*', $permissions, true)) {
            return true;
        }

        return in_array($permission, $permissions, true);
    }

    public function isActive(): bool
    {
        return $this->status === 'ACTIVE';
    }

    private function normalizeRole(string $role): string
    {
        $normalizedRole = strtoupper(trim($role));

        return self::ROLE_ALIASES[$normalizedRole] ?? $normalizedRole;
    }
}
