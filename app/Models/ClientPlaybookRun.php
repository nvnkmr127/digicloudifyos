<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientPlaybookRun extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'client_id',
        'playbook_template_id',
        'run_date',
        'status',
        'completed_at',
        'error_message',
    ];

    protected $casts = [
        'run_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(PlaybookTemplate::class, 'playbook_template_id');
    }

    public function runTasks(): HasMany
    {
        return $this->hasMany(PlaybookRunTask::class);
    }
}
