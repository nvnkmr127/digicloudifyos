<?php

namespace App\Livewire\Projects;

use App\Enums\ProjectBillingType;
use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Edit extends Component
{
    use AuthorizesRequests;

    public Project $project;

    public $client_id;

    public $name;

    public $description;

    public $project_code;

    public $status;

    public $priority;

    public $start_date;

    public $end_date;

    public $budget;

    public $billing_type;

    public $hourly_rate;

    public $project_manager_id;

    protected function rules(): array
    {
        return [
            'client_id' => 'required|uuid|exists:clients,id',
            'name' => 'required|min:3',
            'description' => 'nullable|string',
            'project_code' => 'nullable|string',
            'status' => ['required', 'string', Rule::in(ProjectStatus::values())],
            'priority' => ['required', 'string', Rule::in(ProjectPriority::values())],
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'budget' => 'nullable|numeric|min:0',
            'billing_type' => ['required', 'string', Rule::in(ProjectBillingType::values())],
            'hourly_rate' => 'nullable|numeric|min:0',
            'project_manager_id' => 'nullable|uuid|exists:employees,id',
        ];
    }

    public function mount(Project $project)
    {
        $this->authorize('update', $project);
        $this->project = $project;
        $this->client_id = $project->client_id;
        $this->name = $project->name;
        $this->description = $project->description;
        $this->project_code = $project->project_code;
        $this->status = $project->status;
        $this->priority = $project->priority;
        $this->start_date = $project->start_date ? Carbon::parse($project->start_date)->format('Y-m-d') : '';
        $this->end_date = $project->end_date ? Carbon::parse($project->end_date)->format('Y-m-d') : '';
        $this->budget = $project->budget;
        $this->billing_type = $project->billing_type;
        $this->hourly_rate = $project->hourly_rate;
        $this->project_manager_id = $project->project_manager_id;
    }

    public function update()
    {
        $this->authorize('update', $this->project);
        $this->validate();

        $this->project->update([
            'client_id' => $this->client_id,
            'name' => $this->name,
            'description' => $this->description,
            'project_code' => $this->project_code,
            'status' => $this->status,
            'priority' => $this->priority,
            'start_date' => $this->start_date ?: null,
            'end_date' => $this->end_date ?: null,
            'budget' => $this->budget,
            'billing_type' => $this->billing_type,
            'hourly_rate' => $this->hourly_rate,
            'project_manager_id' => $this->project_manager_id ?: null,
        ]);

        session()->flash('success', 'Project updated successfully.');

        return redirect()->route('projects.index');
    }

    public function render()
    {
        $clients = Client::where('organization_id', Auth::user()->organization_id)->get();
        $employees = Employee::where('organization_id', Auth::user()->organization_id)->get();

        return view('livewire.projects.edit', [
            'clients' => $clients,
            'employees' => $employees,
        ]);
    }
}
