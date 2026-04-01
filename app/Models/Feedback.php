<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feedback extends Model
{
    use OrganizationScoped, HasUuids, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'user_id',
        'entity_type',
        'entity_id',
        'rating',
        'comment',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function entity(): BelongsTo
    {
        // This is a manual polymorphic relation since entity_id is a UUID and type is string
        $modelClass = match($this->entity_type) {
            'project' => Project::class,
            'creative_request' => CreativeRequest::class,
            'lead' => Lead::class,
            default => null
        };

        if ($modelClass) {
            return $this->belongsTo($modelClass, 'entity_id');
        }

        return $this->belongsTo(User::class); // Fallback
    }
}
