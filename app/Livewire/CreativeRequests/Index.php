<?php

namespace App\Livewire\CreativeRequests;

use App\Models\CreativeRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'ALL';
    public $showCreateModal = false;

    // Create Form
    public $title = '';
    public $description = '';
    public $priority = 'Medium';
    public $due_date = '';

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'priority' => 'required|in:Low,Medium,High,Urgent',
        'due_date' => 'nullable|date|after:today',
    ];

    public function createCreativeRequest()
    {
        $this->validate();

        CreativeRequest::create([
            'organization_id' => Auth::user()->organization_id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => 'requested',
            'priority' => $this->priority,
            'due_date' => $this->due_date ?: null,
            'created_by' => Auth::id(),
        ]);

        $this->reset(['title', 'description', 'priority', 'due_date', 'showCreateModal']);
        session()->flash('success', 'Creative request queued for agency review.');
    }

    public function render()
    {
        $requests = CreativeRequest::where('organization_id', Auth::user()->organization_id)
            ->when($this->statusFilter !== 'ALL', fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->search, fn($q) => $q->where('title', 'like', '%' . $this->search . '%'))
            ->latest()
            ->paginate(10);

        return view('livewire.creative-requests.index', [
            'requests' => $requests
        ])->layout('layouts.app');
    }
}
