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

class LinkedInAdsService extends BaseAdsService
{
    protected string $clientId;
    protected string $clientSecret;

    public function __construct()
    {
        $this->clientId = config('services.linkedin.client_id', '');
        $this->clientSecret = config('services.linkedin.client_secret', '');
    }

    public function getAuthUrl(): string
    {
        $redirectUri = route('ads.callback', ['platform' => 'linkedin']);
        $params = [
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => $redirectUri,
            'scope' => 'r_ads r_ads_reporting rw_ads',
        ];
        return "https://www.linkedin.com/oauth/v2/authorization?" . http_build_query($params);
    }

    public function handleCallback(array $data, string $organizationId, string $clientId): AdAccount
    {
        $code = $data['code'] ?? null;
        if (!$code) {
            throw new \Exception('No code provided for LinkedIn Ads callback');
        }

        $redirectUri = route('ads.callback', ['platform' => 'linkedin']);
        $response = Http::asForm()->post("https://www.linkedin.com/oauth/v2/accessToken", [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to get access token from LinkedIn: ' . $response->body());
        }

        $tokenData = $response->json();
        $accessToken = $tokenData['access_token'];
        $expiresIn = $tokenData['expires_in'];

        // Fetch User's Ad Accounts
        $accountsResponse = Http::withToken($accessToken)
            ->get("https://api.linkedin.com/v2/adAccountsV2", [
                'q' => 'search',
                'search' => '(status:(values:List(ACTIVE)))'
            ]);

        if ($accountsResponse->failed()) {
            throw new \Exception('Failed to fetch ad accounts from LinkedIn: ' . $accountsResponse->body());
        }

        $accounts = $accountsResponse->json()['elements'];
        if (empty($accounts)) {
            throw new \Exception('No active ad accounts found for this LinkedIn user');
        }

        // Take the first one
        $accountData = $accounts[0];
        $externalId = str_replace('urn:li:sponsoredAccount:', '', $accountData['id']);

        return AdAccount::updateOrCreate(
            [
                'organization_id' => $organizationId,
                'platform' => 'LINKEDIN_ADS',
                'external_account_id' => $externalId,
            ],
            [
                'client_id' => $clientId,
                'account_name' => $accountData['name'],
                'currency_code' => $accountData['currency'] ?? 'USD',
                'access_token' => $accessToken,
                'token_expires_at' => Carbon::now()->addSeconds($expiresIn),
                'status' => 'ACTIVE',
            ]
        );
    }

    public function syncCampaigns(\App\Models\AdAccount $adAccount): Collection
    {
        $response = Http::withToken($adAccount->access_token)
            ->get("https://api.linkedin.com/v2/adCampaignsV2", [
                'q' => 'search',
                'search' => "(account:(values:List(urn:li:sponsoredAccount:{$adAccount->external_account_id})))"
            ]);

        if ($response->failed()) {
            Log::error('LinkedIn sync campaigns failed: ' . $response->body());
            return collect();
        }

        $campaignsData = $response->json()['elements'];
        $syncedCampaigns = collect();

        foreach ($campaignsData as $data) {
            $externalId = str_replace('urn:li:sponsoredCampaign:', '', $data['id']);

            $campaign = Campaign::updateOrCreate(
                [
                    'organization_id' => $adAccount->organization_id,
                    'ad_account_id' => $adAccount->id,
                    'external_campaign_id' => $externalId,
                ],
                [
                    'client_id' => $adAccount->client_id,
                    'name' => $data['name'],
                    'objective' => $data['objectiveType'] ?? null,
                    'status' => $this->normalizeStatus($data['status']),
                    'daily_budget' => isset($data['dailyBudget']['amount']) ? $data['dailyBudget']['amount'] : null,
                ]
            );
            $syncedCampaigns->push($campaign);
        }

        return $syncedCampaigns;
    }

    public function syncAdSets(\App\Models\Campaign $campaign): Collection
    {
        // To be implemented for LinkedIn Ads
        return collect();
    }

    public function syncAds(\App\Models\AdSet $adSet): Collection
    {
        // To be implemented for LinkedIn Ads
        return collect();
    }

    public function syncInsights(\App\Models\AdAccount $adAccount, string $startDate, string $endDate, string $level): Collection
    {
        // To be implemented for LinkedIn Ads
        return collect();
    }

    public function syncMetrics(\App\Models\Campaign $campaign, string $startDate, string $endDate): Collection
    {
        $adAccount = $campaign->adAccount;

        // LinkedIn date format in query: (start:(day:1,month:1,year:2024))
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $dateRange = "(start:(day:{$start->day},month:{$start->month},year:{$start->year}),end:(day:{$end->day},month:{$end->month},year:{$end->year}))";

        $response = Http::withToken($adAccount->access_token)
            ->get("https://api.linkedin.com/v2/adAnalyticsV2", [
                'q' => 'analytics',
                'pivot' => 'CAMPAIGN',
                'dateRange' => $dateRange,
                'timeGranularity' => 'DAILY',
                'campaigns' => "List(urn:li:sponsoredCampaign:{$campaign->external_campaign_id})",
                'fields' => 'externalAdUnitHierarchy,dateRange,impressions,clicks,costInLocalCurrency,totalEngagements',
            ]);

        if ($response->failed()) {
            Log::error('LinkedIn sync metrics failed: ' . $response->body());
            return collect();
        }

        $elements = $response->json()['elements'] ?? [];
        $syncedMetrics = collect();

        foreach ($elements as $element) {
            $date = Carbon::create(
                $element['dateRange']['start']['year'],
                $element['dateRange']['start']['month'],
                $element['dateRange']['start']['day']
            )->toDateString();

            $metric = DailyMetric::updateOrCreate(
                [
                    'campaign_id' => $campaign->id,
                    'date' => $date,
                ],
                [
                    'impressions' => $element['impressions'] ?? 0,
                    'clicks' => $element['clicks'] ?? 0,
                    'spend' => $element['costInLocalCurrency'] ?? 0,
                    'conversions' => 0, // Conversions often require separate pivot or reach
                ]
            );
            $syncedMetrics->push($metric);
        }

        return $syncedMetrics;
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
            'ACTIVE' => 'ACTIVE',
            'PAUSED' => 'INACTIVE',
            'ARCHIVED' => 'ARCHIVED',
            'CANCELED' => 'ARCHIVED',
            'DRAFT' => 'INACTIVE',
            default => 'INACTIVE',
        };
    }
}
