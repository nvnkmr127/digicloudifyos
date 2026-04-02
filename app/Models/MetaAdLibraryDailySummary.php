<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaAdLibraryDailySummary extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'client_id',
        'client_competitor_id',
        'metric_date',
        'active_ads_count',
        'new_ads_count',
        'pages_fetched',
        'records_fetched',
        'truncated',
        'raw_data',
    ];

    protected $casts = [
        'metric_date' => 'date',
        'truncated' => 'boolean',
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
