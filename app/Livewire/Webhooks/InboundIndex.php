<?php

namespace App\Livewire\Webhooks;

use App\Models\InboundWebhook;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Component;

class InboundIndex extends Component
{
    public $inboundWebhookId = null;

    public $name = '';

    public $provider = 'custom';

    public $verify_token = '';

    public $signing_secret = '';

    public $active = true;

    public function mount(): void
    {
        if (! Auth::check() || ! Auth::user()?->isAdmin()) {
            abort(403);
        }
    }

    public function save(): void
    {
        if (! Schema::hasTable('inbound_webhooks')) {
            session()->flash('error', 'Inbound webhook table is not available. Run migrations first.');

            return;
        }

        $validated = $this->validate([
            'name' => 'required|string|min:3|max:255',
            'provider' => 'required|string|max:100',
            'verify_token' => 'nullable|string|max:255',
            'signing_secret' => 'nullable|string|max:255',
            'active' => 'boolean',
        ]);

        $payload = [
            'organization_id' => Auth::user()->organization_id,
            'name' => $validated['name'],
            'provider' => $validated['provider'],
            'verify_token' => $validated['verify_token'] ?: null,
            'signing_secret' => $validated['signing_secret'] ?: null,
            'active' => (bool) $validated['active'],
        ];

        if ($this->inboundWebhookId) {
            $webhook = InboundWebhook::where('organization_id', Auth::user()->organization_id)
                ->findOrFail($this->inboundWebhookId);
            $webhook->update($payload);
            session()->flash('success', 'Inbound webhook updated successfully.');
        } else {
            $payload['endpoint_key'] = (string) Str::uuid();
            InboundWebhook::create($payload);
            session()->flash('success', 'Inbound webhook created successfully.');
        }

        $this->resetForm();
    }

    public function edit(string $id): void
    {
        $webhook = InboundWebhook::where('organization_id', Auth::user()->organization_id)
            ->findOrFail($id);

        $this->inboundWebhookId = $webhook->id;
        $this->name = $webhook->name;
        $this->provider = $webhook->provider;
        $this->verify_token = $webhook->verify_token ?? '';
        $this->signing_secret = $webhook->signing_secret ?? '';
        $this->active = (bool) $webhook->active;
    }

    public function delete(string $id): void
    {
        InboundWebhook::where('organization_id', Auth::user()->organization_id)
            ->findOrFail($id)
            ->delete();

        if ($this->inboundWebhookId === $id) {
            $this->resetForm();
        }

        session()->flash('success', 'Inbound webhook deleted successfully.');
    }

    public function resetForm(): void
    {
        $this->inboundWebhookId = null;
        $this->name = '';
        $this->provider = 'custom';
        $this->verify_token = '';
        $this->signing_secret = '';
        $this->active = true;
        $this->resetValidation();
    }

    public function render()
    {
        if (! Auth::check() || ! Auth::user()?->isAdmin()) {
            abort(403);
        }

        $inboundWebhooks = collect();

        if (Schema::hasTable('inbound_webhooks')) {
            $inboundWebhooks = InboundWebhook::where('organization_id', Auth::user()->organization_id)
                ->latest()
                ->get();
        }

        return view('livewire.webhooks.inbound-index', [
            'inboundWebhooks' => $inboundWebhooks,
            'hasTable' => Schema::hasTable('inbound_webhooks'),
        ])->layout('layouts.app');
    }
}
