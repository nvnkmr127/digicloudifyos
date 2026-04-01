<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\AsEncryptedArrayObject;

class IntegrationCredential extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'provider',
        'credential_type',
        'label',
        'external_user_id',
        'access_token',
        'refresh_token',
        'expires_at',
        'scopes',
        'payload',
        'last_verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'scopes' => 'array',
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
        'payload' => AsEncryptedArrayObject::class,
        'last_verified_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function connections(): HasMany
    {
        return $this->hasMany(ClientChannelConnection::class, 'integration_credential_id');
    }
}

