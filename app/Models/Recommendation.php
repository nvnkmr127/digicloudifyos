<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recommendation extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'organization_id',
        'client_id',
        'campaign_id',
        'recommendation_type',
        'priority_score',
        'summary',
        'rationale',
        'action_payload',
        'status',
        'generated_at',
        'applied_at',
        'dismissed_at',
    ];

    protected $casts = [
        'action_payload' => 'array',
        'priority_score' => 'decimal:2',
        'generated_at' => 'datetime',
        'applied_at' => 'datetime',
        'dismissed_at' => 'datetime',
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

    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    public function apply(): void
    {
        $this->update([
            'status' => 'APPLIED',
            'applied_at' => now(),
        ]);
    }

    public function dismiss(): void
    {
        $this->update([
            'status' => 'DISMISSED',
            'dismissed_at' => now(),
        ]);
    }
}
