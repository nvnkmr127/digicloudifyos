<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyMetric extends Model
{
    use HasUuids;

    protected $fillable = [
        'campaign_id',
        'date',
        'impressions',
        'clicks',
        'spend',
        'conversions',
        'revenue',
        'additional_data',
    ];

    protected $casts = [
        'date' => 'date',
        'spend' => 'decimal:4',
        'revenue' => 'decimal:4',
        'additional_data' => 'array',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}
