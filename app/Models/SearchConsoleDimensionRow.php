<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchConsoleDimensionRow extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'client_id',
        'metric_date',
        'site_url',
        'dimension',
        'key',
        'clicks',
        'impressions',
        'ctr',
        'avg_position',
        'raw_data',
    ];

    protected $casts = [
        'metric_date' => 'date',
        'raw_data' => 'array',
        'ctr' => 'decimal:6',
        'avg_position' => 'decimal:4',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
