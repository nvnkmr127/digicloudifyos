<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaAdLibraryAd extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'client_id',
        'client_competitor_id',
        'library_ad_id',
        'page_id',
        'page_name',
        'ad_snapshot_url',
        'ad_creation_time',
        'ad_delivery_start_time',
        'ad_delivery_stop_time',
        'publisher_platforms',
        'creative_bodies',
        'creative_link_titles',
        'creative_link_descriptions',
        'creative_link_captions',
        'first_seen_at',
        'last_seen_at',
        'raw_data',
    ];

    protected $casts = [
        'ad_creation_time' => 'datetime',
        'ad_delivery_start_time' => 'datetime',
        'ad_delivery_stop_time' => 'datetime',
        'publisher_platforms' => 'array',
        'creative_bodies' => 'array',
        'creative_link_titles' => 'array',
        'creative_link_descriptions' => 'array',
        'creative_link_captions' => 'array',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'raw_data' => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function competitor(): BelongsTo
    {
        return $this->belongsTo(ClientCompetitor::class, 'client_competitor_id');
    }
}
