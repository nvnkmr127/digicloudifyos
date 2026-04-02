<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PerformanceAnomaly extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'client_id',
        'snapshot_id',
        'anomaly_type',
        'channel_type',
        'metric_name',
        'current_value',
        'baseline_value',
        'deviation_percentage',
        'severity',
        'detected_at',
        'resolved_at',
        'context',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
        'resolved_at' => 'datetime',
        'context' => 'array',
        'current_value' => 'decimal:6',
        'baseline_value' => 'decimal:6',
        'deviation_percentage' => 'decimal:2',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(PerformanceSnapshot::class, 'snapshot_id');
    }

    public function aiInsight(): HasOne
    {
        return $this->hasOne(AiInsight::class, 'anomaly_id');
    }

    public function scopeUnresolved($query)
    {
        return $query->whereNull('resolved_at');
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function isResolved(): bool
    {
        return ! is_null($this->resolved_at);
    }

    public function resolve(): void
    {
        $this->update(['resolved_at' => now()]);
    }

    public function getDeviationDescription(): string
    {
        $direction = $this->deviation_percentage > 0 ? 'increase' : 'decrease';
        $absPercent = abs((float) $this->deviation_percentage);

        return "{$absPercent}% {$direction} in {$this->metric_name} compared to baseline.";
    }
}
