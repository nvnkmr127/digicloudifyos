<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    use AuthorizesRequests;

    public $search = '';

    public function delete($id)
    {
        $project = Project::findOrFail($id);
        $this->authorize('delete', $project);
        $project->delete();

        session()->flash('success', 'Project deleted successfully.');
    }

    public function render()
    {
        $projects = Project::where('organization_id', Auth::user()->organization_id)
            ->with(['client', 'projectManager'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('project_code', 'like', '%'.$this->search.'%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.projects.index', [
            'projects' => $projects,
        ]);
    }
}
