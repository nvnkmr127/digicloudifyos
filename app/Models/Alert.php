<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'organization_id',
        'client_id',
        'campaign_id',
        'alert_type',
        'severity',
        'status',
        'title',
        'message',
        'payload',
        'triggered_at',
        'acknowledged_at',
        'resolved_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'triggered_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
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

    public function scopeOpen($query)
    {
        return $query->where('status', 'OPEN');
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'CRITICAL');
    }

    public function acknowledge(): void
    {
        $this->update([
            'status' => 'ACKNOWLEDGED',
            'acknowledged_at' => now(),
        ]);
    }

    public function resolve(): void
    {
        $this->update([
            'status' => 'RESOLVED',
            'resolved_at' => now(),
        ]);
    }
}
