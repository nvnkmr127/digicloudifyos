<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Webhook extends Model
{
    use HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'name',
        'url',
        'events',
        'secret',
        'active',
        'headers',
    ];

    protected $casts = [
        'events' => 'array',
        'headers' => 'array',
        'secret' => 'encrypted',
        'active' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class);
    }

    public function shouldTriggerForEvent(string $event): bool
    {
        return $this->active && in_array($event, $this->events ?? []);
    }
}
