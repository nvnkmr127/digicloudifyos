<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BriefingActionItem extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'briefing_id',
        'client_id',
        'ai_insight_id',
        'sort_order',
        'priority_level',
        'title',
        'description',
        'action',
        'expected_impact',
        'effort',
        'is_completed',
        'completed_at',
        'completed_by',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'sort_order' => 'integer',
    ];

    public function briefing(): BelongsTo
    {
        return $this->belongsTo(DailyBriefing::class, 'briefing_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function aiInsight(): BelongsTo
    {
        return $this->belongsTo(AiInsight::class, 'ai_insight_id');
    }

    public function scopePending($query)
    {
        return $query->where('is_completed', false);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority_level', $priority);
    }

    public function complete($userId): void
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
            'completed_by' => $userId,
        ]);
    }

    public function getPriorityBadgeClass(): string
    {
        return match ($this->priority_level) {
            'urgent' => 'bg-red-100 text-red-800 border-red-200',
            'important' => 'bg-amber-100 text-amber-800 border-amber-200',
            'opportunity' => 'bg-green-100 text-green-800 border-green-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }
}
