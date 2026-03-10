<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ad extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'ad_set_id',
        'ad_creative_id',
        'name',
        'external_ad_id',
        'status',
        'external_creative_id',
        'creative_data',
    ];

    protected $casts = [
        'creative_data' => 'array',
    ];

    public function adSet(): BelongsTo
    {
        return $this->belongsTo(AdSet::class);
    }

    public function adCreative(): BelongsTo
    {
        return $this->belongsTo(AdCreative::class);
    }

    public function adInsights(): HasMany
    {
        return $this->hasMany(AdInsight::class);
    }
}
