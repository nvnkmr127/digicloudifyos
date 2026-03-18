<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    use HasFactory, HasUuids;

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

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
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
        return $this->status === 'New';
    }

    public function isWon(): bool
    {
        return $this->status === 'Won';
    }

    public function isLost(): bool
    {
        return $this->status === 'Lost';
    }

    protected static function booted()
    {
        static::created(function ($lead) {
            \App\Jobs\ProcessWorkflowAutomation::dispatch('lead_created', [
                'organization_id' => $lead->organization_id,
                'entity_type' => 'lead',
                'entity_id' => $lead->id,
                'email' => $lead->email,
                'full_name' => $lead->name,
                'phone' => $lead->phone,
                'source' => $lead->source,
                'status' => $lead->status,
            ]);
        });

        static::deleted(function ($lead) {
            // When a CRM lead is deleted, optionally delete or anonymize the raw Facebook lead data for privacy compliance
            if ($lead->email) {
                FacebookLead::where('email', $lead->email)
                    ->where('organization_id', $lead->organization_id)
                    ->delete();
            }
        });
    }
}
