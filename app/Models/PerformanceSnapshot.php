<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerformanceSnapshot extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'client_id',
        'channel_type',
        'snapshot_date',
        'impressions',
        'clicks',
        'spend',
        'conversions',
        'revenue',
        'ctr',
        'cpc',
        'cpm',
        'roas',
        'reach',
        'engagement_rate',
        'leads',
        'cost_per_lead',
        'baseline_ctr',
        'baseline_cpc',
        'baseline_roas',
        'baseline_leads',
        'anomaly_flags',
        'raw_data',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'spend' => 'decimal:4',
        'revenue' => 'decimal:4',
        'ctr' => 'decimal:6',
        'cpc' => 'decimal:4',
        'cpm' => 'decimal:4',
        'roas' => 'decimal:4',
        'engagement_rate' => 'decimal:6',
        'cost_per_lead' => 'decimal:4',
        'baseline_ctr' => 'decimal:6',
        'baseline_cpc' => 'decimal:4',
        'baseline_roas' => 'decimal:4',
        'baseline_leads' => 'decimal:2',
        'anomaly_flags' => 'array',
        'raw_data' => 'array',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function anomalies(): HasMany
    {
        return $this->hasMany(PerformanceAnomaly::class, 'snapshot_id');
    }

    public function scopeForDateRange($query, $start, $end)
    {
        return $query->whereBetween('snapshot_date', [$start, $end]);
    }

    public function scopeForChannel($query, $type)
    {
        return $query->where('channel_type', $type);
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function getCtrChangePercent(): ?float
    {
        if (!$this->baseline_ctr || $this->baseline_ctr == 0) return null;
        return (($this->ctr - $this->baseline_ctr) / $this->baseline_ctr) * 100;
    }

    public function getCpcChangePercent(): ?float
    {
        if (!$this->baseline_cpc || $this->baseline_cpc == 0) return null;
        return (($this->cpc - $this->baseline_cpc) / $this->baseline_cpc) * 100;
    }

    public function getRoasChangePercent(): ?float
    {
        if (!$this->baseline_roas || $this->baseline_roas == 0) return null;
        return (($this->roas - $this->baseline_roas) / $this->baseline_roas) * 100;
    }

    public function hasAnomalies(): bool
    {
        return !empty($this->anomaly_flags);
    }
}
