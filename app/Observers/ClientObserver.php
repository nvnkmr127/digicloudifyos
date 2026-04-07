<?php

namespace App\Observers;

use App\Models\Client;
use App\Models\ClientOnboardingChecklist;

class ClientObserver
{
    /**
     * Handle the Client "created" event.
     */
    public function created(Client $client): void
    {
        \Log::debug('ClientObserver: created event fired', ['client_id' => $client->id]);

        $defaults = config('onboarding.default_items', []);
        $initialized = [];

        foreach ($defaults as $category => $tasks) {
            foreach ($tasks as $task) {
                $initialized[] = array_merge($task, [
                    'category' => $category,
                    'completed' => false,
                    'completed_at' => null,
                ]);
            }
        }

        ClientOnboardingChecklist::create([
            'organization_id' => $client->organization_id,
            'client_id' => $client->id,
            'items' => $initialized,
        ]);
        
        \Log::debug('ClientObserver: Onboarding checklist created', ['client_id' => $client->id]);
    }
}
