<?php

namespace App\Services\Integrations;

use App\Models\IntegrationCredential;
use Illuminate\Support\Facades\Http;

class AmazonLwaTokenService
{
    public function getValidAccessToken(IntegrationCredential $credential): string
    {
        if (! $credential->refresh_token) {
            throw new \RuntimeException('Missing refresh token.');
        }

        if ($credential->access_token && $credential->expires_at && $credential->expires_at->greaterThan(now()->addMinutes(5))) {
            return $credential->access_token;
        }

        $response = Http::asForm()->post('https://api.amazon.com/auth/o2/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $credential->refresh_token,
            'client_id' => config('services.amazon_sp_api.lwa_client_id', ''),
            'client_secret' => config('services.amazon_sp_api.lwa_client_secret', ''),
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('Amazon LWA token refresh failed.');
        }

        $token = $response->json();
        $accessToken = $token['access_token'] ?? null;

        if (! is_string($accessToken) || $accessToken === '') {
            throw new \RuntimeException('Amazon LWA token response missing access_token.');
        }

        $credential->update([
            'access_token' => $accessToken,
            'expires_at' => isset($token['expires_in']) ? now()->addSeconds((int) $token['expires_in']) : now()->addHour(),
            'scopes' => isset($token['scope']) ? explode(' ', (string) $token['scope']) : ($credential->scopes ?? null),
            'last_verified_at' => now(),
        ]);

        return $accessToken;
    }
}
