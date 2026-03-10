<?php

namespace App\Services;

use App\Models\AdAccount;
use App\Models\Campaign;
use App\Models\AdSet;
use App\Models\Ad;
use App\Models\DailyMetric;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GoogleAdsService extends BaseAdsService
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $developerToken;

    public function __construct()
    {
        $this->clientId = config('services.google.client_id', '');
        $this->clientSecret = config('services.google.client_secret', '');
        $this->developerToken = config('services.google.developer_token', '');
    }

    public function getAuthUrl(): string
    {
        $redirectUri = route('ads.callback', ['platform' => 'google']);
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/adwords',
            'access_type' => 'offline',
            'prompt' => 'consent',
        ];
        return "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params);
    }

    public function handleCallback(array $data, string $organizationId, string $clientId): AdAccount
    {
        $code = $data['code'] ?? null;
        if (!$code) {
            throw new \Exception('No code provided for Google Ads callback');
        }

        $redirectUri = route('ads.callback', ['platform' => 'google']);
        $response = Http::post("https://oauth2.googleapis.com/token", [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $redirectUri,
            'code' => $code,
            'grant_type' => 'authorization_code',
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to get access token from Google: ' . $response->body());
        }

        $tokenData = $response->json();
        $accessToken = $tokenData['access_token'];
        $refreshToken = $tokenData['refresh_token'] ?? null;
        $expiresIn = $tokenData['expires_in'];

        // Get accessible customers
        $customersResponse = Http::withToken($accessToken)
            ->withHeaders(['developer-token' => $this->developerToken])
            ->get("https://googleads.googleapis.com/v16/customers:listAccessibleCustomers");

        if ($customersResponse->failed()) {
            throw new \Exception('Failed to fetch customers from Google: ' . $customersResponse->body());
        }

        $resourceNames = $customersResponse->json()['resourceNames'] ?? [];
        if (empty($resourceNames)) {
            throw new \Exception('No Google Ads customers found');
        }

        // Take first customer ID
        $customerId = str_replace('customers/', '', $resourceNames[0]);

        return AdAccount::updateOrCreate(
            [
                'organization_id' => $organizationId,
                'platform' => 'GOOGLE_ADS',
                'external_account_id' => $customerId,
            ],
            [
                'client_id' => $clientId,
                'account_name' => "Google Ads Account " . $customerId,
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_expires_at' => Carbon::now()->addSeconds($expiresIn),
                'status' => 'ACTIVE',
            ]
        );
    }

    public function syncCampaigns(\App\Models\AdAccount $adAccount): Collection
    {
        $this->ensureTokenIsValid($adAccount);

        $response = Http::withToken($adAccount->access_token)
            ->withHeaders(['developer-token' => $this->developerToken])
            ->post("https://googleads.googleapis.com/v16/customers/{$adAccount->external_account_id}/googleAds:search", [
                'query' => 'SELECT campaign.id, campaign.name, campaign.status, campaign.start_date, campaign.end_date, campaign.advertising_channel_type FROM campaign',
            ]);

        if ($response->failed()) {
            Log::error('FE Google sync campaigns failed: ' . $response->body());
            return collect();
        }

        $rows = $response->json()['results'] ?? [];
        $syncedCampaigns = collect();

        foreach ($rows as $row) {
            $data = $row['campaign'];
            $campaign = Campaign::updateOrCreate(
                [
                    'organization_id' => $adAccount->organization_id,
                    'ad_account_id' => $adAccount->id,
                    'external_campaign_id' => $data['id'],
                ],
                [
                    'client_id' => $adAccount->client_id,
                    'name' => $data['name'],
                    'objective' => $data['advertising_channel_type'],
                    'status' => $this->normalizeStatus($data['status']),
                    'start_date' => $data['start_date'] ?? null,
                    'end_date' => $data['end_date'] ?? null,
                ]
            );
            $syncedCampaigns->push($campaign);
        }

        return $syncedCampaigns;
    }

    public function syncAdSets(\App\Models\Campaign $campaign): Collection
    {
        // To be implemented for Google Ads
        return collect();
    }

    public function syncAds(\App\Models\AdSet $adSet): Collection
    {
        // To be implemented for Google Ads
        return collect();
    }

    public function syncInsights(\App\Models\AdAccount $adAccount, string $startDate, string $endDate, string $level): Collection
    {
        // To be implemented for Google Ads
        return collect();
    }

    public function syncMetrics(\App\Models\Campaign $campaign, string $startDate, string $endDate): Collection
    {
        $adAccount = $campaign->adAccount;
        $this->ensureTokenIsValid($adAccount);

        $query = "SELECT metrics.impressions, metrics.clicks, metrics.cost_micros, metrics.conversions, segments.date 
                  FROM campaign 
                  WHERE campaign.id = '{$campaign->external_campaign_id}' 
                  AND segments.date BETWEEN '{$startDate}' AND '{$endDate}'";

        $response = Http::withToken($adAccount->access_token)
            ->withHeaders(['developer-token' => $this->developerToken])
            ->post("https://googleads.googleapis.com/v16/customers/{$adAccount->external_account_id}/googleAds:search", [
                'query' => $query,
            ]);

        if ($response->failed()) {
            Log::error('Google sync metrics failed: ' . $response->body());
            return collect();
        }

        $rows = $response->json()['results'] ?? [];
        $syncedMetrics = collect();

        foreach ($rows as $row) {
            $metrics = $row['metrics'];
            $segments = $row['segments'];

            $metric = DailyMetric::updateOrCreate(
                [
                    'campaign_id' => $campaign->id,
                    'date' => $segments['date'],
                ],
                [
                    'impressions' => $metrics['impressions'] ?? 0,
                    'clicks' => $metrics['clicks'] ?? 0,
                    'spend' => isset($metrics['cost_micros']) ? $metrics['cost_micros'] / 1000000 : 0,
                    'conversions' => $metrics['conversions'] ?? 0,
                ]
            );
            $syncedMetrics->push($metric);
        }

        return $syncedMetrics;
    }

    protected function ensureTokenIsValid(AdAccount $adAccount): void
    {
        if ($adAccount->token_expires_at && Carbon::now()->addMinutes(5)->isAfter($adAccount->token_expires_at)) {
            $this->refreshAccessToken($adAccount);
        }
    }

    protected function refreshAccessToken(AdAccount $adAccount): void
    {
        if (!$adAccount->refresh_token) {
            return;
        }

        $response = Http::post("https://oauth2.googleapis.com/token", [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $adAccount->refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        if ($response->successful()) {
            $tokenData = $response->json();
            $adAccount->update([
                'access_token' => $tokenData['access_token'],
                'token_expires_at' => Carbon::now()->addSeconds($tokenData['expires_in']),
            ]);
        }
    }

    public function pauseCampaign(\App\Models\Campaign $campaign): bool
    {
        return false;
    }

    public function archiveCampaign(\App\Models\Campaign $campaign): bool
    {
        return false;
    }

    public function deleteCampaign(\App\Models\Campaign $campaign): bool
    {
        return false;
    }

    protected function normalizeStatus(string $status): string
    {
        return match ($status) {
            'ENABLED' => 'ACTIVE',
            'PAUSED' => 'INACTIVE',
            'REMOVED' => 'ARCHIVED',
            default => 'INACTIVE',
        };
    }
}
