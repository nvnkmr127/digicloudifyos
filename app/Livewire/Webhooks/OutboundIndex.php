<?php

namespace App\Livewire\Webhooks;

use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class OutboundIndex extends Component
{
    private const ALLOWED_EVENTS = [
        'lead_created',
        'lead_captured',
        'task_completed',
        'campaign_created',
        'creative_requested',
        'creative_approved',
    ];

    public $webhookId = null;
    public $name = '';
    public $url = '';
    public $events = 'lead_created, task_completed';
    public $secret = '';
    public $active = true;

    public function mount(): void
    {
        if (! Auth::check() || ! Auth::user()?->isAdmin()) {
            abort(403);
        }
    }

    public function save(): void
    {
        if (! Schema::hasTable('webhooks')) {
            session()->flash('error', 'Webhooks table is not available. Run migrations first.');
            return;
        }

        $validated = $this->validate([
            'name' => 'required|string|min:3|max:255',
            'url' => 'required|url|max:1000',
            'events' => 'required|string|max:1000',
            'secret' => 'nullable|string|max:255',
            'active' => 'boolean',
        ]);

        if (app()->environment('production') && str_starts_with(strtolower($validated['url']), 'http://')) {
            $this->addError('url', 'Only HTTPS webhook URLs are allowed in production.');
            return;
        }

        $eventList = collect(explode(',', $validated['events']))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->map(fn ($item) => strtolower($item))
            ->unique()
            ->values()
            ->all();

        if (empty($eventList)) {
            $this->addError('events', 'Provide at least one event.');
            return;
        }

        $unknownEvents = collect($eventList)
            ->reject(fn ($event) => in_array($event, self::ALLOWED_EVENTS, true))
            ->values();

        if ($unknownEvents->isNotEmpty()) {
            $this->addError('events', 'Unsupported events: ' . $unknownEvents->implode(', '));
            return;
        }

        $payload = [
            'organization_id' => Auth::user()->organization_id,
            'name' => $validated['name'],
            'url' => $validated['url'],
            'events' => $eventList,
            'secret' => $validated['secret'] ?: null,
            'active' => (bool) $validated['active'],
        ];

        if ($this->webhookId) {
            $webhook = Webhook::where('organization_id', Auth::user()->organization_id)
                ->findOrFail($this->webhookId);
            $webhook->update($payload);
            session()->flash('success', 'Outbound webhook updated successfully.');
        } else {
            Webhook::create($payload);
            session()->flash('success', 'Outbound webhook created successfully.');
        }

        $this->resetForm();
    }

    public function edit(string $id): void
    {
        $webhook = Webhook::where('organization_id', Auth::user()->organization_id)
            ->findOrFail($id);

        $this->webhookId = $webhook->id;
        $this->name = $webhook->name;
        $this->url = $webhook->url;
        $this->events = implode(', ', $webhook->events ?? []);
        $this->secret = $webhook->secret ?? '';
        $this->active = (bool) $webhook->active;
    }

    public function delete(string $id): void
    {
        Webhook::where('organization_id', Auth::user()->organization_id)
            ->findOrFail($id)
            ->delete();

        if ($this->webhookId === $id) {
            $this->resetForm();
        }

        session()->flash('success', 'Outbound webhook deleted successfully.');
    }

    public function resetForm(): void
    {
        $this->webhookId = null;
        $this->name = '';
        $this->url = '';
        $this->events = 'lead_created, task_completed';
        $this->secret = '';
        $this->active = true;
        $this->resetValidation();
    }

    public function render()
    {
        if (! Auth::check() || ! Auth::user()?->isAdmin()) {
            abort(403);
        }

        $webhooks = collect();
        $recentDeliveries = collect();

        if (Schema::hasTable('webhooks')) {
            $webhooks = Webhook::where('organization_id', Auth::user()->organization_id)
                ->latest()
                ->get();
        }

        if (Schema::hasTable('webhook_deliveries') && Schema::hasTable('webhooks')) {
            $recentDeliveries = WebhookDelivery::query()
                ->whereHas('webhook', function ($query) {
                    $query->where('organization_id', Auth::user()->organization_id);
                })
                ->latest()
                ->limit(10)
                ->get();
        }

        return view('livewire.webhooks.outbound-index', [
            'webhooks' => $webhooks,
            'recentDeliveries' => $recentDeliveries,
            'hasTable' => Schema::hasTable('webhooks'),
            'hasDeliveryTable' => Schema::hasTable('webhook_deliveries'),
        ])->layout('layouts.app');
    }
}
