<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdAccount extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'client_id',
        'access_token_id',
        'platform',
        'account_name',
        'external_account_id',
        'currency_code',
        'timezone',
        'status',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'credentials',
        'facebook_page_id',
        'facebook_page_token',
        'target_cpl',
        'target_ctr',
        'target_frequency',
    ];

    public function accessToken(): BelongsTo
    {
        return $this->belongsTo(AccessToken::class);
    }

    public function facebookLeads(): HasMany
    {
        return $this->hasMany(FacebookLead::class);
    }

    protected $casts = [
        'token_expires_at' => 'datetime',
        'credentials' => 'array',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function adInsights(): HasMany
    {
        return $this->hasMany(AdInsight::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    public function getPlatformNameAttribute(): string
    {
        return ucwords(str_replace('_', ' ', $this->platform));
    }

    public function getCurrencySymbolAttribute(): string
    {
        return match ($this->currency_code) {
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'INR' => '₹',
            default => '$',
        };
    }
}
