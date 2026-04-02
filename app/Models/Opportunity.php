<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Model;

class Opportunity extends Model
{
    use OrganizationScoped;

    protected $guarded = [];

    public function pipeline()
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function stage()
    {
        return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
