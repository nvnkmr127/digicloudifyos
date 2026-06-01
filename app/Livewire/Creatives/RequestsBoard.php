<?php

namespace App\Livewire\Creatives;

use App\Models\CreativeRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class RequestsBoard extends Component
{
    use AuthorizesRequests;

    public $requests = [];

    public bool $showCreateModal = false;

    public string $title = '';

    public string $description = '';

    public string $priority = 'Medium';

    public string $type = 'Graphic'; // Default type

    public ?string $deadline = null;

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'priority' => 'required|in:Low,Medium,High,Urgent',
        'type' => 'required|string',
        'deadline' => 'nullable|date|after:today',
    ];

    public $statusGroups = [
        'requested' => ['title' => 'Requested', 'color' => 'bg-gray-100', 'text' => 'text-gray-700'],
        'in_production' => ['title' => 'In Production', 'color' => 'bg-yellow-100', 'text' => 'text-yellow-700'],
        'review' => ['title' => 'Review', 'color' => 'bg-purple-100', 'text' => 'text-purple-700'],
        'approved' => ['title' => 'Approved', 'color' => 'bg-green-100', 'text' => 'text-green-700'],
    ];

    public function mount()
    {
        $this->refreshRequests();
    }

    public function refreshRequests()
    {
        $allRequests = CreativeRequest::where('organization_id', Auth::user()->organization_id)
            ->with(['client', 'campaign', 'assignee'])
            ->orderBy('deadline', 'asc')
            ->get();

        $this->requests = collect($this->statusGroups)
            ->mapWithKeys(function ($group, $key) use ($allRequests) {
                return [
                    $key => $allRequests->where('status', $key)->values()->toArray(),
                ];
            })
            ->toArray();
    }

    public function updateStatus($requestId, $newStatus)
    {
        // D015: Enforce multi-tenant isolation and status validation
        if (! isset($this->statusGroups[$newStatus])) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Invalid status target.']);

            return;
        }

        $request = CreativeRequest::where('organization_id', Auth::user()->organization_id)
            ->findOrFail($requestId);

        $this->authorize('update', $request);

        $request->update(['status' => $newStatus]);
        $this->refreshRequests();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Status updated',
        ]);
    }

    public function createRequest()
    {
        $this->validate();

        CreativeRequest::create([
            'organization_id' => Auth::user()->organization_id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => 'requested',
            'priority' => $this->priority,
            'type' => $this->type,
            'deadline' => $this->deadline ?: null,
            'created_by' => Auth::id(),
        ]);

        $this->reset(['title', 'description', 'priority', 'type', 'deadline', 'showCreateModal']);
        $this->refreshRequests();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Creative request created successfully',
        ]);
    }

    public function deleteRequest($requestId)
    {
        $request = CreativeRequest::where('organization_id', Auth::user()->organization_id)
            ->findOrFail($requestId);

        $this->authorize('delete', $request);

        $request->delete();
        $this->refreshRequests();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Request deleted successfully',
        ]);
    }

    public function render()
    {
        return view('livewire.creatives.requests-board')->layout('layouts.app');
    }
}
