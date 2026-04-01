<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopifyDailyMetric extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'client_id',
        'client_channel_connection_id',
        'metric_date',
        'shop_domain',
        'currency_code',
        'orders_count',
        'customers_count',
        'gross_sales',
        'net_sales',
        'refunds',
        'raw_data',
    ];

    protected $casts = [
        'metric_date' => 'date',
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

