<?php

namespace App\Observers;

use App\Models\Client;
use App\Models\ClientOnboardingChecklist;
use App\Services\AgencyManagementService;

class ClientObserver
{
    /**
     * Handle the Client "created" event.
     */
    public function created(Client $client): void
    {
        // Security: Clear agency dashboard cache when client data changes (B002)
        app(AgencyManagementService::class)->clearCache($client->organization_id);

        $items = [];
        $defaults = config('onboarding.default_items', []);
        foreach ($defaults as $category => $tasks) {
            foreach ($tasks as $task) {
                $items[] = array_merge($task, [
                    'category' => $category,
                    'completed' => false,
                    'completed_at' => null,
                ]);
            }
        }

        ClientOnboardingChecklist::create([
            'organization_id' => $client->organization_id,
            'client_id' => $client->id,
            'items' => $items,
        ]);

        \Log::debug('ClientObserver: Onboarding checklist created', ['client_id' => $client->id]);
    }

    public function updated(Client $client): void
    {
        app(AgencyManagementService::class)->clearCache($client->organization_id);
    }

    public function deleted(Client $client): void
    {
        app(AgencyManagementService::class)->clearCache($client->organization_id);
    }
}
