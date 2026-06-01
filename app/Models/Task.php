<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    public const STATUS_PENDING = 'pending';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_REVIEW = 'review';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_BLOCKED = 'blocked';

    /**
     * Get all available statuses with their metadata.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => ['title' => 'Pending', 'color' => 'bg-gray-100'],
            self::STATUS_IN_PROGRESS => ['title' => 'In Progress', 'color' => 'bg-blue-100'],
            self::STATUS_REVIEW => ['title' => 'Review', 'color' => 'bg-purple-100'],
            self::STATUS_COMPLETED => ['title' => 'Completed', 'color' => 'bg-green-100'],
            self::STATUS_BLOCKED => ['title' => 'Blocked', 'color' => 'bg-red-100'],
        ];
    }

    protected $fillable = [
        'organization_id',
        'client_id',
        'campaign_id',
        'creative_request_id',
        'title',
        'description',
        'task_type',
        'priority',
        'status',
        'assigned_to',
        'created_by',
        'deadline',
    ];

    protected $casts = [
        'deadline' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function creativeRequest(): BelongsTo
    {
        return $this->belongsTo(CreativeRequest::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOverdue($query)
    {
        return $query->where('deadline', '<', now())
            ->whereNotIn('status', ['completed']);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isOverdue(): bool
    {
        return $this->deadline && $this->deadline->isPast() && ! $this->isCompleted();
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'red',
            'high' => 'orange',
            'medium' => 'yellow',
            'low' => 'green',
            default => 'gray',
        };
    }
}
