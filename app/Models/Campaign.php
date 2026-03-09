<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Campaign extends Model
{
    use HasFactory, HasUuids, Searchable;

    protected $fillable = [
        'organization_id',
        'client_id',
        'ad_account_id',
        'name',
        'external_campaign_id',
        'objective',
        'status',
        'start_date',
        'end_date',
        'daily_budget',
        'lifetime_budget',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'daily_budget' => 'decimal:4',
        'lifetime_budget' => 'decimal:4',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function adAccount(): BelongsTo
    {
        return $this->belongsTo(AdAccount::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function creativeRequests(): HasMany
    {
        return $this->hasMany(CreativeRequest::class);
    }

    public function dailyMetrics(): HasMany
    {
        return $this->hasMany(DailyMetric::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    public function getFormattedBudgetAttribute(): string
    {
        $symbol = $this->adAccount?->currency_symbol ?? '$';

        if ($this->daily_budget) {
            return $symbol . number_format((float) $this->daily_budget, 2) . '/day';
        }
        if ($this->lifetime_budget) {
            return $symbol . number_format((float) $this->lifetime_budget, 2) . ' lifetime';
        }

        return 'No budget set';
    }

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'objective' => $this->objective,
            'status' => $this->status,
            'client_name' => $this->client?->name,
            'organization_id' => $this->organization_id,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->status !== 'deleted';
    }
}
