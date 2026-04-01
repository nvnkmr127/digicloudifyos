<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleMerchantCenterDailyMetric extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'client_id',
        'client_channel_connection_id',
        'metric_date',
        'merchant_id',
        'items_checked',
        'items_disapproved',
        'items_pending',
        'items_approved',
        'issue_count',
        'issue_breakdown',
        'feed_count',
        'feed_issue_count',
        'feed_statuses',
        'top_issue_examples',
        'pages_fetched',
        'records_fetched',
        'truncated',
        'raw_data',
    ];

    protected $casts = [
        'metric_date' => 'date',
        'issue_breakdown' => 'array',
        'feed_statuses' => 'array',
        'top_issue_examples' => 'array',
        'truncated' => 'boolean',
        'raw_data' => 'array',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function connection(): BelongsTo
    {
        return $this->belongsTo(ClientChannelConnection::class, 'client_channel_connection_id');
    }
}
