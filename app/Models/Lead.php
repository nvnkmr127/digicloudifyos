<?php

namespace App\Models;

use App\Enums\LeadStatus;
use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'name',
        'phone',
        'email',
        'source',
        'status',
        'assigned_user',
        'notes',
    ];

    protected $casts = [
        'status' => LeadStatus::class,
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function scopeForStatus($query, LeadStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeAssignedTo($query, $user)
    {
        return $query->where('assigned_user', $user);
    }

    public function isNew(): bool
    {
        return $this->status === LeadStatus::New;
    }

    public function isWon(): bool
    {
        return $this->status === LeadStatus::Won;
    }

    public function isLost(): bool
    {
        return $this->status === LeadStatus::Lost;
    }
}
