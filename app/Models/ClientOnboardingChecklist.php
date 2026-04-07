<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientOnboardingChecklist extends Model
{
    use HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'client_id',
        'items',
    ];

    protected $casts = [
        'items' => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
