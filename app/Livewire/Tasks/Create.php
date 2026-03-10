<?php

namespace App\Livewire\Tasks;

use Livewire\Component;

use App\Models\Task;
use App\Models\User;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;

class Create extends Component
{
    public $title = '';
    public $description = '';
    public $task_type = 'general';
    public $priority = 'medium';
    public $status = 'todo';
    public $assigned_to = '';
    public $client_id = '';
    public $deadline = '';

    protected $rules = [
        'title' => 'required|min:3',
        'description' => 'nullable|string',
        'task_type' => 'required|string',
        'priority' => 'required|in:low,medium,high,urgent',
        'status' => 'required|in:todo,in_progress,review,completed',
        'assigned_to' => 'nullable|uuid|exists:users,id',
        'client_id' => 'nullable|uuid|exists:clients,id',
        'deadline' => 'nullable|date',
    ];

    public function save()
    {
        $this->validate();

        Task::create([
            'organization_id' => Auth::user()->organization_id,
            'title' => $this->title,
            'description' => $this->description,
            'task_type' => $this->task_type,
            'priority' => $this->priority,
            'status' => $this->status,
            'assigned_to' => $this->assigned_to ?: null,
            'client_id' => $this->client_id ?: null,
            'deadline' => $this->deadline ?: null,
            'created_by' => Auth::id(),
        ]);

        session()->flash('success', 'Task created successfully.');

        return redirect()->route('tasks.index');
    }

    public function render()
    {
        $users = User::where('organization_id', Auth::user()->organization_id)->get();
        $clients = Client::where('organization_id', Auth::user()->organization_id)->get();

        return view('livewire.tasks.create', [
            'users' => $users,
            'clients' => $clients,
        ]);
    }
}
