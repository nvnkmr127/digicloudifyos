<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, HasUuids, OrganizationScoped, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'email',
        'website_url',
        'phone',
        'external_ref',
        'industry',
        'timezone',
        'currency_code',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country_code',
        'business_description',
        'goals',
        'target_audience',
        'competitors',
        'primary_kpis',
        'gdpr_consent_at',
        'ccpa_opt_out_at',
        'data_retention_days',
        'privacy_contact_email',
        'status',
    ];

    protected $casts = [
        'goals' => 'array',
        'target_audience' => 'array',
        'competitors' => 'array',
        'primary_kpis' => 'array',
        'gdpr_consent_at' => 'datetime',
        'ccpa_opt_out_at' => 'datetime',
        'data_retention_days' => 'integer',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function adAccounts(): HasMany
    {
        return $this->hasMany(AdAccount::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function campaignRefs(): HasMany
    {
        return $this->hasMany(CampaignRef::class);
    }

    public function creativeRequests(): HasMany
    {
        return $this->hasMany(CreativeRequest::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function channelConnections(): HasMany
    {
        return $this->hasMany(ClientChannelConnection::class);
    }

    public function servicePackages(): BelongsToMany
    {
        return $this->belongsToMany(ServicePackage::class, 'client_service_packages')
            ->withPivot(['id', 'is_active', 'started_at'])
            ->wherePivot('is_active', true);
    }

    public function performanceSnapshots(): HasMany
    {
        return $this->hasMany(PerformanceSnapshot::class);
    }

    public function performanceAnomalies(): HasMany
    {
        return $this->hasMany(PerformanceAnomaly::class);
    }

    public function healthScores(): HasMany
    {
        return $this->hasMany(ClientHealthScore::class);
    }

    public function latestHealthScore(): HasOne
    {
        return $this->hasOne(ClientHealthScore::class)->latestOfMany('score_date');
    }

    public function aiInsights(): HasMany
    {
        return $this->hasMany(AiInsight::class);
    }

    public function getCurrentHealthScoreAttribute(): ?int
    {
        return $this->latestHealthScore?->overall_score;
    }

    public function scopeWithHealthScores($query)
    {
        return $query->with('latestHealthScore');
    }

    public function onboardingChecklist(): HasOne
    {
        return $this->hasOne(ClientOnboardingChecklist::class);
    }

    public function getOnboardingProgressAttribute(): int
    {
        $items = $this->onboardingChecklist?->items ?? [];
        if (empty($items)) {
            return 0;
        }

        $total = count($items);
        $completed = collect($items)->where('completed', true)->count();

        return (int) round(($completed / $total) * 100);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }
}
