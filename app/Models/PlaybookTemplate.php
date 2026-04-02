<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlaybookTemplate extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'name',
        'category',
        'is_active',
        'steps',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'steps' => 'array',
    ];

    public function runs(): HasMany
    {
        return $this->hasMany(ClientPlaybookRun::class);
    }
}
