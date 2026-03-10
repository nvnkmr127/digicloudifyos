<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacebookConnection extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'facebook_user_id',
        'access_token',
        'token_expires',
        'business_id',
    ];

    protected $casts = [
        'token_expires' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
