<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyBriefing extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'briefing_date',
        'status',
        'total_clients_analyzed',
        'critical_alerts_count',
        'opportunities_count',
        'summary',
        'generated_at',
        'sent_at',
    ];

    protected $casts = [
        'briefing_date' => 'date',
        'summary' => 'array',
        'generated_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function actionItems(): HasMany
    {
        return $this->hasMany(BriefingActionItem::class, 'briefing_id');
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('briefing_date', $date);
    }

    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    public function isReady(): bool
    {
        return $this->status === 'ready';
    }

    public function markSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function getUrgentItems()
    {
        return $this->actionItems()->where('priority_level', 'urgent')->orderBy('sort_order')->get();
    }

    public function getImportantItems()
    {
        return $this->actionItems()->where('priority_level', 'important')->orderBy('sort_order')->get();
    }

    public function getOpportunities()
    {
        return $this->actionItems()->where('priority_level', 'opportunity')->orderBy('sort_order')->get();
    }
}
