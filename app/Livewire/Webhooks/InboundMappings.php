<?php

namespace App\Livewire\Webhooks;

use App\Models\InboundWebhook;
use App\Models\WebhookMapping;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class InboundMappings extends Component
{
    public $mappingId = null;

    public $name = '';

    public $source_key = '';

    public $target_key = '';

    public $transform_rule = '';

    public $inbound_webhook_id = '';

    public $active = true;

    public function mount(): void
    {
        if (! Auth::check() || ! Auth::user()?->isAdmin()) {
            abort(403);
        }
    }

    public function save(): void
    {

        $validated = $this->validate([
            'name' => 'required|string|min:3|max:255',
            'source_key' => 'required|string|max:255',
            'target_key' => 'required|string|max:255',
            'transform_rule' => 'nullable|string|max:255',
            'inbound_webhook_id' => [
                'nullable',
                'uuid',
                'exists:inbound_webhooks,id,organization_id,'.Auth::user()->organization_id,
            ],
            'active' => 'boolean',
        ]);

        $payload = [
            'organization_id' => Auth::user()->organization_id,
            'direction' => 'inbound',
            'name' => $validated['name'],
            'source_key' => $validated['source_key'],
            'target_key' => $validated['target_key'],
            'transform_rule' => $validated['transform_rule'] ?: null,
            'inbound_webhook_id' => $validated['inbound_webhook_id'] ?: null,
            'active' => (bool) $validated['active'],
        ];

        if ($this->mappingId) {
            $mapping = WebhookMapping::where('organization_id', Auth::user()->organization_id)
                ->where('direction', 'inbound')
                ->findOrFail($this->mappingId);
            $mapping->update($payload);
            session()->flash('success', 'Inbound mapping updated successfully.');
        } else {
            WebhookMapping::create($payload);
            session()->flash('success', 'Inbound mapping created successfully.');
        }

        $this->resetForm();
    }

    public function edit(string $id): void
    {
        $mapping = WebhookMapping::where('organization_id', Auth::user()->organization_id)
            ->where('direction', 'inbound')
            ->findOrFail($id);

        $this->mappingId = $mapping->id;
        $this->name = $mapping->name;
        $this->source_key = $mapping->source_key;
        $this->target_key = $mapping->target_key;
        $this->transform_rule = $mapping->transform_rule ?? '';
        $this->inbound_webhook_id = $mapping->inbound_webhook_id ?? '';
        $this->active = (bool) $mapping->active;
    }

    public function delete(string $id): void
    {
        WebhookMapping::where('organization_id', Auth::user()->organization_id)
            ->where('direction', 'inbound')
            ->findOrFail($id)
            ->delete();

        if ($this->mappingId === $id) {
            $this->resetForm();
        }

        session()->flash('success', 'Inbound mapping deleted successfully.');
    }

    public function resetForm(): void
    {
        $this->mappingId = null;
        $this->name = '';
        $this->source_key = '';
        $this->target_key = '';
        $this->transform_rule = '';
        $this->inbound_webhook_id = '';
        $this->active = true;
        $this->resetValidation();
    }

    public function render()
    {
        if (! Auth::check() || ! Auth::user()?->isAdmin()) {
            abort(403);
        }

        $mappings = collect();
        $inboundWebhooks = collect();

        $mappings = WebhookMapping::where('organization_id', Auth::user()->organization_id)
            ->where('direction', 'inbound')
            ->latest()
            ->get();

        $inboundWebhooks = InboundWebhook::where('organization_id', Auth::user()->organization_id)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.webhooks.inbound-mappings', [
            'mappings' => $mappings,
            'inboundWebhooks' => $inboundWebhooks,
            'hasTable' => true,
        ])->layout('layouts.app');
    }
}
