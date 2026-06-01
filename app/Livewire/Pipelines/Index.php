<?php

namespace App\Livewire\Pipelines;

use App\Models\Opportunity;
use App\Models\Pipeline;
use App\Models\PipelineStage;
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

    /**
     * Move an opportunity to a new stage with strict tenant validation.
     */
    public function updateOpportunityStage($opportunityId, $newStageId)
    {
        $opportunity = Opportunity::where('organization_id', Auth::user()->organization_id)
            ->findOrFail($opportunityId);

        $this->authorize('update', $opportunity);

        // Validate that the new stage belongs to an organization-owned pipeline (B031)
        $stageExists = PipelineStage::where('id', $newStageId)
            ->whereHas('pipeline', function ($q) {
                $q->where('organization_id', Auth::user()->organization_id);
            })->exists();

        if (! $stageExists) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Invalid pipeline stage selected.']);

            return;
        }

        $opportunity->update(['pipeline_stage_id' => $newStageId]);

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Opportunity stage updated.']);
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
