<?php

namespace App\Livewire\Tasks;

use Livewire\Component;

class DetailView extends Component
{
    public $task;

    public function mount($id)
    {
        $this->task = \App\Models\Task::where('id', $id)
            ->where('organization_id', \Illuminate\Support\Facades\Auth::user()->organization_id)
            ->with(['campaign', 'assignee', 'creator'])
            ->firstOrFail();
    }

    public function render()
    {
        return view('livewire.tasks.detail-view')->layout('layouts.app');
    }
}
