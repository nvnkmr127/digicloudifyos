<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Broadcast extends Model
{
    use HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'name',
        'channel',
        'target_segment',
        'content_payload',
        'status',
        'automation_rule_id',
        'scheduled_at',
        'recipients_count',
    ];

    protected $casts = [
        'content_payload' => 'array',
        'scheduled_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function automationRule(): BelongsTo
    {
        return $this->belongsTo(WorkflowRule::class, 'automation_rule_id');
    }
}
