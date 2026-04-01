<?php

namespace App\Livewire\Opportunities;

use App\Models\Opportunity;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Contact;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    public $name;
    public $monetary_value = 0;
    public $pipeline_id;
    public $pipeline_stage_id;
    public $contact_id;
    public $status = 'open';

    protected $rules = [
        'name' => 'required|string|max:255',
        'monetary_value' => 'required|numeric|min:0',
        'pipeline_id' => 'required',
        'pipeline_stage_id' => 'required',
        'contact_id' => 'nullable',
    ];

    public function mount()
    {
        $orgId = Auth::user()->organization_id;
        $pipeline = Pipeline::where('organization_id', $orgId)->first();
        if ($pipeline) {
            $this->pipeline_id = $pipeline->id;
            $this->updatedPipelineId($pipeline->id);
        }
    }

    public function updatedPipelineId($value)
    {
        $stage = PipelineStage::where('pipeline_id', $value)->first();
        if ($stage) {
            $this->pipeline_stage_id = $stage->id;
        }
    }

    public function save()
    {
        $this->validate();

        Opportunity::create([
            'organization_id' => Auth::user()->organization_id,
            'name' => $this->name,
            'monetary_value' => $this->monetary_value,
            'pipeline_id' => $this->pipeline_id,
            'pipeline_stage_id' => $this->pipeline_stage_id,
            'contact_id' => $this->contact_id ?: null,
            'status' => $this->status,
        ]);

        session()->flash('message', 'Opportunity created successfully.');

        return redirect()->route('pipelines.index');
    }

    public function render()
    {
        $orgId = Auth::user()->organization_id;
        $pipelines = Pipeline::where('organization_id', $orgId)->get();
        $stages = PipelineStage::where('pipeline_id', $this->pipeline_id)->get();
        $contacts = Contact::where('organization_id', $orgId)->get();

        return view('livewire.opportunities.create', [
            'pipelines' => $pipelines,
            'stages' => $stages,
            'contacts' => $contacts,
        ]);
    }
}
