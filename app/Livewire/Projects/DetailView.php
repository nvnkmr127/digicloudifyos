<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Livewire\Component;

class DetailView extends Component
{
    public $project;

    public function mount($project)
    {
        $this->project = Project::with(['client', 'projectManager', 'tasks', 'campaigns'])
            ->findOrFail($project);

        $this->authorize('view', $this->project);
    }

    public function render()
    {
        return view('livewire.projects.detail-view');
    }
}
