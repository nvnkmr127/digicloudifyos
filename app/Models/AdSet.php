<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdSet extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'campaign_id',
        'name',
        'external_adset_id',
        'status',
        'daily_budget',
        'lifetime_budget',
        'targeting',
    ];

    protected $casts = [
        'targeting' => 'array',
        'daily_budget' => 'decimal:4',
        'lifetime_budget' => 'decimal:4',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function ads(): HasMany
    {
        return $this->hasMany(Ad::class);
    }

    public function adInsights(): HasMany
    {
        return $this->hasMany(AdInsight::class);
    }
}
