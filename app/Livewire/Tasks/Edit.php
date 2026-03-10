<?php

namespace App\Livewire\Tasks;

use Livewire\Component;

use App\Models\Task;
use App\Models\User;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;

class Edit extends Component
{
    public Task $task;
    public $title;
    public $description;
    public $task_type;
    public $priority;
    public $status;
    public $assigned_to;
    public $client_id;
    public $deadline;

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

    public function mount(Task $task)
    {
        $this->task = $task;
        $this->title = $task->title;
        $this->description = $task->description;
        $this->task_type = $task->task_type;
        $this->priority = $task->priority;
        $this->status = $task->status;
        $this->assigned_to = $task->assigned_to;
        $this->client_id = $task->client_id;
        $this->deadline = $task->deadline ? $task->deadline->format('Y-m-d') : '';
    }

    public function update()
    {
        $this->validate();

        $this->task->update([
            'title' => $this->title,
            'description' => $this->description,
            'task_type' => $this->task_type,
            'priority' => $this->priority,
            'status' => $this->status,
            'assigned_to' => $this->assigned_to ?: null,
            'client_id' => $this->client_id ?: null,
            'deadline' => $this->deadline ?: null,
        ]);

        session()->flash('success', 'Task updated successfully.');

        return redirect()->route('tasks.index');
    }

    public function render()
    {
        $users = User::where('organization_id', Auth::user()->organization_id)->get();
        $clients = Client::where('organization_id', Auth::user()->organization_id)->get();

        return view('livewire.tasks.edit', [
            'users' => $users,
            'clients' => $clients,
        ]);
    }
}
