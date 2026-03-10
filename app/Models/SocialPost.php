<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SocialPost extends Model
{
    use HasFactory, HasUuids, OrganizationScoped, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'user_id',
        'social_channel_id',
        'campaign_id',
        'content',
        'media_urls',
        'scheduled_at',
        'published_at',
        'external_post_id',
        'status',
        'error_message',
        'metrics',
    ];

    protected $casts = [
        'media_urls' => 'array',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'metrics' => 'array',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(SocialChannel::class, 'social_channel_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}
