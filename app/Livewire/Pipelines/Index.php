<?php

namespace App\Livewire\Pipelines;

use App\Models\Opportunity;
use App\Models\Pipeline;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    use AuthorizesRequests;

    public $selectedPipelineId = null;

    public function mount()
    {
        $firstPipeline = Pipeline::where('organization_id', Auth::user()->organization_id)->first();
        if ($firstPipeline) {
            $this->selectedPipelineId = $firstPipeline->id;
        }
    }

    public function updateOpportunityStage($opportunityId, $newStageId)
    {
        $opportunity = Opportunity::findOrFail($opportunityId);
        $this->authorize('update', $opportunity);
        $opportunity->update(['pipeline_stage_id' => $newStageId]);
    }

    public function render()
    {
        $orgId = Auth::user()->organization_id;
        $pipelines = Pipeline::where('organization_id', $orgId)->get();
        $selectedPipeline = null;

        if ($this->selectedPipelineId) {
            $selectedPipeline = Pipeline::where('organization_id', $orgId)
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
