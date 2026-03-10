<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FunnelMetric extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'date',
        'campaign_id',
        'impressions',
        'clicks',
        'landing_views',
        'leads',
        'sales',
        'ctr',
        'lpc_rate',
        'lead_conv_rate',
        'sales_conv_rate',
    ];

    protected $casts = [
        'date' => 'date',
        'impressions' => 'integer',
        'clicks' => 'integer',
        'landing_views' => 'integer',
        'leads' => 'integer',
        'sales' => 'integer',
        'ctr' => 'decimal:4',
        'lpc_rate' => 'decimal:4',
        'lead_conv_rate' => 'decimal:4',
        'sales_conv_rate' => 'decimal:2',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}
