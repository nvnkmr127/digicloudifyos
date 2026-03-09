<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pipeline extends Model
{
    protected $guarded = [];

    public function stages()
    {
        return $this->hasMany(PipelineStage::class)->orderBy('order_index');
    }

    public function opportunities()
    {
        return $this->hasMany(Opportunity::class);
    }
}
