<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookMapping extends Model
{
    use HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'direction',
        'name',
        'source_key',
        'target_key',
        'transform_rule',
        'webhook_id',
        'inbound_webhook_id',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }

    public function inboundWebhook(): BelongsTo
    {
        return $this->belongsTo(InboundWebhook::class);
    }
}
