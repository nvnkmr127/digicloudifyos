<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Form extends Model
{
    use HasUuids, OrganizationScoped, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'fields',
        'status',
    ];

    protected $casts = [
        'fields' => 'array',
    ];

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }
}
