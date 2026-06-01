<?php

namespace App\Livewire\CreativeRequests;

use App\Models\Campaign;
use App\Models\Client;
use App\Models\CreativeRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';

    public $statusFilter = 'ALL';

    public $showCreateModal = false;

    // Create Form
    public $client_id = '';

    public $campaign_id = '';

    public $type = 'image';

    public $title = '';

    public $description = '';

    public $priority = 'medium';

    public $deadline = '';

    protected function rules(): array
    {
        $orgId = Auth::user()->organization_id;

        return [
            'client_id' => [
                'required',
                'uuid',
                Rule::exists('clients', 'id')->where('organization_id', $orgId),
            ],
            'campaign_id' => [
                'required',
                'uuid',
                Rule::exists('campaigns', 'id')->where('organization_id', $orgId),
            ],
            'type' => 'required|in:image,carousel,video,banner',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'deadline' => 'nullable|date',
        ];
    }

    public function createCreativeRequest()
    {
        $this->validate();

        $orgId = Auth::user()->organization_id;

        CreativeRequest::create([
            'organization_id' => $orgId,
            'client_id' => $this->client_id,
            'campaign_id' => $this->campaign_id,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'status' => 'requested',
            'priority' => $this->priority,
            'deadline' => $this->deadline ?: null,
            'created_by' => Auth::id(),
        ]);

        $this->reset(['client_id', 'campaign_id', 'type', 'title', 'description', 'priority', 'deadline', 'showCreateModal']);
        $this->priority = 'medium';
        $this->type = 'image';
        session()->flash('success', 'Creative request queued for agency review.');
    }

    public function deleteCreativeRequest($id)
    {
        $request = CreativeRequest::where('organization_id', Auth::user()->organization_id)
            ->findOrFail($id);

        $request->delete();
        session()->flash('success', 'Creative request removed.');
    }

    public function render()
    {
        $orgId = Auth::user()->organization_id;

        $requests = CreativeRequest::where('organization_id', $orgId)
            ->when($this->statusFilter !== 'ALL', fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->search, fn ($q) => $q->where('title', 'like', '%'.$this->search.'%'))
            ->latest()
            ->paginate(10);

        return view('livewire.creative-requests.index', [
            'requests' => $requests,
            'clients' => Client::where('organization_id', $orgId)->orderBy('name')->get(['id', 'name']),
            'campaigns' => Campaign::where('organization_id', $orgId)->orderBy('name')->get(['id', 'name', 'client_id']),
        ])->layout('layouts.app');
    }
}
