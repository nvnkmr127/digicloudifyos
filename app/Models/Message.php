<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use OrganizationScoped;

    protected $guarded = [];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
