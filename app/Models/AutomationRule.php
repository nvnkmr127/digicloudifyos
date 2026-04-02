<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutomationRule extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'client_id',
        'campaign_id',
        'name',
        'channel_type',
        'trigger_type',
        'trigger_config',
        'action_type',
        'action_config',
        'is_active',
        'requires_approval',
        'created_by',
    ];

    protected $casts = [
        'trigger_config' => 'array',
        'action_config' => 'array',
        'is_active' => 'boolean',
        'requires_approval' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(AutomationAction::class);
    }
}
