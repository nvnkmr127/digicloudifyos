<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PipelineStage extends Model
{
    protected $guarded = [];

    public function pipeline()
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function opportunities()
    {
        return $this->hasMany(Opportunity::class);
    }
}
