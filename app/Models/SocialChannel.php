<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SocialChannel extends Model
{
    use HasFactory, HasUuids, OrganizationScoped, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'platform',
        'account_id',
        'account_name',
        'profile_picture_url',
        'access_token',
        'refresh_token',
        'expires_at',
        'status',
        'metadata',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'metadata' => 'array',
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(SocialPost::class);
    }
}
