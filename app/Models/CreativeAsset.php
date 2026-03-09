<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreativeAsset extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'creative_request_id',
        'file_url',
        'file_type',
        'version',
        'uploaded_by',
    ];

    public function creativeRequest(): BelongsTo
    {
        return $this->belongsTo(CreativeRequest::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFilenameAttribute(): string
    {
        return basename($this->file_url);
    }
}
