<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccessToken extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'platform',
        'access_token',
        'refresh_token',
        'expires_at',
        'scopes',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'scopes' => 'array',
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function adAccounts(): HasMany
    {
        return $this->hasMany(AdAccount::class);
    }

    public function facebookUser(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(FacebookUser::class);
    }
}
