<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdInsight extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'ad_account_id',
        'campaign_id',
        'ad_set_id',
        'ad_id',
        'date',
        'level',
        'spend',
        'impressions',
        'reach',
        'clicks',
        'ctr',
        'cpc',
        'cpm',
        'conversions',
        'roas',
        'metadata',
    ];

    protected $casts = [
        'date' => 'date',
        'metadata' => 'array',
        'spend' => 'decimal:4',
        'ctr' => 'decimal:6',
        'cpc' => 'decimal:4',
        'cpm' => 'decimal:4',
        'conversions' => 'decimal:4',
        'roas' => 'decimal:6',
    ];

    public function adAccount(): BelongsTo
    {
        return $this->belongsTo(AdAccount::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function adSet(): BelongsTo
    {
        return $this->belongsTo(AdSet::class);
    }

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }
}
