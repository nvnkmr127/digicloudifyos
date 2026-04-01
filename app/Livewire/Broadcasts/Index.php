<?php

namespace App\Livewire\Broadcasts;

use App\Models\Broadcast;
use App\Models\WorkflowRule;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $showCreateModal = false;
    public $search = '';
    
    // Create Form Fields
    public $name = '';
    public $channel = 'EMAIL';
    public $target_segment = 'ALL_CONTACTS';
    public $automation_rule_id = '';
    public $scheduled_at = '';
    public $content_body = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'channel' => 'required|in:WHATSAPP,EMAIL,SMS',
        'target_segment' => 'required|string',
        'content_body' => 'required|string',
    ];

    public function createBroadcast()
    {
        $this->validate();

        Broadcast::create([
            'organization_id' => Auth::user()->organization_id,
            'name' => $this->name,
            'channel' => $this->channel,
            'target_segment' => $this->target_segment,
            'content_payload' => ['body' => $this->content_body],
            'status' => 'DRAFT',
            'automation_rule_id' => $this->automation_rule_id ?: null,
            'scheduled_at' => $this->scheduled_at ?: null,
        ]);

        $this->reset(['name', 'content_body', 'automation_rule_id', 'scheduled_at', 'showCreateModal']);
        session()->flash('success', 'Broadcast draft created successfully.');
    }

    public function delete($id)
    {
        Broadcast::where('organization_id', Auth::user()->organization_id)->findOrFail($id)->delete();
        session()->flash('success', 'Broadcast deleted.');
    }

    public function render()
    {
        $broadcasts = Broadcast::where('organization_id', Auth::user()->organization_id)
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->latest()
            ->paginate(10);

        $automationRules = WorkflowRule::where('organization_id', Auth::user()->organization_id)->get();

        return view('livewire.broadcasts.index', [
            'broadcasts' => $broadcasts,
            'automationRules' => $automationRules
        ])->layout('layouts.app');
    }
}
