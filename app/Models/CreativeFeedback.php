<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreativeFeedback extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'creative_request_id',
        'feedback',
        'commented_by',
    ];

    public function creativeRequest(): BelongsTo
    {
        return $this->belongsTo(CreativeRequest::class);
    }

    public function commenter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'commented_by');
    }
}
