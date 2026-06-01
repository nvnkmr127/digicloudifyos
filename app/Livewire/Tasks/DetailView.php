<?php

namespace App\Livewire\Tasks;

use App\Models\Task;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class DetailView extends Component
{
    use AuthorizesRequests;

    public $task;

    public function mount($id)
    {
        $this->task = Task::with(['campaign', 'assignee', 'creator'])
            ->findOrFail($id);

        $this->authorize('view', $this->task);
    }

    /**
     * Transition the current task to the completed state.
     *
     * @throws AuthorizationException
     */
    public function markComplete(): void
    {
        $this->authorize('update', $this->task);

        if ($this->task->status === 'completed') {
            return;
        }

        $this->task->status = 'completed';
        $this->task->save();

        session()->flash('message', 'Task marked complete.');
    }

    public function render()
    {
        return view('livewire.tasks.detail-view')->layout('layouts.app');
    }
}
