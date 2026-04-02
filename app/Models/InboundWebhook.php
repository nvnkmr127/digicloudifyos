<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InboundWebhook extends Model
{
    use HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'name',
        'provider',
        'endpoint_key',
        'verify_token',
        'signing_secret',
        'active',
    ];

    protected $casts = [
        'verify_token' => 'encrypted',
        'signing_secret' => 'encrypted',
        'active' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function mappings(): HasMany
    {
        return $this->hasMany(WebhookMapping::class);
    }
}
