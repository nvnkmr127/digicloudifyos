<?php

namespace App\Livewire\Pipelines;

use Livewire\Component;

class Index extends Component
{
    public $selectedPipelineId = null;

    public function mount()
    {
        $firstPipeline = \App\Models\Pipeline::where('organization_id', \Illuminate\Support\Facades\Auth::user()->organization_id)->first();
        if ($firstPipeline) {
            $this->selectedPipelineId = $firstPipeline->id;
        }
    }

    public function updateOpportunityStage($opportunityId, $newStageId)
    {
        $opportunity = \App\Models\Opportunity::findOrFail($opportunityId);
        $this->authorize('update', $opportunity);
        $opportunity->update(['pipeline_stage_id' => $newStageId]);
    }

    public function render()
    {
        $orgId = \Illuminate\Support\Facades\Auth::user()->organization_id;
        $pipelines = \App\Models\Pipeline::where('organization_id', $orgId)->get();
        $selectedPipeline = null;

        if ($this->selectedPipelineId) {
            $selectedPipeline = \App\Models\Pipeline::where('organization_id', $orgId)
                ->with(['stages.opportunities.contact'])
                ->findOrFail($this->selectedPipelineId);
            
            $this->authorize('view', $selectedPipeline);
        }

        return view('livewire.pipelines.index', [
            'pipelines' => $pipelines,
            'selectedPipeline' => $selectedPipeline,
        ]);
    }
}
