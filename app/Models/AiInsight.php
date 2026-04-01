<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiInsight extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'client_id',
        'anomaly_id',
        'channel_type',
        'insight_date',
        'priority',
        'category',
        'title',
        'issue_description',
        'root_cause',
        'recommended_action',
        'expected_impact',
        'effort_level',
        'urgency',
        'is_dismissed',
        'dismissed_at',
        'dismissed_by',
        'is_completed',
        'completed_at',
        'completed_by',
        'raw_ai_response',
    ];

    protected $casts = [
        'insight_date' => 'date',
        'is_dismissed' => 'boolean',
        'is_completed' => 'boolean',
        'dismissed_at' => 'datetime',
        'completed_at' => 'datetime',
        'raw_ai_response' => 'array',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function anomaly(): BelongsTo
    {
        return $this->belongsTo(PerformanceAnomaly::class, 'anomaly_id');
    }

    public function actionItems(): HasMany
    {
        return $this->hasMany(BriefingActionItem::class, 'ai_insight_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_dismissed', false)->where('is_completed', false);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeForToday($query)
    {
        return $query->whereDate('insight_date', today());
    }

    public function scopeOpportunities($query)
    {
        return $query->where('category', 'opportunity');
    }

    public function dismiss($userId): void
    {
        $this->update([
            'is_dismissed' => true,
            'dismissed_at' => now(),
            'dismissed_by' => $userId,
        ]);
    }

    public function complete($userId): void
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
            'completed_by' => $userId,
        ]);
    }

    public function getPriorityColor(): string
    {
        return match ($this->priority) {
            'critical' => 'red',
            'high' => 'orange',
            'medium' => 'blue',
            'low' => 'gray',
            'opportunity' => 'green',
            default => 'gray',
        };
    }

    public function getPriorityIcon(): string
    {
        return match ($this->priority) {
            'critical' => 'fire',
            'high' => 'exclamation-circle',
            'medium' => 'information-circle',
            'low' => 'dots-horizontal',
            'opportunity' => 'sparkles',
            default => 'question-mark-circle',
        };
    }
}
