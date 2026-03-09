<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowAction extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'workflow_rule_id',
        'action_type',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public function workflowRule(): BelongsTo
    {
        return $this->belongsTo(WorkflowRule::class);
    }
}
