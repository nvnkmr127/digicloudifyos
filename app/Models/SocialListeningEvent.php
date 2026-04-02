<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialListeningEvent extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'client_id',
        'client_competitor_id',
        'source_type',
        'external_id',
        'title',
        'url',
        'content',
        'author',
        'published_at',
        'event_date',
        'raw_data',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'event_date' => 'date',
        'raw_data' => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function competitor(): BelongsTo
    {
        return $this->belongsTo(ClientCompetitor::class, 'client_competitor_id');
    }
}
