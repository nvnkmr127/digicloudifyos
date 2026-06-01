<?php

namespace App\Livewire\Tasks;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class KanbanBoard extends Component
{
    use AuthorizesRequests;

    public $tasks = [];

    public $availableAssignees = [];

    public $priorityFilter = 'all';

    public $assigneeFilter = null;

    public $searchQuery = '';

    public $columns = [];

    protected $listeners = [
        'taskUpdated' => 'refreshTasks',
        'taskCreated' => 'refreshTasks',
    ];

    /**
     * Component boot logic.
     */
    public function boot(): void
    {
        $this->columns = collect(Task::getStatuses())->map(function ($meta, $key) {
            return array_merge(['key' => $key], $meta);
        })->values()->toArray();
    }

    public function mount()
    {
        $this->loadAssignees();
        $this->refreshTasks();
    }

    public function loadAssignees()
    {
        $this->availableAssignees = User::active()
            ->orderBy('full_name')
            ->get(['id', 'full_name']);
    }

    public function refreshTasks()
    {
        $query = Task::query()
            ->with(['assignee:id,full_name', 'campaign:id,name', 'client:id,name']);

        if ($this->priorityFilter !== 'all') {
            $query->where('priority', $this->priorityFilter);
        }

        if ($this->assigneeFilter) {
            $query->where('assigned_to', $this->assigneeFilter);
        }

        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%'.$this->searchQuery.'%')
                    ->orWhere('description', 'like', '%'.$this->searchQuery.'%');
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

    /**
     * Update the status of a specific task with validation and authorization.
     *
     * @param  string  $taskId
     * @param  string  $newStatus
     * @return void
     */
    public function updateTaskStatus($taskId, $newStatus)
    {
        try {
            // Validate incoming status against allowed model keys
            $allowedStatuses = collect(Task::getStatuses())->keys()->toArray();
            if (! in_array($newStatus, $allowedStatuses)) {
                throw new \InvalidArgumentException("Invalid task status requested: {$newStatus}");
            }

            $task = Task::findOrFail($taskId);

            $this->authorize('update', $task);

            $oldStatus = $task->status;

            // Explicit assignment for better audit trail visibility
            $task->status = $newStatus;
            $task->save();

            $this->refreshTasks();

            $this->dispatch('taskUpdated', [
                'taskId' => $taskId,
                'oldStatus' => $oldStatus,
                'newStatus' => $newStatus,
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Task stage updated successfully',
            ]);

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Operation failed: '.$e->getMessage(),
            ]);
        }
    }

    public function deleteTask($taskId)
    {
        try {
            $task = Task::findOrFail($taskId);
            $this->authorize('delete', $task);

            $task->delete();
            $this->refreshTasks();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Task deleted successfully',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to delete task: '.$e->getMessage(),
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
