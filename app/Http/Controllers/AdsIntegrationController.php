<?php

namespace App\Http\Controllers;

use App\Jobs\SyncAdInsights;
use App\Jobs\SyncAdsStructure;
use App\Models\Client;
use App\Models\User;
use App\Services\GoogleAdsService;
use App\Services\LinkedInAdsService;
use App\Services\MetaAdsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class AdsIntegrationController extends Controller
{
    public function redirect(Request $request, string $platform)
    {
        $clientId = $request->query('client_id');
        if ($clientId) {
            session(['current_connect_client_id' => $clientId]);
        }

        $service = $this->getService($platform);
        $state = Str::random(64);
        session(["ads.oauth_state.{$platform}" => $state]);

        $url = $this->appendQueryParams($service->getAuthUrl(), ['state' => $state]);

        return Redirect::away($url);
    }

    public function callback(Request $request, string $platform)
    {
        $expectedState = session("ads.oauth_state.{$platform}");
        $incomingState = $request->query('state');

        if (! is_string($expectedState) || ! is_string($incomingState) || ! hash_equals($expectedState, $incomingState)) {
            return redirect()->route('settings', ['tab' => 'ads'])->with('error', 'Invalid OAuth state. Please try connecting again.');
        }

        session()->forget("ads.oauth_state.{$platform}");

        $service = $this->getService($platform);

        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }

        $organizationId = $user->organization_id;

        // For now, we'll try to get client_id from session or default if not provided
        $clientId = session('current_connect_client_id');

        if (! $clientId) {
            // Fallback: use first available client or throw error
            $client = Client::where('organization_id', $organizationId)->first();
            $clientId = $client ? $client->id : null;
        }

        if (! $clientId) {
            return redirect()->route('settings', ['tab' => 'ads'])->with('error', 'No client found to associate with this ad account.');
        }

        try {
            $adAccount = $service->handleCallback($request->all(), $organizationId, $clientId);
            // 3. Dispatch initial sync jobs
            SyncAdsStructure::dispatch($adAccount);
            SyncAdInsights::dispatch($adAccount, now()->subDays(30)->toDateString(), now()->toDateString());

            return redirect()->route('settings', ['tab' => 'ads'])
                ->with('success', ucfirst($platform).' Ads connected and sync started!');
        } catch (\Exception $e) {
            Log::error('Ads Callback Error: '.$e->getMessage());

            return redirect()->route('settings', ['tab' => 'ads'])->with('error', "Failed to connect {$platform} account: ".$e->getMessage());
        }
    }

    public function facebookCallback(Request $request)
    {
        return $this->callback($request, 'meta');
    }

    protected function getService(string $platform)
    {
        return match ($platform) {
            'meta' => new MetaAdsService,
            'google' => new GoogleAdsService,
            'linkedin' => new LinkedInAdsService,
            default => throw new \Exception('Unsupported platform'),
        };
    }

    protected function appendQueryParams(string $url, array $params): string
    {
        $parts = parse_url($url);
        $query = [];

        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        foreach ($params as $key => $value) {
            $query[$key] = $value;
        }

        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';
        $path = $parts['path'] ?? '';
        $fragment = isset($parts['fragment']) ? '#'.$parts['fragment'] : '';

        $newQuery = http_build_query($query);
        $qs = $newQuery !== '' ? '?'.$newQuery : '';

        return "{$scheme}://{$host}{$port}{$path}{$qs}{$fragment}";
    }
}
