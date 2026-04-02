<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleBusinessProfileDailyMetric extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'client_id',
        'metric_date',
        'website_clicks',
        'call_clicks',
        'directions_requests',
        'impressions_search_desktop',
        'impressions_search_mobile',
        'impressions_maps_desktop',
        'impressions_maps_mobile',
        'raw_data',
    ];

    protected $casts = [
        'metric_date' => 'date',
        'raw_data' => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
