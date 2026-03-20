<?php

namespace App\Livewire\Tasks;

use Livewire\Component;

class DetailView extends Component
{
    public $task;

    public function mount($id)
    {
        $this->task = \App\Models\Task::with(['campaign', 'assignee', 'creator'])
            ->findOrFail($id);

        $this->authorize('view', $this->task);
    }

    public function render()
    {
        return view('livewire.tasks.detail-view')->layout('layouts.app');
    }
}
