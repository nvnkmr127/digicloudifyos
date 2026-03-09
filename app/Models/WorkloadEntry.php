<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkloadEntry extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'employee_id',
        'project_id',
        'start_date',
        'end_date',
        'allocated_hours',
        'actual_hours',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'allocated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function getUtilizationPercentage(): float
    {
        if ($this->allocated_hours == 0) {
            return 0;
        }

        return ($this->actual_hours / $this->allocated_hours) * 100;
    }

    public function isOverallocated(): bool
    {
        return $this->actual_hours > $this->allocated_hours;
    }
}
