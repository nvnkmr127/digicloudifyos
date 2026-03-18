<?php

namespace App\Livewire\Webhooks;

use App\Models\InboundWebhook;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use App\Models\WebhookMapping;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
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
            'outbound_count' => 0,
            'inbound_count' => 0,
            'inbound_mappings_count' => 0,
            'outbound_mappings_count' => 0,
            'recent_deliveries' => collect(),
        ];

        if (Schema::hasTable('webhooks')) {
            $overview['outbound_count'] = Webhook::where('organization_id', $organizationId)->count();
        }

        if (Schema::hasTable('inbound_webhooks')) {
            $overview['inbound_count'] = InboundWebhook::where('organization_id', $organizationId)->count();
        }

        if (Schema::hasTable('webhook_mappings')) {
            $overview['inbound_mappings_count'] = WebhookMapping::where('organization_id', $organizationId)
                ->where('direction', 'inbound')
                ->count();

            $overview['outbound_mappings_count'] = WebhookMapping::where('organization_id', $organizationId)
                ->where('direction', 'outbound')
                ->count();
        }

        if (Schema::hasTable('webhook_deliveries') && Schema::hasTable('webhooks')) {
            $overview['recent_deliveries'] = WebhookDelivery::query()
                ->whereHas('webhook', function ($query) use ($organizationId) {
                    $query->where('organization_id', $organizationId);
                })
                ->latest()
                ->limit(5)
                ->get();
        }

        return view('livewire.webhooks.index', $overview)->layout('layouts.app');
    }
}
