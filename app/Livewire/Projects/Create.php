<?php

namespace App\Livewire\Projects;

use Livewire\Component;

use App\Models\Project;
use App\Models\Client;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

class Create extends Component
{
    public $client_id = '';
    public $name = '';
    public $description = '';
    public $project_code = '';
    public $status = 'active';
    public $priority = 'medium';
    public $start_date = '';
    public $end_date = '';
    public $budget = 0;
    public $billing_type = 'fixed';
    public $hourly_rate = 0;
    public $project_manager_id = '';

    protected $rules = [
        'client_id' => 'required|uuid|exists:clients,id',
        'name' => 'required|min:3',
        'description' => 'nullable|string',
        'project_code' => 'nullable|string',
        'status' => 'required|in:active,completed,on_hold,cancelled',
        'priority' => 'required|in:low,medium,high,urgent',
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date',
        'budget' => 'nullable|numeric|min:0',
        'billing_type' => 'required|in:fixed,hourly',
        'hourly_rate' => 'nullable|numeric|min:0',
        'project_manager_id' => 'nullable|uuid|exists:employees,id',
    ];

    public function save()
    {
        $this->validate();

        Project::create([
            'organization_id' => Auth::user()->organization_id,
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

        session()->flash('success', 'Project created successfully.');

        return redirect()->route('projects.index');
    }

    public function render()
    {
        $clients = Client::where('organization_id', Auth::user()->organization_id)->get();
        $employees = Employee::where('organization_id', Auth::user()->organization_id)->get();

        return view('livewire.projects.create', [
            'clients' => $clients,
            'employees' => $employees,
        ]);
    }
}
