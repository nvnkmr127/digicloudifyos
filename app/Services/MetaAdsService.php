<?php

namespace App\Services;

use App\Models\AdAccount;
use App\Models\Campaign;
use App\Models\AdSet;
use App\Models\Ad;
use App\Models\AdCreative;
use App\Models\AdInsight;
use App\Models\DailyMetric;
use App\Models\FacebookLead;
use App\Models\Creative;
use App\Models\ConversionEvent;
use App\Models\FunnelMetric;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use FacebookAds\Api;
use FacebookAds\Object\AdAccount as MetaAdAccount;
use FacebookAds\Object\Campaign as MetaCampaign;
use FacebookAds\Object\AdSet as MetaAdSet;
use FacebookAds\Object\Ad as MetaAd;
use FacebookAds\Object\AdCreative as MetaAdCreative;
use FacebookAds\Object\Lead;
use FacebookAds\Object\LeadgenForm;
use FacebookAds\Object\Page;
use FacebookAds\Object\Fields\CampaignFields;
use FacebookAds\Object\Fields\AdSetFields;
use FacebookAds\Object\Fields\AdFields;
use FacebookAds\Object\Fields\AdCreativeFields;
use FacebookAds\Object\Fields\AdsInsightsFields;
use FacebookAds\Object\Fields\LeadFields;
use FacebookAds\Object\Fields\LeadgenFormFields;

class MetaAdsService extends BaseAdsService
{
    protected string $appId;
    protected string $appSecret;
    protected string $apiVersion = 'v25.0';

    public function __construct()
    {
        $this->appId = config('services.facebook.client_id', '');
        $this->appSecret = config('services.facebook.client_secret', '');
    }

    protected function initSdk(string $accessToken): void
    {
        try {
            Api::init($this->appId, $this->appSecret, $accessToken);
        } catch (\Exception $e) {
            Log::error('Meta SDK Init failed: ' . $e->getMessage());
        }
    }

    public function getAuthUrl(): string
    {
        $redirectUri = route('auth.facebook.callback');
        return "https://www.facebook.com/{$this->apiVersion}/dialog/oauth?client_id={$this->appId}&redirect_uri={$redirectUri}&scope=ads_management,ads_read,business_management,pages_manage_ads,pages_show_list";
    }

    public function handleCallback(array $data, string $organizationId, string $clientId): AdAccount
    {
        $code = $data['code'] ?? null;
        if (!$code) {
            throw new \Exception('No code provided for Meta Ads callback');
        }

        $redirectUri = route('auth.facebook.callback');
        $response = Http::get("https://graph.facebook.com/{$this->apiVersion}/oauth/access_token", [
            'client_id' => $this->appId,
            'client_secret' => $this->appSecret,
            'redirect_uri' => $redirectUri,
            'code' => $code,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to get access token from Meta: ' . $response->body());
        }

        $tokenData = $response->json();
        $accessTokenValue = $tokenData['access_token'];

        // Get long-lived token
        $longLivedResponse = Http::get("https://graph.facebook.com/{$this->apiVersion}/oauth/access_token", [
            'grant_type' => 'fb_exchange_token',
            'client_id' => $this->appId,
            'client_secret' => $this->appSecret,
            'fb_exchange_token' => $accessTokenValue,
        ]);

        if ($longLivedResponse->successful()) {
            $tokenData = $longLivedResponse->json();
            $accessTokenValue = $tokenData['access_token'];
        }

        // Fetch User Info
        $userResponse = Http::withToken($accessTokenValue)->get("https://graph.facebook.com/{$this->apiVersion}/me", [
            'fields' => 'id,name,email,picture',
        ]);

        if ($userResponse->failed()) {
            throw new \Exception('Failed to fetch user info from Meta: ' . $userResponse->body());
        }

        $userData = $userResponse->json();

        // 1. Create/Update Access Token
        $accessToken = \App\Models\AccessToken::updateOrCreate(
            [
                'organization_id' => $organizationId,
                'platform' => 'meta',
            ],
            [
                'access_token' => $accessTokenValue,
                'expires_at' => isset($tokenData['expires_in']) ? now()->addSeconds($tokenData['expires_in']) : null,
            ]
        );

        // 2. Create/Update Facebook Connection (Requested Phase 2)
        \App\Models\FacebookConnection::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'facebook_user_id' => $userData['id'],
            ],
            [
                'access_token' => $accessTokenValue,
                'token_expires' => isset($tokenData['expires_in']) ? now()->addSeconds($tokenData['expires_in']) : null,
                'business_id' => $userData['business_id'] ?? null, // Will be null unless specifically requested in fields
            ]
        );

        // 3. Create/Update Facebook User
        \App\Models\FacebookUser::updateOrCreate(
            [
                'organization_id' => $organizationId,
                'facebook_user_id' => $userData['id'],
            ],
            [
                'access_token_id' => $accessToken->id,
                'name' => $userData['name'],
                'email' => $userData['email'] ?? null,
                'profile_pic' => $userData['picture']['data']['url'] ?? null,
            ]
        );

        // Fetch User's Ad Accounts (Discovery Phase 3)
        $accountsResponse = Http::withToken($accessTokenValue)->get("https://graph.facebook.com/{$this->apiVersion}/me/adaccounts", [
            'fields' => 'name,account_id,currency,timezone_name,account_status',
        ]);

        if ($accountsResponse->failed()) {
            throw new \Exception('Failed to fetch ad accounts from Meta: ' . $accountsResponse->body());
        }

        $accountsData = $accountsResponse->json()['data'] ?? [];
        if (empty($accountsData)) {
            throw new \Exception('No ad accounts found for this Meta user');
        }

        $storedAccounts = collect();

