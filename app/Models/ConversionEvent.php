<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversionEvent extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'date',
        'campaign_id',
        'adset_id',
        'ad_id',
        'event_type',
        'count',
        'revenue',
        'cost_per_event',
    ];

    protected $casts = [
        'date' => 'date',
        'count' => 'integer',
        'revenue' => 'decimal:2',
        'cost_per_event' => 'decimal:2',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function adSet(): BelongsTo
    {
        return $this->belongsTo(AdSet::class, 'adset_id');
    }

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }
}
