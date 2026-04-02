<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Model;

class PipelineStage extends Model
{
    use OrganizationScoped;

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