        foreach ($accountsData as $accountData) {
            $status = match ($accountData['account_status']) {
                1 => 'ACTIVE',
                2 => 'INACTIVE',
                3 => 'ARCHIVED',
                default => 'INACTIVE',
            };

            $adAccount = AdAccount::updateOrCreate(
                [
                    'organization_id' => $organizationId,
                    'platform' => 'META_ADS',
                    'external_account_id' => $accountData['account_id'],
                ],
                [
                    'client_id' => $clientId, // Associated with provided client during connection
                    'access_token_id' => $accessToken->id,
                    'account_name' => $accountData['name'],
                    'currency_code' => $accountData['currency'],
                    'timezone' => $accountData['timezone_name'],
                    'status' => $status,
                ]
            );
            $storedAccounts->push($adAccount);
        }

        return $storedAccounts->first(); // Return first for immediate UI flow, others synced in BG if needed
    }

    public function syncCampaigns(\App\Models\AdAccount $adAccount): Collection
    {
        $token = $adAccount->access_token ?? $adAccount->accessToken?->access_token;
        if (!$token) {
            Log::error('No access token found for ad account: ' . $adAccount->id);
            return collect();
        }

        $this->initSdk($token);
        $account = new MetaAdAccount('act_' . $adAccount->external_account_id);

        $fields = [
            CampaignFields::ID,
            CampaignFields::NAME,
            CampaignFields::STATUS,
            CampaignFields::OBJECTIVE,
            CampaignFields::START_TIME,
            CampaignFields::STOP_TIME,
            CampaignFields::DAILY_BUDGET,
            CampaignFields::LIFETIME_BUDGET,
            CampaignFields::SPEND_CAP,
        ];

        try {
            $campaigns = $account->getCampaigns($fields);
            $syncedCampaigns = collect();

            foreach ($campaigns as $data) {
                $campaign = Campaign::updateOrCreate(
                    [
                        'organization_id' => $adAccount->organization_id,
                        'ad_account_id' => $adAccount->id,
                        'external_campaign_id' => $data->{CampaignFields::ID},
                    ],
                    [
                        'client_id' => $adAccount->client_id,
                        'name' => $data->{CampaignFields::NAME},
                        'objective' => $data->{CampaignFields::OBJECTIVE},
                        'status' => $this->normalizeStatus($data->{CampaignFields::STATUS}),
                        'start_date' => $data->{CampaignFields::START_TIME} ? Carbon::parse($data->{CampaignFields::START_TIME})->toDateString() : null,
                        'end_date' => $data->{CampaignFields::STOP_TIME} ? Carbon::parse($data->{CampaignFields::STOP_TIME})->toDateString() : null,
                        'daily_budget' => $data->{CampaignFields::DAILY_BUDGET} ? $data->{CampaignFields::DAILY_BUDGET} / 100 : null,
                        'lifetime_budget' => $data->{CampaignFields::LIFETIME_BUDGET} ? $data->{CampaignFields::LIFETIME_BUDGET} / 100 : null,
                        'spend_cap' => $data->{CampaignFields::SPEND_CAP} ? $data->{CampaignFields::SPEND_CAP} / 100 : null,
                    ]
                );
                $syncedCampaigns->push($campaign);
            }

            return $syncedCampaigns;
        } catch (\Exception $e) {
            Log::error('Meta sync campaigns failed: ' . $e->getMessage());
            return collect();
        }
    }

    public function syncAdSets(\App\Models\Campaign $campaign): Collection
    {
        $adAccount = $campaign->adAccount;
        $token = $adAccount->access_token ?? $adAccount->accessToken?->access_token;
        if (!$token) {
            Log::error('No access token found for ad account: ' . $adAccount->id);
            return collect();
        }

        $this->initSdk($token);

        $metaCampaign = new MetaCampaign($campaign->external_campaign_id);

        $fields = [
            AdSetFields::ID,
            AdSetFields::NAME,
            AdSetFields::STATUS,
            AdSetFields::DAILY_BUDGET,
            AdSetFields::LIFETIME_BUDGET,
            AdSetFields::TARGETING,
        ];

        try {
            $adSets = $metaCampaign->getAdSets($fields);
            $syncedAdSets = collect();

            foreach ($adSets as $data) {
                $adSet = AdSet::updateOrCreate(
                    [
                        'organization_id' => $campaign->organization_id,
                        'campaign_id' => $campaign->id,
                        'external_adset_id' => $data->{AdSetFields::ID},
                    ],
                    [
                        'name' => $data->{AdSetFields::NAME},
                        'status' => $data->{AdSetFields::STATUS},
                        'daily_budget' => $data->{AdSetFields::DAILY_BUDGET} ? $data->{AdSetFields::DAILY_BUDGET} / 100 : null,
                        'lifetime_budget' => $data->{AdSetFields::LIFETIME_BUDGET} ? $data->{AdSetFields::LIFETIME_BUDGET} / 100 : null,
                        'targeting' => $data->{AdSetFields::TARGETING},
                    ]
                );
                $syncedAdSets->push($adSet);
            }

            return $syncedAdSets;
        } catch (\Exception $e) {
            Log::error('Meta sync ad sets failed: ' . $e->getMessage());
            return collect();
        }
    }

    public function syncAds(\App\Models\AdSet $adSet): Collection
    {
        $adAccount = $adSet->campaign->adAccount;
        $token = $adAccount->access_token ?? $adAccount->accessToken?->access_token;
        if (!$token) {
            Log::error('No access token found for ad account: ' . $adAccount->id);
            return collect();
        }

        $this->initSdk($token);

        $metaAdSet = new MetaAdSet($adSet->external_adset_id);

        $fields = [
            AdFields::ID,
            AdFields::NAME,
            AdFields::STATUS,
            AdFields::CREATIVE,
        ];

        try {
            $ads = $metaAdSet->getAds($fields);
            $syncedAds = collect();

            foreach ($ads as $data) {
                // Find local creative ID
                $creativeId = null;
                $externalCreativeId = $data->{AdFields::CREATIVE}['id'] ?? null;
                if ($externalCreativeId) {
                    $localCreative = AdCreative::where('external_creative_id', $externalCreativeId)->first();
                    $creativeId = $localCreative?->id;
                }

                $ad = Ad::updateOrCreate(
                    [
                        'organization_id' => $adSet->organization_id,
                        'ad_set_id' => $adSet->id,
                        'external_ad_id' => $data->{AdFields::ID},
                    ],
                    [
                        'ad_creative_id' => $creativeId,
                        'name' => $data->{AdFields::NAME},
                        'status' => $data->{AdFields::STATUS},
                        'external_creative_id' => $externalCreativeId,
                        'creative_data' => $data->{AdFields::CREATIVE},
                    ]
                );

                // Phase 12: Populate flat creatives table for performance analysis
                if ($localCreative) {
                    Creative::updateOrCreate(
                        [
                            'organization_id' => $adSet->organization_id,
                            'ad_id' => $ad->id,
                            'creative_id' => $localCreative->external_creative_id,
                        ],
                        [
                            'headline' => $localCreative->title,
                            'body' => $localCreative->body,
                            'image_url' => $localCreative->image_url,
                            'video_id' => $localCreative->video_id,
                            'cta' => $localCreative->call_to_action_type,
                        ]
                    );
                }

                $syncedAds->push($ad);
            }

            return $syncedAds;
        } catch (\Exception $e) {
            Log::error('Meta sync ads failed: ' . $e->getMessage());
            return collect();
        }
    }

    public function syncCreatives(\App\Models\AdAccount $adAccount): Collection
    {
        $this->initSdk($adAccount->access_token);
        $account = new MetaAdAccount('act_' . $adAccount->external_account_id);

        $fields = [
            AdCreativeFields::ID,
            AdCreativeFields::NAME,
            AdCreativeFields::TITLE,
            AdCreativeFields::BODY,
            AdCreativeFields::IMAGE_URL,
            AdCreativeFields::THUMBNAIL_URL,
            'video_id',
            AdCreativeFields::CALL_TO_ACTION_TYPE,
            AdCreativeFields::OBJECT_STORY_SPEC,
        ];

        try {
            $creatives = $account->getAdCreatives($fields);
            $syncedCreatives = collect();

            foreach ($creatives as $data) {
                $creative = AdCreative::updateOrCreate(
                    [
                        'organization_id' => $adAccount->organization_id,
                        'external_creative_id' => $data->{AdCreativeFields::ID},
                    ],
                    [
                        'ad_account_id' => $adAccount->id,
                        'name' => $data->{AdCreativeFields::NAME},
                        'title' => $data->{AdCreativeFields::TITLE},
                        'body' => $data->{AdCreativeFields::BODY},
                        'image_url' => $data->{AdCreativeFields::IMAGE_URL},
                        'thumbnail_url' => $data->{AdCreativeFields::THUMBNAIL_URL},
                        'video_id' => $data->video_id ?? null,
                        'call_to_action_type' => $data->{AdCreativeFields::CALL_TO_ACTION_TYPE} ?? null,
                        'object_story_spec' => $data->{AdCreativeFields::OBJECT_STORY_SPEC},
                        'asset_data' => $data->getData(),
                    ]
                );
                $syncedCreatives->push($creative);
            }

            return $syncedCreatives;
        } catch (\Exception $e) {
            Log::error('Meta sync creatives failed: ' . $e->getMessage());
            return collect();
        }
    }

    public function syncFullHierarchy(\App\Models\AdAccount $adAccount): void
    {
        Log::info('Starting full hierarchy sync for Meta account', ['ad_account_id' => $adAccount->id]);

        // 1. Sync Creatives first so ads can link to them
        $this->syncCreatives($adAccount);

        // 2. Sync Campaigns
        $campaigns = $this->syncCampaigns($adAccount);

        // 3. Sync AdSets and Ads
        foreach ($campaigns as $campaign) {
            $adSets = $this->syncAdSets($campaign);
            foreach ($adSets as $adSet) {
                $this->syncAds($adSet);
            }
        }

        Log::info('Completed full hierarchy sync for Meta account', ['ad_account_id' => $adAccount->id]);
    }

    public function syncBreakdowns(AdAccount $adAccount, string $startDate, string $endDate, string $level = 'campaign', array $breakdowns = []): Collection
    {
        $token = $adAccount->access_token ?? $adAccount->accessToken?->access_token;
        if (!$token) {
            Log::error('No access token found for ad account: ' . $adAccount->id);
            return collect();
        }

        $this->initSdk($token);
        $account = new MetaAdAccount('act_' . $adAccount->external_account_id);

        $params = [
            'time_range' => ['since' => $startDate, 'until' => $endDate],
            'time_increment' => 1,
            'level' => $level,
            'breakdowns' => $breakdowns,
        ];

        // Handle hourly stats specifically if requested
        if (in_array('hourly_stats', $breakdowns)) {
            unset($params['breakdowns']);
            $params['breakdowns'] = ['hourly_stats_aggregated_by_audience_time_zone'];
        }

        $fields = [
            AdsInsightsFields::IMPRESSIONS,
            AdsInsightsFields::REACH,
            AdsInsightsFields::CLICKS,
            AdsInsightsFields::SPEND,
            AdsInsightsFields::CONVERSIONS,
            AdsInsightsFields::DATE_START,
        ];

        if ($level === 'campaign')
            $fields[] = AdsInsightsFields::CAMPAIGN_ID;
        if ($level === 'adset') {
            $fields[] = AdsInsightsFields::CAMPAIGN_ID;
            $fields[] = AdsInsightsFields::ADSET_ID;
        }
        if ($level === 'ad') {
            $fields[] = AdsInsightsFields::CAMPAIGN_ID;
            $fields[] = AdsInsightsFields::ADSET_ID;
            $fields[] = AdsInsightsFields::AD_ID;
        }

        try {
            $insights = $account->getInsights($fields, $params);
            $syncedBreakdowns = collect();

            foreach ($insights as $insight) {
                $data = $insight->getData();

                // Map Breakdowns to Columns
                $mappedBreakdown = [
                    'age' => $data['age'] ?? null,
                    'gender' => $data['gender'] ?? null,
                    'country' => $data['country'] ?? null,
                    'city' => $data['region'] ?? $data['city'] ?? null,
                    'device' => $data['device_platform'] ?? null,
                    'placement' => $data['publisher_platform'] ?? null,
                    'hour' => $data['hourly_stats_aggregated_by_audience_time_zone'] ?? null,
                ];

                // If platform_position is present, append to placement
                if (isset($data['platform_position'])) {
                    $mappedBreakdown['placement'] .= ' - ' . $data['platform_position'];
                }

                // Determine Breakdown Type for index/querying
                $breakdownType = implode(',', $breakdowns);

                // Find local IDs
                $campaignId = null;
                $adSetId = null;
                $adId = null;

                if ($level !== 'account' && isset($insight->{AdsInsightsFields::CAMPAIGN_ID})) {
                    $campaign = Campaign::where('external_campaign_id', $insight->{AdsInsightsFields::CAMPAIGN_ID})->first();
                    $campaignId = $campaign?->id;
                }
                if (in_array($level, ['adset', 'ad']) && isset($insight->{AdsInsightsFields::ADSET_ID})) {
                    $adSet = AdSet::where('external_adset_id', $insight->{AdsInsightsFields::ADSET_ID})->first();
                    $adSetId = $adSet?->id;
                }
                if ($level === 'ad' && isset($insight->{AdsInsightsFields::AD_ID})) {
                    $ad = Ad::where('external_ad_id', $insight->{AdsInsightsFields::AD_ID})->first();
                    $adId = $ad?->id;
                }

                $record = \App\Models\AudienceInsight::updateOrCreate(
                    [
                        'ad_account_id' => $adAccount->id,
                        'campaign_id' => $campaignId,
                        'ad_set_id' => $adSetId,
                        'ad_id' => $adId,
                        'date' => $insight->{AdsInsightsFields::DATE_START},
                        'breakdown_type' => $breakdownType,
                        'age' => $mappedBreakdown['age'],
                        'gender' => $mappedBreakdown['gender'],
                        'country' => $mappedBreakdown['country'],
                        'city' => $mappedBreakdown['city'],
                        'device' => $mappedBreakdown['device'],
                        'placement' => $mappedBreakdown['placement'],
                        'hour' => $mappedBreakdown['hour'],
                    ],
                    [
                        'organization_id' => $adAccount->organization_id,
                        'spend' => $insight->{AdsInsightsFields::SPEND} ?? 0,
                        'impressions' => $insight->{AdsInsightsFields::IMPRESSIONS} ?? 0,
                        'reach' => $insight->{AdsInsightsFields::REACH} ?? 0,
                        'clicks' => $insight->{AdsInsightsFields::CLICKS} ?? 0,
                        'conversions' => isset($insight->{AdsInsightsFields::CONVERSIONS}) ? array_sum(array_column($insight->{AdsInsightsFields::CONVERSIONS}, 'value')) : 0,
                        'metadata' => $data,
                    ]
                );
                $syncedBreakdowns->push($record);
            }
            return $syncedBreakdowns;
        } catch (\Exception $e) {
            Log::error("Meta sync breakdowns (level: $level) failed: " . $e->getMessage());
            return collect();
        }
    }

    public function syncInsights(\App\Models\AdAccount $adAccount, string $startDate, string $endDate, string $level = 'account'): Collection
    {
        $this->initSdk($adAccount->access_token);
        $account = new MetaAdAccount('act_' . $adAccount->external_account_id);

        $params = [
            'time_range' => ['since' => $startDate, 'until' => $endDate],
            'time_increment' => 1,
            'level' => $level, // 'account', 'campaign', 'adset', 'ad'
        ];

        $fields = [
            AdsInsightsFields::IMPRESSIONS,
            AdsInsightsFields::REACH,
            AdsInsightsFields::CLICKS,
            AdsInsightsFields::SPEND,
            AdsInsightsFields::CTR,
            AdsInsightsFields::CPC,
            AdsInsightsFields::CPM,
            AdsInsightsFields::CONVERSIONS,
            AdsInsightsFields::ACTIONS,
            AdsInsightsFields::ACTION_VALUES,
            AdsInsightsFields::COST_PER_ACTION_TYPE,
            AdsInsightsFields::PURCHASE_ROAS,
            AdsInsightsFields::DATE_START,
        ];

        // Specific fields based on level to link accurately
        if ($level === 'campaign')
            $fields[] = AdsInsightsFields::CAMPAIGN_ID;
        if ($level === 'adset') {
            $fields[] = AdsInsightsFields::CAMPAIGN_ID;
            $fields[] = AdsInsightsFields::ADSET_ID;
        }
        if ($level === 'ad') {
            $fields[] = AdsInsightsFields::CAMPAIGN_ID;
            $fields[] = AdsInsightsFields::ADSET_ID;
            $fields[] = AdsInsightsFields::AD_ID;
        }

        try {
            $insights = $account->getInsights($fields, $params);
            $syncedInsights = collect();

            foreach ($insights as $insight) {
                // Find local IDs for linking
                $campaignId = null;
                $adSetId = null;
                $adId = null;

                if ($level !== 'account' && isset($insight->{AdsInsightsFields::CAMPAIGN_ID})) {
                    $campaign = Campaign::where('external_campaign_id', $insight->{AdsInsightsFields::CAMPAIGN_ID})->first();
                    $campaignId = $campaign?->id;
                }

                if (in_array($level, ['adset', 'ad']) && isset($insight->{AdsInsightsFields::ADSET_ID})) {
                    $adSet = AdSet::where('external_adset_id', $insight->{AdsInsightsFields::ADSET_ID})->first();
                    $adSetId = $adSet?->id;
                }

                if ($level === 'ad' && isset($insight->{AdsInsightsFields::AD_ID})) {
                    $ad = Ad::where('external_ad_id', $insight->{AdsInsightsFields::AD_ID})->first();
                    $adId = $ad?->id;
                }

                // Extract Revenue (Purchase Value)
                $revenue = 0;
                $actionValues = $insight->{AdsInsightsFields::ACTION_VALUES} ?? [];
                if (is_array($actionValues)) {
                    foreach ($actionValues as $action) {
                        if (in_array($action['action_type'], ['purchase', 'offsite_conversion.fb_pixel_purchase'])) {
                            $revenue += (float) $action['value'];
                        }
                    }
                }

                $mappedData = [
                    'organization_id' => $adAccount->organization_id,
                    'spend' => $insight->{AdsInsightsFields::SPEND} ?? 0,
                    'impressions' => $insight->{AdsInsightsFields::IMPRESSIONS} ?? 0,
                    'reach' => $insight->{AdsInsightsFields::REACH} ?? 0,
                    'clicks' => $insight->{AdsInsightsFields::CLICKS} ?? 0,
                    'ctr' => $insight->{AdsInsightsFields::CTR} ?? 0,
                    'cpc' => $insight->{AdsInsightsFields::CPC} ?? 0,
                    'cpm' => $insight->{AdsInsightsFields::CPM} ?? 0,
                    'conversions' => isset($insight->{AdsInsightsFields::CONVERSIONS}) ? array_sum(array_column($insight->{AdsInsightsFields::CONVERSIONS}, 'value')) : 0,
                    'revenue' => $revenue,
                    'roas' => isset($insight->{AdsInsightsFields::PURCHASE_ROAS}) ? $insight->{AdsInsightsFields::PURCHASE_ROAS}[0]['value'] : 0,
                    'metadata' => $insight->getData(),
                ];

                $insightRecord = AdInsight::updateOrCreate(
                    [
                        'ad_account_id' => $adAccount->id,
                        'campaign_id' => $campaignId,
                        'ad_set_id' => $adSetId,
                        'ad_id' => $adId,
                        'date' => $insight->{AdsInsightsFields::DATE_START},
                        'level' => $level,
                    ],
                    $mappedData
                );

                // Phase 13: Sync specific conversion events
                $this->syncConversionEventsFromInsight($adAccount, $insight, $campaignId, $adSetId, $adId);

                // Phase 14: Sync Funnel Metrics (only for campaign level as specified)
                if ($level === 'campaign' && $campaignId) {
                    $this->syncFunnelMetricsFromInsight($adAccount, $insight, $campaignId);
                }

                $syncedInsights->push($insightRecord);
            }

            return $syncedInsights;
        } catch (\Exception $e) {
            Log::error("Meta sync insights (level: $level) failed: " . $e->getMessage());
            return collect();
        }
    }

    protected function syncFunnelMetricsFromInsight(AdAccount $adAccount, $insight, $campaignId): void
    {
        $impressions = (int) ($insight->{AdsInsightsFields::IMPRESSIONS} ?? 0);
        $clicks = (int) ($insight->{AdsInsightsFields::CLICKS} ?? 0);
        $actions = $insight->{AdsInsightsFields::ACTIONS} ?? [];
        $date = $insight->{AdsInsightsFields::DATE_START};

        $landingViews = 0;
        $leads = 0;
        $sales = 0;

        foreach ($actions as $action) {
            $type = $action['action_type'];
            if ($type === 'landing_page_view')
                $landingViews += (int) $action['value'];
            if (str_contains($type, 'lead'))
                $leads += (int) $action['value'];
            if (str_contains($type, 'purchase'))
                $sales += (int) $action['value'];
        }

        // Calculate rates
        $ctr = $impressions > 0 ? ($clicks / $impressions) * 100 : 0;
        $lpcRate = $clicks > 0 ? ($landingViews / $clicks) * 100 : 0;
        $leadConvRate = $landingViews > 0 ? ($leads / $landingViews) * 100 : 0;
        $salesConvRate = $leads > 0 ? ($sales / $leads) * 100 : 0;

        FunnelMetric::updateOrCreate(
            [
                'organization_id' => $adAccount->organization_id,
                'campaign_id' => $campaignId,
                'date' => $date,
            ],
            [
                'impressions' => $impressions,
                'clicks' => $clicks,
                'landing_views' => $landingViews,
                'leads' => $leads,
                'sales' => $sales,
                'ctr' => $ctr,
                'lpc_rate' => $lpcRate,
                'lead_conv_rate' => $leadConvRate,
                'sales_conv_rate' => $salesConvRate,
            ]
        );
    }

    protected function syncConversionEventsFromInsight(AdAccount $adAccount, $insight, $campaignId, $adSetId, $adId): void
    {
        $actions = $insight->{AdsInsightsFields::ACTIONS} ?? [];
        $actionValues = $insight->{AdsInsightsFields::ACTION_VALUES} ?? [];
        $costPerAction = $insight->{AdsInsightsFields::COST_PER_ACTION_TYPE} ?? [];
        $date = $insight->{AdsInsightsFields::DATE_START};

        $importantEvents = [
            'lead' => 'lead',
            'purchase' => 'purchase',
            'add_to_cart' => 'add_to_cart',
            'initiate_checkout' => 'initiate_checkout',
            'complete_registration' => 'complete_registration',
            'subscribe' => 'subscribe',
            'landing_page_view' => 'landing_page_view',
        ];

        foreach ($importantEvents as $key => $type) {
            $count = 0;
            $revenue = 0;
            $cost = 0;

            // Find count
            foreach ($actions as $action) {
                if (str_contains($action['action_type'], $key)) {
                    $count += (int) $action['value'];
                }
            }

            if ($count === 0)
                continue;

            // Find revenue
            foreach ($actionValues as $val) {
                if (str_contains($val['action_type'], $key)) {
                    $revenue += (float) $val['value'];
                }
            }

            // Find cost
            foreach ($costPerAction as $cpa) {
                if (str_contains($cpa['action_type'], $key)) {
                    $cost = (float) $cpa['value'];
                }
            }

            ConversionEvent::updateOrCreate(
                [
                    'organization_id' => $adAccount->organization_id,
                    'date' => $date,
                    'campaign_id' => $campaignId,
                    'adset_id' => $adSetId,
                    'ad_id' => $adId,
                    'event_type' => $type,
                ],
                [
                    'count' => $count,
                    'revenue' => $revenue,
                    'cost_per_event' => $cost,
                ]
            );
        }
    }

    public function syncMetrics(\App\Models\Campaign $campaign, string $startDate, string $endDate): Collection
    {
        $adAccount = $campaign->adAccount;
        $this->initSdk($adAccount->access_token);

        $metaCampaign = new MetaCampaign($campaign->external_campaign_id);

        $params = [
            'time_range' => ['since' => $startDate, 'until' => $endDate],
            'time_increment' => 1,
        ];

        $fields = [
            AdsInsightsFields::IMPRESSIONS,
            AdsInsightsFields::CLICKS,
            AdsInsightsFields::SPEND,
            AdsInsightsFields::CONVERSIONS,
            AdsInsightsFields::PURCHASE_ROAS,
            AdsInsightsFields::DATE_START,
        ];

        try {
            $insights = $metaCampaign->getInsights($fields, $params);
            $syncedMetrics = collect();

            foreach ($insights as $insight) {
                $metric = DailyMetric::updateOrCreate(
                    [
                        'campaign_id' => $campaign->id,
                        'date' => $insight->{AdsInsightsFields::DATE_START},
                    ],
                    [
                        'impressions' => $insight->{AdsInsightsFields::IMPRESSIONS} ?? 0,
                        'clicks' => $insight->{AdsInsightsFields::CLICKS} ?? 0,
                        'spend' => $insight->{AdsInsightsFields::SPEND} ?? 0,
                        'conversions' => isset($insight->{AdsInsightsFields::CONVERSIONS}) ? array_sum(array_column($insight->{AdsInsightsFields::CONVERSIONS}, 'value')) : 0,
                        'additional_data' => [
                            'roas' => isset($insight->{AdsInsightsFields::PURCHASE_ROAS}) ? $insight->{AdsInsightsFields::PURCHASE_ROAS}[0]['value'] : null,
                        ]
                    ]
                );
                $syncedMetrics->push($metric);
            }

            return $syncedMetrics;
        } catch (\Exception $e) {
            Log::error('Meta sync metrics failed: ' . $e->getMessage());
            return collect();
        }
    }

    public function pauseCampaign(Campaign $campaign): bool
    {
        $this->initSdk($campaign->adAccount->access_token);
        try {
            $metaCampaign = new MetaCampaign($campaign->external_campaign_id);
            $metaCampaign->setData([CampaignFields::STATUS => 'PAUSED']);
            $metaCampaign->update();
            $campaign->update(['status' => 'INACTIVE']);
            return true;
        } catch (\Exception $e) {
            Log::error('Meta pause campaign failed: ' . $e->getMessage());
            return false;
        }
    }

    public function archiveCampaign(Campaign $campaign): bool
    {
        $this->initSdk($campaign->adAccount->access_token);
        try {
            $metaCampaign = new MetaCampaign($campaign->external_campaign_id);
            $metaCampaign->setData([CampaignFields::STATUS => 'ARCHIVED']);
            $metaCampaign->update();
            $campaign->update(['status' => 'ARCHIVED']);
            return true;
        } catch (\Exception $e) {
            Log::error('Meta archive campaign failed: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteCampaign(Campaign $campaign): bool
    {
        $this->initSdk($campaign->adAccount->access_token);
        try {
            $metaCampaign = new MetaCampaign($campaign->external_campaign_id);
            $metaCampaign->deleteSelf();
            $campaign->delete();
            return true;
        } catch (\Exception $e) {
            Log::error('Meta delete campaign failed: ' . $e->getMessage());
            return false;
        }
    }

    public function createCampaign(AdAccount $adAccount, array $data): Campaign
    {
        $this->initSdk($adAccount->access_token);

        $metaCampaign = new MetaCampaign(null, 'act_' . $adAccount->external_account_id);
        $metaCampaign->setData([
            CampaignFields::NAME => $data['name'],
            CampaignFields::OBJECTIVE => $data['objective'],
            CampaignFields::STATUS => 'PAUSED',
        ]);
        $metaCampaign->create();

        return Campaign::create([
            'organization_id' => $adAccount->organization_id,
            'client_id' => $data['client_id'],
            'ad_account_id' => $adAccount->id,
            'name' => $data['name'],
            'external_campaign_id' => $metaCampaign->id,
            'objective' => $data['objective'],
            'status' => 'INACTIVE',
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
        ]);
    }

    public function createAdSet(Campaign $campaign, array $data): AdSet
    {
        $this->initSdk($campaign->adAccount->access_token);

        $metaAdSet = new MetaAdSet(null, 'act_' . $campaign->adAccount->external_account_id);
        $metaAdSet->setData([
            AdSetFields::NAME => $data['name'],
            AdSetFields::CAMPAIGN_ID => $campaign->external_campaign_id,
            AdSetFields::BILLING_EVENT => 'IMPRESSIONS',
            AdSetFields::OPTIMIZATION_GOAL => 'REACH',
            AdSetFields::BID_AMOUNT => 1000,
            AdSetFields::DAILY_BUDGET => $data['daily_budget'] * 100, // Convert to cents
            AdSetFields::TARGETING => $data['targeting'],
            AdSetFields::STATUS => 'PAUSED',
        ]);
        $metaAdSet->create();

        return AdSet::create([
            'organization_id' => $campaign->organization_id,
            'campaign_id' => $campaign->id,
            'name' => $data['name'],
            'external_adset_id' => $metaAdSet->id,
            'status' => 'INACTIVE',
            'daily_budget' => $data['daily_budget'],
            'targeting' => json_encode($data['targeting']),
        ]);
    }

    public function createAd(AdSet $adSet, array $data): Ad
    {
        $this->initSdk($adSet->campaign->adAccount->access_token);

        // This is a simplified version; real ads need Creative objects
        $metaAd = new MetaAd(null, 'act_' . $adSet->campaign->adAccount->external_account_id);
        $metaAd->setData([
            AdFields::NAME => $data['name'],
            AdFields::ADSET_ID => $adSet->external_adset_id,
            AdFields::CREATIVE => ['creative_id' => $data['creative_id']],
            AdFields::STATUS => 'PAUSED',
        ]);
        $metaAd->create();

        return Ad::create([
            'organization_id' => $adSet->organization_id,
            'ad_set_id' => $adSet->id,
            'name' => $data['name'],
            'external_ad_id' => $metaAd->id,
            'status' => 'INACTIVE',
        ]);
    }

    public function syncLead(AdAccount $adAccount, string $leadId, string $formId = null, string $formName = null): ?FacebookLead
    {
        $this->initSdk($adAccount->facebook_page_token);

        $fields = [
            LeadFields::ID,
            LeadFields::AD_ID,
            LeadFields::ADSET_ID,
            LeadFields::CAMPAIGN_ID,
            LeadFields::FIELD_DATA,
            LeadFields::CREATED_TIME,
        ];

        try {
            $metaLead = new Lead($leadId);
            $metaLead->read($fields);

            $fieldData = $metaLead->{LeadFields::FIELD_DATA};
            $mapped = $this->mapLeadFieldData($fieldData);

            // Find local IDs
            $campaignId = null;
            $adSetId = null;
            $adId = null;

            if (isset($metaLead->{LeadFields::CAMPAIGN_ID})) {
                $campaign = Campaign::where('external_campaign_id', $metaLead->{LeadFields::CAMPAIGN_ID})->first();
                $campaignId = $campaign?->id;
            }

            if (isset($metaLead->{LeadFields::ADSET_ID})) {
                $adSet = AdSet::where('external_adset_id', $metaLead->{LeadFields::ADSET_ID})->first();
                $adSetId = $adSet?->id;
            }

            if (isset($metaLead->{LeadFields::AD_ID})) {
                $ad = Ad::where('external_ad_id', $metaLead->{LeadFields::AD_ID})->first();
                $adId = $ad?->id;
            }

            $lead = FacebookLead::updateOrCreate(
                [
                    'facebook_lead_id' => $metaLead->{LeadFields::ID},
                    'organization_id' => $adAccount->organization_id,
                ],
                [
                    'form_id' => $formId ?: ($mapped['form_id'] ?? 'Unknown'),
                    'form_name' => $formName ?: ($mapped['form_name'] ?? 'Webhook Lead'),
                    'campaign_id' => $campaignId,
                    'ad_set_id' => $adSetId,
                    'ad_id' => $adId,
                    'full_name' => $mapped['full_name'] ?? 'Unknown',
                    'email' => $mapped['email'] ?? 'Unknown',
                    'phone_number' => $mapped['phone_number'] ?? null,
                    'custom_questions' => $mapped['custom_questions'] ?? [],
                    'raw_data' => $metaLead->getData(),
                    'created_at' => Carbon::parse($metaLead->{LeadFields::CREATED_TIME}),
                ]
            );

            // Also sync to main CRM leads table
            \App\Models\Lead::updateOrCreate(
                [
                    'organization_id' => $adAccount->organization_id,
                    'email' => $mapped['email'],
                ],
                [
                    'name' => $mapped['full_name'] ?? 'Facebook Lead',
                    'phone' => $mapped['phone_number'],
                    'source' => 'Facebook Ads',
                    'status' => 'New',
                    'notes' => "Webhook lead captured" . ($formName ? " from $formName" : ""),
                ]
            );

            return $lead;
        } catch (\Exception $e) {
            Log::error("Meta sync individual lead $leadId failed: " . $e->getMessage());
            return null;
        }
    }

    public function syncAllLeads(AdAccount $adAccount): Collection
    {
        if (!$adAccount->facebook_page_id || !$adAccount->facebook_page_token) {
            Log::warning('Lead sync skipped: Ad account has no page ID or token', ['ad_account_id' => $adAccount->id]);
            return collect();
        }

        $this->initSdk($adAccount->facebook_page_token);
        $page = new Page($adAccount->facebook_page_id);

        try {
            $forms = $page->getLeadGenForms([LeadgenFormFields::ID, LeadgenFormFields::NAME]);
            $allSyncedLeads = collect();

            foreach ($forms as $form) {
                $leads = $this->syncLeadsFromForm($adAccount, $form->{LeadgenFormFields::ID}, $form->{LeadgenFormFields::NAME});
                $allSyncedLeads = $allSyncedLeads->merge($leads);
            }

            return $allSyncedLeads;
        } catch (\Exception $e) {
            Log::error('Meta sync all leads failed: ' . $e->getMessage());
            return collect();
        }
    }

    public function syncLeadsFromForm(AdAccount $adAccount, string $formId, string $formName): Collection
    {
        $this->initSdk($adAccount->facebook_page_token);
        $form = new LeadgenForm($formId);

        $fields = [
            LeadFields::ID,
            LeadFields::AD_ID,
            LeadFields::ADSET_ID,
            LeadFields::CAMPAIGN_ID,
            LeadFields::FIELD_DATA,
            LeadFields::CREATED_TIME,
        ];

        try {
            $leads = $form->getLeads($fields);
            $synced = collect();

            foreach ($leads as $metaLead) {
                $fieldData = $metaLead->{LeadFields::FIELD_DATA};
                $mapped = $this->mapLeadFieldData($fieldData);

                // Find local IDs
                $campaignId = null;
                $adSetId = null;
                $adId = null;

                if (isset($metaLead->{LeadFields::CAMPAIGN_ID})) {
                    $campaign = Campaign::where('external_campaign_id', $metaLead->{LeadFields::CAMPAIGN_ID})->first();
                    $campaignId = $campaign?->id;
                }

                if (isset($metaLead->{LeadFields::ADSET_ID})) {
                    $adSet = AdSet::where('external_adset_id', $metaLead->{LeadFields::ADSET_ID})->first();
                    $adSetId = $adSet?->id;
                }

                if (isset($metaLead->{LeadFields::AD_ID})) {
                    $ad = Ad::where('external_ad_id', $metaLead->{LeadFields::AD_ID})->first();
                    $adId = $ad?->id;
                }

                $lead = FacebookLead::updateOrCreate(
                    [
                        'facebook_lead_id' => $metaLead->{LeadFields::ID},
                        'organization_id' => $adAccount->organization_id,
                    ],
                    [
                        'form_id' => $formId,
                        'form_name' => $formName,
                        'campaign_id' => $campaignId,
                        'ad_set_id' => $adSetId,
                        'ad_id' => $adId,
                        'full_name' => $mapped['full_name'] ?? 'Unknown',
                        'email' => $mapped['email'] ?? 'Unknown',
                        'phone_number' => $mapped['phone_number'] ?? null,
                        'custom_questions' => $mapped['custom_questions'] ?? [],
                        'raw_data' => $metaLead->getData(),
                        'created_at' => Carbon::parse($metaLead->{LeadFields::CREATED_TIME}),
                    ]
                );

                // Also sync to main CRM leads table
                \App\Models\Lead::updateOrCreate(
                    [
                        'organization_id' => $adAccount->organization_id,
                        'email' => $mapped['email'],
                    ],
                    [
                        'name' => $mapped['full_name'] ?? 'Facebook Lead',
                        'phone' => $mapped['phone_number'],
                        'source' => 'Facebook Ads',
                        'status' => 'New',
                        'notes' => "Lead from Form: $formName (ID: $formId)",
                    ]
                );

                $synced->push($lead);
            }

            return $synced;
        } catch (\Exception $e) {
            Log::error("Meta sync leads for form $formId failed: " . $e->getMessage());
            return collect();
        }
    }

    protected function mapLeadFieldData(array $fieldData): array
    {
        $result = [
            'full_name' => null,
            'email' => null,
            'phone_number' => null,
            'custom_questions' => [],
        ];

        foreach ($fieldData as $field) {
            $name = $field['name'];
            $value = is_array($field['values']) ? ($field['values'][0] ?? null) : $field['values'];

            if (in_array($name, ['full_name', 'first_name', 'last_name', 'name'])) {
                if ($name === 'name' || $name === 'full_name') {
                    $result['full_name'] = $value;
                } else {
                    $result['full_name'] = ($result['full_name'] ? $result['full_name'] . ' ' : '') . $value;
                }
            } elseif (in_array($name, ['email'])) {
                $result['email'] = $value;
            } elseif (in_array($name, ['phone_number', 'phone'])) {
                $result['phone_number'] = $value;
            } else {
                $result['custom_questions'][$name] = $value;
            }
        }

        return $result;
    }

    public function getPages(string $accessToken): Collection
    {
        $this->initSdk($accessToken);
        try {
            // Fetch account's managed pages
            $response = Http::get("https://graph.facebook.com/{$this->apiVersion}/me/accounts", [
                'access_token' => $accessToken,
            ]);

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch Facebook pages: ' . $response->body());
            }

            return collect($response->json()['data']);
        } catch (\Exception $e) {
            Log::error('Meta get pages failed: ' . $e->getMessage());
            return collect();
        }
    }

    protected function normalizeStatus(string $status): string
    {
        return match ($status) {
            'ACTIVE' => 'ACTIVE',
            'PAUSED' => 'INACTIVE',
            'ARCHIVED' => 'ARCHIVED',
            'DELETED' => 'ARCHIVED',
            default => 'INACTIVE',
        };
    }
}
