<?php

namespace App\Http\Controllers;

use App\Models\AdAccount;
use App\Services\MetaAdsService;
use App\Services\GoogleAdsService;
use App\Services\LinkedInAdsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class AdsIntegrationController extends Controller
{
    public function redirect(Request $request, string $platform)
    {
        $clientId = $request->query('client_id');
        if ($clientId) {
            session(['current_connect_client_id' => $clientId]);
        }

        $service = $this->getService($platform);
        return Redirect::away($service->getAuthUrl());
    }

    public function callback(Request $request, string $platform)
    {
        $service = $this->getService($platform);

        $organizationId = auth()->user()->organization_id;

        // For now, we'll try to get client_id from session or default if not provided
        $clientId = session('current_connect_client_id');

        if (!$clientId) {
            // Fallback: use first available client or throw error
            $client = \App\Models\Client::where('organization_id', $organizationId)->first();
            $clientId = $client ? $client->id : null;
        }

        if (!$clientId) {
            return redirect()->route('settings', ['tab' => 'ads'])->with('error', 'No client found to associate with this ad account.');
        }

        try {
            $adAccount = $service->handleCallback($request->all(), $organizationId, $clientId);
            // 3. Dispatch initial sync jobs
            \App\Jobs\SyncAdsStructure::dispatch($adAccount);
            \App\Jobs\SyncAdInsights::dispatch($adAccount, now()->subDays(30)->toDateString(), now()->toDateString());

            return redirect()->route('settings', ['tab' => 'ads'])
                ->with('success', ucfirst($platform) . ' Ads connected and sync started!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Ads Callback Error: " . $e->getMessage());
            return redirect()->route('settings', ['tab' => 'ads'])->with('error', "Failed to connect {$platform} account: " . $e->getMessage());
        }
    }

    public function facebookCallback(Request $request)
    {
        return $this->callback($request, 'meta');
    }

    protected function getService(string $platform)
    {
        return match ($platform) {
            'meta' => new MetaAdsService(),
            'google' => new GoogleAdsService(),
            'linkedin' => new LinkedInAdsService(),
            default => throw new \Exception('Unsupported platform'),
        };
    }
}