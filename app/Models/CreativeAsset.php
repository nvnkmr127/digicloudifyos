<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreativeAsset extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    protected static function booted()
    {
        static::deleting(function ($asset) {
            if ($asset->file_path && \Storage::exists($asset->file_path)) {
                \Storage::delete($asset->file_path);
            }
        });
    }

    protected $fillable = [
        'organization_id',
        'creative_request_id',
        'name',
        'file_path',
        'file_url', // Fallback for external URLs
        'file_type',
        'file_size',
        'version',
        'uploaded_by',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

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
        return $this->name ?? basename($this->file_path ?? $this->file_url);
    }
}
