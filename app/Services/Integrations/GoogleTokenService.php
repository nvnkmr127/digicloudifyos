<?php

namespace App\Services\Integrations;

use App\Models\IntegrationCredential;
use Illuminate\Support\Facades\Http;

class GoogleTokenService
{
    public function getValidAccessToken(IntegrationCredential $credential): string
    {
        if (! $credential->access_token) {
            throw new \RuntimeException('Missing access token.');
        }

        if (! $credential->expires_at) {
            return $credential->access_token;
        }

        if ($credential->expires_at->greaterThan(now()->addMinutes(5))) {
            return $credential->access_token;
        }

        if (! $credential->refresh_token) {
            return $credential->access_token;
        }

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('services.google.client_id', ''),
            'client_secret' => config('services.google.client_secret', ''),
            'grant_type' => 'refresh_token',
            'refresh_token' => $credential->refresh_token,
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('Token refresh failed.');
        }

        $token = $response->json();
        $accessToken = $token['access_token'] ?? null;

        if (! is_string($accessToken) || $accessToken === '') {
            throw new \RuntimeException('Token refresh did not return access_token.');
        }

        $credential->update([
            'access_token' => $accessToken,
            'expires_at' => isset($token['expires_in']) ? now()->addSeconds((int) $token['expires_in']) : null,
            'scopes' => isset($token['scope']) ? explode(' ', (string) $token['scope']) : ($credential->scopes ?? null),
            'last_verified_at' => now(),
        ]);

        return $accessToken;
    }
}
