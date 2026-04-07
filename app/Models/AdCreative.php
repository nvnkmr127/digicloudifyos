<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdCreative extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'ad_account_id',
        'external_creative_id',
        'name',
        'title',
        'body',
        'image_url',
        'thumbnail_url',
        'video_id',
        'call_to_action_type',
        'object_story_spec',
        'asset_data',
    ];

    protected $casts = [
        'object_story_spec' => 'array',
        'asset_data' => 'array',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function adAccount(): BelongsTo
    {
        return $this->belongsTo(AdAccount::class);
    }

    public function ads(): HasMany
    {
        return $this->hasMany(Ad::class);
    }
}
