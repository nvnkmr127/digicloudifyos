<?php

namespace App\Livewire\Tasks;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class KanbanBoard extends Component
{
    public $tasks = [];

    public $users = [];

    public $priorityFilter = 'all';

    public $assigneeFilter = null;

    public $searchQuery = '';

    public $columns = [
        ['key' => 'pending', 'title' => 'Pending', 'color' => 'bg-gray-100'],
        ['key' => 'in_progress', 'title' => 'In Progress', 'color' => 'bg-blue-100'],
        ['key' => 'review', 'title' => 'Review', 'color' => 'bg-purple-100'],
        ['key' => 'completed', 'title' => 'Completed', 'color' => 'bg-green-100'],
        ['key' => 'blocked', 'title' => 'Blocked', 'color' => 'bg-red-100'],
    ];

    protected $listeners = [
        'taskUpdated' => 'refreshTasks',
        'taskCreated' => 'refreshTasks',
    ];

    public function mount()
    {
        $this->loadUsers();
        $this->refreshTasks();
    }

    public function loadUsers()
    {
        $this->users = User::where('organization_id', Auth::user()->organization_id)
            ->where('status', 'ACTIVE')
            ->orderBy('full_name')
            ->get(['id', 'full_name']);
    }

    public function refreshTasks()
    {
        $query = Task::query()
            ->where('organization_id', Auth::user()->organization_id)
            ->with(['assignee:id,full_name', 'campaign:id,name', 'client:id,name']);

        if ($this->priorityFilter !== 'all') {
            $query->where('priority', $this->priorityFilter);
        }

        if ($this->assigneeFilter) {
            $query->where('assigned_to', $this->assigneeFilter);
        }

        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->searchQuery . '%')
                    ->orWhere('description', 'like', '%' . $this->searchQuery . '%');
            });
        }

        $tasks = $query->orderBy('deadline', 'asc')->get();

        $this->tasks = collect($this->columns)
            ->mapWithKeys(function ($column) use ($tasks) {
                return [
                    $column['key'] => $tasks->where('status', $column['key'])->values()->toArray(),
                ];
            })
            ->toArray();
    }

    public function updatedPriorityFilter()
    {
        $this->refreshTasks();
    }

    public function updatedAssigneeFilter()
    {
        $this->refreshTasks();
    }

    public function updatedSearchQuery()
    {
        $this->refreshTasks();
    }

    public function updateTaskStatus($taskId, $newStatus)
    {
        try {
            $task = Task::where('id', $taskId)
                ->where('organization_id', Auth::user()->organization_id)
                ->firstOrFail();

            $this->authorize('update', $task);

            $oldStatus = $task->status;
            $task->update(['status' => $newStatus]);

            $this->refreshTasks();

            $this->dispatch('taskUpdated', [
                'taskId' => $taskId,
                'oldStatus' => $oldStatus,
                'newStatus' => $newStatus,
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Task status updated successfully',
            ]);

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to update task status: ' . $e->getMessage(),
            ]);
        }
    }

    public function clearFilters()
    {
        $this->priorityFilter = 'all';
        $this->assigneeFilter = null;
        $this->searchQuery = '';
        $this->refreshTasks();
    }

    public function render()
    {
        return view('livewire.tasks.kanban-board')->layout('layouts.app');
    }
}
