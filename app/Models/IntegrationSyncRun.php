<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrationSyncRun extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'client_id',
        'client_channel_connection_id',
        'channel_type',
        'run_date',
        'status',
        'attempt',
        'started_at',
        'finished_at',
        'next_retry_at',
        'error_message',
        'metrics',
    ];

    protected $casts = [
        'run_date' => 'date',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'next_retry_at' => 'datetime',
        'attempt' => 'integer',
        'metrics' => 'array',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function connection(): BelongsTo
    {
        return $this->belongsTo(ClientChannelConnection::class, 'client_channel_connection_id');
    }
}
