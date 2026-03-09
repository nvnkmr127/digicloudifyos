<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationLog extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'workflow_rule_id',
        'event_id',
        'action_type',
        'status',
        'result',
    ];

    protected $casts = [
        'result' => 'array',
    ];

    public function workflowRule(): BelongsTo
    {
        return $this->belongsTo(WorkflowRule::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(WorkflowEvent::class, 'event_id');
    }
}
