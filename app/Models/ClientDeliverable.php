<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientDeliverable extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'client_id',
        'deliverable_template_id',
        'deliverable_date',
        'title',
        'status',
        'generated_at',
        'body_html',
        'payload',
        'error_message',
    ];

    protected $casts = [
        'deliverable_date' => 'date',
        'generated_at' => 'datetime',
        'payload' => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(DeliverableTemplate::class, 'deliverable_template_id');
    }
}
