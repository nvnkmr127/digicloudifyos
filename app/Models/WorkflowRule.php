<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowRule extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'event_type',
        'conditions',
        'action_type',
        'action_config',
        'is_active',
    ];

    protected $casts = [
        'conditions' => 'array',
        'action_config' => 'array',
        'is_active' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function actions(): HasMany
    {
        return $this->hasMany(WorkflowAction::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AutomationLog::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
