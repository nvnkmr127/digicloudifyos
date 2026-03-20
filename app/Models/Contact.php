<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use OrganizationScoped;

    protected $guarded = [];

    protected $casts = ['tags' => 'array'];

    public function opportunities()
    {
        return $this->hasMany(Opportunity::class);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }
}
