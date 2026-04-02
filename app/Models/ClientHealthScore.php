<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientHealthScore extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'client_id',
        'score_date',
        'overall_score',
        'ad_performance_score',
        'organic_score',
        'conversion_score',
        'budget_efficiency_score',
        'score_breakdown',
        'trend',
    ];

    protected $casts = [
        'score_date' => 'date',
        'overall_score' => 'integer',
        'ad_performance_score' => 'integer',
        'organic_score' => 'integer',
        'conversion_score' => 'integer',
        'budget_efficiency_score' => 'integer',
        'score_breakdown' => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function getScoreColor(): string
    {
        if ($this->overall_score >= 70) {
            return 'green';
        }
        if ($this->overall_score >= 40) {
            return 'yellow';
        }

        return 'red';
    }

    public function getScoreLabel(): string
    {
        if ($this->overall_score >= 70) {
            return 'Healthy';
        }
        if ($this->overall_score >= 40) {
            return 'Needs Attention';
        }

        return 'Critical';
    }

    public function getScoreBadgeClass(): string
    {
        if ($this->overall_score >= 70) {
            return 'bg-green-100 text-green-800 border-green-200';
        }
        if ($this->overall_score >= 40) {
            return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        }

        return 'bg-red-100 text-red-800 border-red-200';
    }
}
