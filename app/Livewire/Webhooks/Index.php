<?php

namespace App\Livewire\Webhooks;

use App\Models\InboundWebhook;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use App\Models\WebhookMapping;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public function mount(): void
    {
        if (! Auth::check() || ! Auth::user()?->isAdmin()) {
            abort(403);
        }
    }

    public function render()
    {
        if (! Auth::check() || ! Auth::user()?->isAdmin()) {
            abort(403);
        }

        $organizationId = Auth::user()->organization_id;

        $overview = [
            'outbound_count' => Webhook::where('organization_id', $organizationId)->count(),
            'inbound_count' => InboundWebhook::where('organization_id', $organizationId)->count(),
            'inbound_mappings_count' => WebhookMapping::where('organization_id', $organizationId)
                ->where('direction', 'inbound')
                ->count(),
            'outbound_mappings_count' => WebhookMapping::where('organization_id', $organizationId)
                ->where('direction', 'outbound')
                ->count(),
            'recent_deliveries' => WebhookDelivery::query()
                ->whereHas('webhook', function ($query) use ($organizationId) {
                    $query->where('organization_id', $organizationId);
                })
                ->latest()
                ->limit(5)
                ->get(),
        ];

        return view('livewire.webhooks.index', $overview)->layout('layouts.app');
    }
}
