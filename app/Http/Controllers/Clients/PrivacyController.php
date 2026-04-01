<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrivacyController extends Controller
{
    public function export(Request $request, Client $client)
    {
        $user = Auth::user();
        if (! $user instanceof \App\Models\User || ! $user->isAdmin()) {
            abort(403);
        }

        if ($client->organization_id !== $user->organization_id) {
            abort(403);
        }

        $client->load([
            'channelConnections',
            'campaigns',
            'leads',
            'tasks',
        ]);

        $payload = [
            'client' => $client->toArray(),
            'exported_at' => now()->toIso8601String(),
            'summary' => [
                'campaigns_count' => $client->campaigns->count(),
                'leads_count' => $client->leads->count(),
                'tasks_count' => $client->tasks->count(),
                'connections_count' => $client->channelConnections->count(),
            ],
        ];

        $filename = 'client-export-' . $client->id . '-' . now()->format('Ymd_His') . '.json';

        return response()->json($payload)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function erase(Request $request, Client $client)
    {
        $user = Auth::user();
        if (! $user instanceof \App\Models\User || ! $user->isAdmin()) {
            abort(403);
        }

        if ($client->organization_id !== $user->organization_id) {
            abort(403);
        }

        $client->channelConnections()->update([
            'is_active' => false,
            'integration_credential_id' => null,
            'sync_disabled_at' => now(),
            'last_sync_status' => 'disabled',
        ]);

        $client->update([
            'email' => null,
            'website_url' => null,
            'phone' => null,
            'external_ref' => null,
            'timezone' => null,
            'currency_code' => null,
            'address_line1' => null,
            'address_line2' => null,
            'city' => null,
            'state' => null,
            'postal_code' => null,
            'country_code' => null,
            'business_description' => null,
            'goals' => null,
            'target_audience' => null,
            'competitors' => null,
            'primary_kpis' => null,
            'privacy_contact_email' => null,
            'status' => 'ARCHIVED',
        ]);

        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client data erased and archived.');
    }
}

