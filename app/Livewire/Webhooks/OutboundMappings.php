<?php

namespace App\Livewire\Webhooks;

use App\Models\Webhook;
use App\Models\WebhookMapping;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class OutboundMappings extends Component
{
    public $mappingId = null;

    public $name = '';

    public $source_key = '';

    public $target_key = '';

    public $transform_rule = '';

    public $webhook_id = '';

    public $active = true;

    public function mount(): void
    {
        if (! Auth::check() || ! Auth::user()?->isAdmin()) {
            abort(403);
        }
    }

    public function save(): void
    {
        if (! Schema::hasTable('webhook_mappings')) {
            session()->flash('error', 'Webhook mappings table is not available. Run migrations first.');

            return;
        }

        $validated = $this->validate([
            'name' => 'required|string|min:3|max:255',
            'source_key' => 'required|string|max:255',
            'target_key' => 'required|string|max:255',
            'transform_rule' => 'nullable|string|max:255',
            'webhook_id' => 'nullable|string',
            'active' => 'boolean',
        ]);

        $payload = [
            'organization_id' => Auth::user()->organization_id,
            'direction' => 'outbound',
            'name' => $validated['name'],
            'source_key' => $validated['source_key'],
            'target_key' => $validated['target_key'],
            'transform_rule' => $validated['transform_rule'] ?: null,
            'webhook_id' => $validated['webhook_id'] ?: null,
            'active' => (bool) $validated['active'],
        ];

        if ($this->mappingId) {
            $mapping = WebhookMapping::where('organization_id', Auth::user()->organization_id)
                ->where('direction', 'outbound')
                ->findOrFail($this->mappingId);
            $mapping->update($payload);
            session()->flash('success', 'Outbound mapping updated successfully.');
        } else {
            WebhookMapping::create($payload);
            session()->flash('success', 'Outbound mapping created successfully.');
        }

        $this->resetForm();
    }

    public function edit(string $id): void
    {
        $mapping = WebhookMapping::where('organization_id', Auth::user()->organization_id)
            ->where('direction', 'outbound')
            ->findOrFail($id);

        $this->mappingId = $mapping->id;
        $this->name = $mapping->name;
        $this->source_key = $mapping->source_key;
        $this->target_key = $mapping->target_key;
        $this->transform_rule = $mapping->transform_rule ?? '';
        $this->webhook_id = $mapping->webhook_id ?? '';
        $this->active = (bool) $mapping->active;
    }

    public function delete(string $id): void
    {
        WebhookMapping::where('organization_id', Auth::user()->organization_id)
            ->where('direction', 'outbound')
            ->findOrFail($id)
            ->delete();

        if ($this->mappingId === $id) {
            $this->resetForm();
        }

        session()->flash('success', 'Outbound mapping deleted successfully.');
    }

    public function resetForm(): void
    {
        $this->mappingId = null;
        $this->name = '';
        $this->source_key = '';
        $this->target_key = '';
        $this->transform_rule = '';
        $this->webhook_id = '';
        $this->active = true;
        $this->resetValidation();
    }

    public function render()
    {
        if (! Auth::check() || ! Auth::user()?->isAdmin()) {
            abort(403);
        }

        $mappings = collect();
        $outboundWebhooks = collect();

        if (Schema::hasTable('webhook_mappings')) {
            $mappings = WebhookMapping::where('organization_id', Auth::user()->organization_id)
                ->where('direction', 'outbound')
                ->latest()
                ->get();
        }

        if (Schema::hasTable('webhooks')) {
            $outboundWebhooks = Webhook::where('organization_id', Auth::user()->organization_id)
                ->where('active', true)
                ->orderBy('name')
                ->get();
        }

        return view('livewire.webhooks.outbound-mappings', [
            'mappings' => $mappings,
            'outboundWebhooks' => $outboundWebhooks,
            'hasTable' => Schema::hasTable('webhook_mappings'),
        ])->layout('layouts.app');
    }
}
