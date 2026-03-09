<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'user_id',
        'employee_code',
        'department',
        'position',
        'employment_type',
        'join_date',
        'salary',
        'hourly_rate',
        'skills',
        'certifications',
        'status',
        'manager_id',
        'work_hours_per_week',
        'performance_rating',
    ];

    protected $casts = [
        'join_date' => 'date',
        'skills' => 'array',
        'certifications' => 'array',
        'salary' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'work_hours_per_week' => 'integer',
        'performance_rating' => 'decimal:2',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function projectAssignments(): HasMany
    {
        return $this->hasMany(ProjectAssignment::class);
    }

    public function workloadEntries(): HasMany
    {
        return $this->hasMany(WorkloadEntry::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByDepartment($query, string $department)
    {
        return $query->where('department', $department);
    }

    public function getFullNameAttribute(): string
    {
        return $this->user->name ?? 'N/A';
    }

    public function getCurrentWorkload(): float
    {
        return $this->workloadEntries()
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->sum('allocated_hours');
    }

    public function getUtilizationRate(): float
    {
        $currentWorkload = $this->getCurrentWorkload();
        $availableHours = $this->work_hours_per_week;

        return $availableHours > 0 ? ($currentWorkload / $availableHours) * 100 : 0;
    }
}
