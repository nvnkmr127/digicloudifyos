<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductivityDailySummary extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'employee_id',
        'summary_date',
        'hours_tracked',
        'billable_hours',
        'billable_ratio',
        'tasks_completed',
        'avg_task_cycle_days',
        'overdue_tasks',
        'allocated_hours',
        'utilization_rate',
        'raw_data',
    ];

    protected $casts = [
        'summary_date' => 'date',
        'raw_data' => 'array',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
