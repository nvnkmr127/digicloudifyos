<?php

namespace App\Services\Integrations;

use App\Models\Client;
use App\Models\Organization;
use App\Notifications\IntegrationSyncFailedNotification;

class IntegrationAlertService
{
    public function notifySyncFailure(string $organizationId, string $clientId, string $channelType, string $runDate, string $errorMessage): void
    {
        $client = Client::where('organization_id', $organizationId)->find($clientId);
        if (! $client) return;

        $org = Organization::find($organizationId);
        if (! $org) return;

        foreach ($org->users as $user) {
            if (! $user instanceof \App\Models\User) continue;
            if (! $user->isAdmin()) continue;
            if (! $user->email) continue;

            $user->notify(new IntegrationSyncFailedNotification(
                $client->name,
                $channelType,
                $runDate,
                $errorMessage
            ));
        }
    }
}

