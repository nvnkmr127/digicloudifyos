<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'client_id',
        'name',
        'description',
        'project_code',
        'status',
        'priority',
        'start_date',
        'end_date',
        'budget',
        'actual_cost',
        'billing_type',
        'hourly_rate',
        'project_manager_id',
        'health_status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'project_manager_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ProjectAssignment::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function workloadEntries(): HasMany
    {
        return $this->hasMany(WorkloadEntry::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getBudgetUtilization(): float
    {
        if ($this->budget == 0) {
            return 0;
        }

        return ($this->actual_cost / $this->budget) * 100;
    }

    public function getTotalBillableHours(): float
    {
        return $this->timeEntries()->billable()->approved()->sum('hours');
    }

    public function getProjectedRevenue(): float
    {
        if ($this->billing_type === 'hourly') {
            return $this->getTotalBillableHours() * $this->hourly_rate;
        }

        return $this->budget;
    }

    public function isProfitable(): bool
    {
        return $this->getProjectedRevenue() > $this->actual_cost;
    }
}
