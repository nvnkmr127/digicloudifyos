<?php

namespace App\Livewire\Pipelines;

use Livewire\Component;

class Index extends Component
{
    public $selectedPipelineId = null;

    public function mount()
    {
        $firstPipeline = \App\Models\Pipeline::first();
        if ($firstPipeline) {
            $this->selectedPipelineId = $firstPipeline->id;
        }
    }

    public function updateOpportunityStage($opportunityId, $newStageId)
    {
        $opportunity = \App\Models\Opportunity::find($opportunityId);
        if ($opportunity) {
            $opportunity->update(['pipeline_stage_id' => $newStageId]);
        }
    }

    public function render()
    {
        $pipelines = \App\Models\Pipeline::all();
        $selectedPipeline = null;

        if ($this->selectedPipelineId) {
            $selectedPipeline = \App\Models\Pipeline::with(['stages.opportunities.contact'])->find($this->selectedPipelineId);
        }

        return view('livewire.pipelines.index', [
            'pipelines' => $pipelines,
            'selectedPipeline' => $selectedPipeline
        ]);
    }
}
