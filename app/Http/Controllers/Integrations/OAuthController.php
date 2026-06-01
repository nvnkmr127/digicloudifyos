<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientChannelConnection;
use App\Models\IntegrationCredential;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OAuthController extends Controller
{
    public function redirect(Request $request, string $provider)
    {
        $clientId = (string) $request->query('client_id');
        if ($clientId !== '') {
            session(["integrations.oauth.client_id.{$provider}" => $clientId]);
        }

        $state = Str::random(64);
        session(["integrations.oauth.state.{$provider}" => $state]);

        return match ($provider) {
            'google_analytics' => redirect()->away($this->googleAuthUrl(
                $provider,
                [
                    'openid',
                    'email',
                    'profile',
                    'https://www.googleapis.com/auth/analytics.readonly',
                ],
                $state
            )),
            'google_search_console' => redirect()->away($this->googleAuthUrl(
                $provider,
                [
                    'openid',
                    'email',
                    'profile',
                    'https://www.googleapis.com/auth/webmasters.readonly',
                ],
                $state
            )),
            'google_merchant_center' => redirect()->away($this->googleAuthUrl(
                $provider,
                [
                    'openid',
                    'email',
                    'profile',
                    'https://www.googleapis.com/auth/content',
                ],
                $state
            )),
            'google_business_profile' => redirect()->away($this->googleAuthUrl(
                $provider,
                [
                    'openid',
                    'email',
                    'profile',
                    'https://www.googleapis.com/auth/business.manage',
                ],
                $state
            )),
            'shopify' => redirect()->away($this->shopifyAuthUrl($request, $provider, $state)),
            'meta_organic' => redirect()->away($this->metaAuthUrl($provider, $state)),
            'twitter' => redirect()->away($this->twitterAuthUrl($provider, $state)),
            'linkedin_organic' => redirect()->away($this->linkedInAuthUrl($provider, $state)),
            default => abort(404),
        };
    }

    public function callback(Request $request, string $provider)
    {
        $expectedState = session("integrations.oauth.state.{$provider}");
        $incomingState = $request->query('state');

        if (! is_string($expectedState) || ! is_string($incomingState) || ! hash_equals($expectedState, $incomingState)) {
            return redirect()->route('settings')->with('error', 'Invalid OAuth state. Please try connecting again.');
        }

        session()->forget("integrations.oauth.state.{$provider}");

        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }

        $orgId = $user->organization_id;
        $clientId = session("integrations.oauth.client_id.{$provider}");

        if (! is_string($clientId) || $clientId === '') {
            return redirect()->route('settings')->with('error', 'No client selected for this connection.');
        }

        $client = Client::where('organization_id', $orgId)->find($clientId);
        if (! $client) {
            return redirect()->route('settings')->with('error', 'Client not found.');
        }

        if ($request->query('error')) {
            return redirect()->route('clients.integrations', $client->id)
                ->with('error', 'Connection was cancelled.');
        }

        $code = $request->query('code');
        if (! is_string($code) || $code === '') {
            return redirect()->route('clients.integrations', $client->id)
                ->with('error', 'Missing authorization code.');
        }

        try {
            return match ($provider) {
                'google_analytics' => $this->handleGoogleCallback($client, $provider, $code),
                'google_search_console' => $this->handleGoogleCallback($client, $provider, $code),
                'google_merchant_center' => $this->handleGoogleCallback($client, $provider, $code),
                'google_business_profile' => $this->handleGoogleCallback($client, $provider, $code),
                'shopify' => $this->handleShopifyCallback($request, $client, $provider, $code),
                'meta_organic' => $this->handleMetaCallback($client, $provider, $code),
                'twitter' => $this->handleTwitterCallback($request, $client, $provider, $code),
                'linkedin_organic' => $this->handleLinkedInOrganicCallback($client, $provider, $code),
                default => abort(404),
            };
        } catch (\Throwable $e) {
            Log::error("OAuth callback failed for {$provider}: ".$e->getMessage());

            return redirect()->route('clients.integrations', $client->id)
                ->with('error', 'Failed to connect. Please try again.');
        }
    }

    protected function googleAuthUrl(string $provider, array $scopes, string $state): string
    {
        $redirectUri = route('integrations.oauth.callback', ['provider' => $provider]);

        $params = [
            'client_id' => config('services.google.client_id', ''),
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => implode(' ', $scopes),
            'access_type' => 'offline',
            'prompt' => 'consent',
            'include_granted_scopes' => 'true',
            'state' => $state,
        ];

        return 'https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query($params);
    }

    protected function handleGoogleCallback(Client $client, string $provider, string $code)
    {
        $redirectUri = route('integrations.oauth.callback', ['provider' => $provider]);

        $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('services.google.client_id', ''),
            'client_secret' => config('services.google.client_secret', ''),
            'redirect_uri' => $redirectUri,
            'code' => $code,
            'grant_type' => 'authorization_code',
        ]);

        if ($tokenResponse->failed()) {
            throw new \RuntimeException('Google token exchange failed: '.$tokenResponse->body());
        }

        $token = $tokenResponse->json();
        $accessToken = $token['access_token'] ?? null;
        $refreshToken = $token['refresh_token'] ?? null;

        if (! is_string($accessToken) || $accessToken === '') {
            throw new \RuntimeException('Google token exchange did not return access_token.');
        }

        $externalUserId = null;
        $label = null;
        $userInfo = Http::withToken($accessToken)->get('https://openidconnect.googleapis.com/v1/userinfo');
        if ($userInfo->successful()) {
            $userData = $userInfo->json();
            $externalUserId = isset($userData['sub']) ? (string) $userData['sub'] : null;
            $label = isset($userData['email']) ? (string) $userData['email'] : null;
        }
        $externalUserId = $externalUserId ?: $label;

        $credential = IntegrationCredential::updateOrCreate(
            [
                'organization_id' => $client->organization_id,
                'provider' => $provider,
                'external_user_id' => $externalUserId,
            ],
            [
                'credential_type' => 'oauth',
                'label' => $label,
                'access_token' => $accessToken,
                'refresh_token' => is_string($refreshToken) ? $refreshToken : null,
                'expires_at' => isset($token['expires_in']) ? now()->addSeconds((int) $token['expires_in']) : null,
                'scopes' => isset($token['scope']) ? explode(' ', (string) $token['scope']) : null,
                'last_verified_at' => now(),
            ]
        );

        $connectionType = match ($provider) {
            'google_analytics' => 'ga4',
            'google_search_console' => 'google_search_console',
            'google_merchant_center' => 'google_merchant_center',
            'google_business_profile' => 'google_business_profile',
            default => $provider,
        };
        $metadata = [];
        $accountId = null;
        $accountName = null;

        if ($provider === 'google_analytics') {
            $discovery = Http::withToken($accessToken)->get('https://analyticsadmin.googleapis.com/v1beta/accountSummaries');
            if ($discovery->successful()) {
                $summaries = $discovery->json()['accountSummaries'] ?? [];
                $firstProperty = $summaries[0]['propertySummaries'][0] ?? null;
                if (is_array($firstProperty)) {
                    $accountId = isset($firstProperty['property']) ? str_replace('properties/', '', (string) $firstProperty['property']) : null;
                    $accountName = $firstProperty['displayName'] ?? null;
                    $metadata['property'] = $firstProperty;
                }
                $metadata['account_summaries_count'] = is_array($summaries) ? count($summaries) : 0;
            }
        }

        if ($provider === 'google_search_console') {
            $discovery = Http::withToken($accessToken)->get('https://www.googleapis.com/webmasters/v3/sites');
            if ($discovery->successful()) {
                $sites = $discovery->json()['siteEntry'] ?? [];
                $first = $sites[0] ?? null;
                if (is_array($first)) {
                    $accountId = $first['siteUrl'] ?? null;
                    $accountName = $first['siteUrl'] ?? null;
                    $metadata['site'] = $first;
                }
                $metadata['sites_count'] = is_array($sites) ? count($sites) : 0;
            }
        }

        if ($provider === 'google_merchant_center') {
            $discovery = Http::withToken($accessToken)->get('https://shoppingcontent.googleapis.com/content/v2.1/accounts/authinfo');
            if ($discovery->successful()) {
                $ids = $discovery->json()['accountIdentifiers'] ?? [];
                $first = $ids[0] ?? null;
                if (is_array($first) && isset($first['merchantId'])) {
                    $accountId = (string) $first['merchantId'];
                    $accountName = 'Merchant '.$accountId;
                    $metadata['authinfo'] = $first;
                }
                $metadata['merchant_accounts_count'] = is_array($ids) ? count($ids) : 0;
            }
        }

        if ($provider === 'google_business_profile') {
            $accounts = Http::withToken($accessToken)->get('https://mybusinessaccountmanagement.googleapis.com/v1/accounts');
            if ($accounts->successful()) {
                $list = $accounts->json()['accounts'] ?? [];
                $firstAcc = $list[0] ?? null;
                $accountName = is_array($firstAcc) ? ($firstAcc['name'] ?? null) : null;
                $metadata['accounts_count'] = is_array($list) ? count($list) : 0;
                $metadata['account'] = $firstAcc;

                if (is_string($accountName) && $accountName !== '') {
                    $locations = Http::withToken($accessToken)->get('https://mybusinessbusinessinformation.googleapis.com/v1/'.$accountName.'/locations', [
                        'readMask' => 'name,title,storefrontAddress,websiteUri,phoneNumbers',
                        'pageSize' => 100,
                    ]);

                    if ($locations->successful()) {
                        $locs = $locations->json()['locations'] ?? [];
                        $firstLoc = $locs[0] ?? null;
                        if (is_array($firstLoc)) {
                            $accountId = isset($firstLoc['name']) ? (string) $firstLoc['name'] : null;
                            $accountName = isset($firstLoc['title']) ? (string) $firstLoc['title'] : $accountId;
                            $metadata['location'] = $firstLoc;
                            $metadata['locations_count'] = is_array($locs) ? count($locs) : 0;
                        }
                    }
                }
            }
        }

        ClientChannelConnection::updateOrCreate(
            [
                'organization_id' => $client->organization_id,
                'client_id' => $client->id,
                'channel_type' => $connectionType,
            ],
            [
                'integration_credential_id' => $credential->id,
                'account_id' => is_string($accountId) && $accountId !== '' ? $accountId : null,
                'account_name' => is_string($accountName) && $accountName !== '' ? $accountName : null,
                'is_active' => true,
                'connected_at' => now(),
                'last_sync_status' => 'connected',
                'metadata' => $metadata ?: null,
            ]
        );

        return redirect()->route('clients.integrations', $client->id)->with('success', 'Connected successfully.');
    }

    protected function shopifyAuthUrl(Request $request, string $provider, string $state): string
    {
        $shop = $request->query('shop');
        if (! is_string($shop) || $shop === '') {
            abort(422, 'Missing shop parameter.');
        }

        $shop = strtolower(trim($shop));
        if (! str_ends_with($shop, '.myshopify.com')) {
            $shop .= '.myshopify.com';
        }

        $shop = preg_replace('/[^a-z0-9\\.-]/', '', $shop);

        $redirectUri = route('integrations.oauth.callback', ['provider' => $provider]);
        $scopes = config('services.shopify.scopes', 'read_orders,read_products');

        return "https://{$shop}/admin/oauth/authorize?".http_build_query([
            'client_id' => config('services.shopify.client_id', ''),
            'scope' => $scopes,
            'redirect_uri' => $redirectUri,
            'state' => $state,
        ]);
    }

    protected function metaAuthUrl(string $provider, string $state): string
    {
        $redirectUri = route('integrations.oauth.callback', ['provider' => $provider]);
        $appId = config('services.facebook.client_id', '');

        return 'https://www.facebook.com/v25.0/dialog/oauth?'.http_build_query([
            'client_id' => $appId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => implode(',', [
                'email',
                'public_profile',
                'pages_show_list',
                'pages_read_engagement',
                'pages_read_user_content',
                'read_insights',
                'instagram_basic',
                'instagram_manage_insights',
            ]),
            'state' => $state,
        ]);
    }

    protected function twitterAuthUrl(string $provider, string $state): string
    {
        $redirectUri = route('integrations.oauth.callback', ['provider' => $provider]);
        $clientId = config('services.twitter.client_id', '');

        $verifier = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
        session(["integrations.oauth.pkce.{$provider}" => $verifier]);

        return 'https://twitter.com/i/oauth2/authorize?'.http_build_query([
            'response_type' => 'code',
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => 'tweet.read users.read offline.access',
            'state' => $state,
            'code_challenge' => $challenge,
            'code_challenge_method' => 'S256',
        ]);
    }

    protected function linkedInAuthUrl(string $provider, string $state): string
    {
        $redirectUri = route('integrations.oauth.callback', ['provider' => $provider]);
        $clientId = config('services.linkedin.client_id', '');

        return 'https://www.linkedin.com/oauth/v2/authorization?'.http_build_query([
            'response_type' => 'code',
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => 'r_liteprofile r_emailaddress r_organization_social rw_organization_admin',
            'state' => $state,
        ]);
    }

    protected function handleShopifyCallback(Request $request, Client $client, string $provider, string $code)
    {
        $shop = $request->query('shop');
        if (! is_string($shop) || $shop === '') {
            throw new \RuntimeException('Missing shop in callback.');
        }

        $shop = $this->validateShopifyShop($shop);

        $tokenResponse = Http::asForm()
            ->timeout(20)
            ->retry(2, 200)
            ->withOptions(['allow_redirects' => false])
            ->post("https://{$shop}/admin/oauth/access_token", [
                'client_id' => config('services.shopify.client_id', ''),
                'client_secret' => config('services.shopify.client_secret', ''),
                'code' => $code,
            ]);

        if ($tokenResponse->failed()) {
            throw new \RuntimeException('Shopify token exchange failed.');
        }

        $token = $tokenResponse->json();
        $accessToken = $token['access_token'] ?? null;

        if (! is_string($accessToken) || $accessToken === '') {
            throw new \RuntimeException('Shopify token exchange did not return access_token.');
        }

        $credential = IntegrationCredential::updateOrCreate(
            [
                'organization_id' => $client->organization_id,
                'provider' => $provider,
                'external_user_id' => $shop,
            ],
            [
                'credential_type' => 'oauth',
                'label' => $shop,
                'access_token' => $accessToken,
                'refresh_token' => null,
                'expires_at' => null,
                'payload' => [
                    'shop' => $shop,
                ],
                'last_verified_at' => now(),
            ]
        );

        ClientChannelConnection::updateOrCreate(
            [
                'organization_id' => $client->organization_id,
                'client_id' => $client->id,
                'channel_type' => 'shopify',
            ],
            [
                'integration_credential_id' => $credential->id,
                'account_id' => $shop,
                'account_name' => $shop,
                'is_active' => true,
                'connected_at' => now(),
                'last_sync_status' => 'connected',
                'metadata' => [
                    'shop' => $shop,
                ],
            ]
        );

        return redirect()->route('clients.integrations', $client->id)->with('success', 'Connected successfully.');
    }

    protected function validateShopifyShop(string $shop): string
    {
        $shop = mb_strtolower(trim($shop));

        if (str_contains($shop, '/') || str_contains($shop, '@') || str_contains($shop, ':')) {
            throw new \RuntimeException('Invalid shop domain.');
        }

        if (! preg_match('/\A[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.myshopify\.com\z/', $shop)) {
            throw new \RuntimeException('Invalid shop domain.');
        }

        return $shop;
    }

    protected function handleMetaCallback(Client $client, string $provider, string $code)
    {
        $redirectUri = route('integrations.oauth.callback', ['provider' => $provider]);
        $appId = config('services.facebook.client_id', '');
        $appSecret = config('services.facebook.client_secret', '');

        $tokenResponse = Http::get('https://graph.facebook.com/v25.0/oauth/access_token', [
            'client_id' => $appId,
            'client_secret' => $appSecret,
            'redirect_uri' => $redirectUri,
            'code' => $code,
        ]);

        if ($tokenResponse->failed()) {
            throw new \RuntimeException('Meta token exchange failed.');
        }

        $token = $tokenResponse->json();
        $accessToken = $token['access_token'] ?? null;

        if (! is_string($accessToken) || $accessToken === '') {
            throw new \RuntimeException('Meta token exchange did not return access_token.');
        }

        $longLived = Http::get('https://graph.facebook.com/v25.0/oauth/access_token', [
            'grant_type' => 'fb_exchange_token',
            'client_id' => $appId,
            'client_secret' => $appSecret,
            'fb_exchange_token' => $accessToken,
        ]);

        if ($longLived->successful()) {
            $token = $longLived->json();
            $accessToken = $token['access_token'] ?? $accessToken;
        }

        $me = Http::withToken($accessToken)->get('https://graph.facebook.com/v25.0/me', [
            'fields' => 'id,name,email',
        ]);

        $meData = $me->successful() ? $me->json() : [];
        $externalUserId = isset($meData['id']) ? (string) $meData['id'] : null;
        $label = isset($meData['email']) ? (string) $meData['email'] : (isset($meData['name']) ? (string) $meData['name'] : null);

        $credential = IntegrationCredential::updateOrCreate(
            [
                'organization_id' => $client->organization_id,
                'provider' => $provider,
                'external_user_id' => $externalUserId ?: $label,
            ],
            [
                'credential_type' => 'oauth',
                'label' => $label,
                'access_token' => $accessToken,
                'refresh_token' => null,
                'expires_at' => isset($token['expires_in']) ? now()->addSeconds((int) $token['expires_in']) : null,
                'payload' => [
                    'api_version' => 'v25.0',
                    'meta_user_id' => $externalUserId,
                ],
                'last_verified_at' => now(),
            ]
        );

        $pages = Http::withToken($accessToken)->get('https://graph.facebook.com/v25.0/me/accounts', [
            'fields' => 'id,name,instagram_business_account',
        ]);

        $page = $pages->successful() ? (($pages->json()['data'] ?? [])[0] ?? null) : null;
        $pageId = is_array($page) && isset($page['id']) ? (string) $page['id'] : null;
        $pageName = is_array($page) && isset($page['name']) ? (string) $page['name'] : null;

        if ($pageId) {
            ClientChannelConnection::updateOrCreate(
                [
                    'organization_id' => $client->organization_id,
                    'client_id' => $client->id,
                    'channel_type' => 'facebook_organic',
                ],
                [
                    'integration_credential_id' => $credential->id,
                    'account_id' => $pageId,
                    'account_name' => $pageName,
                    'is_active' => true,
                    'connected_at' => now(),
                    'last_sync_status' => 'connected',
                    'metadata' => [
                        'page' => $page,
                    ],
                ]
            );

            $ig = is_array($page) ? ($page['instagram_business_account'] ?? null) : null;
            $igId = is_array($ig) && isset($ig['id']) ? (string) $ig['id'] : null;

            if ($igId) {
                ClientChannelConnection::updateOrCreate(
                    [
                        'organization_id' => $client->organization_id,
                        'client_id' => $client->id,
                        'channel_type' => 'instagram',
                    ],
                    [
                        'integration_credential_id' => $credential->id,
                        'account_id' => $igId,
                        'account_name' => $pageName,
                        'is_active' => true,
                        'connected_at' => now(),
                        'last_sync_status' => 'connected',
                        'metadata' => [
                            'instagram_business_account_id' => $igId,
                            'page_id' => $pageId,
                        ],
                    ]
                );
            }
        }

        return redirect()->route('clients.integrations', $client->id)->with('success', 'Connected successfully.');
    }

    protected function handleTwitterCallback(Request $request, Client $client, string $provider, string $code)
    {
        $redirectUri = route('integrations.oauth.callback', ['provider' => $provider]);

        $verifier = session("integrations.oauth.pkce.{$provider}");
        session()->forget("integrations.oauth.pkce.{$provider}");
        if (! is_string($verifier) || $verifier === '') {
            throw new \RuntimeException('Missing PKCE verifier.');
        }

        $clientId = config('services.twitter.client_id', '');
        $clientSecret = config('services.twitter.client_secret', '');

        $tokenResponse = Http::asForm()
            ->withHeaders([
                'Authorization' => 'Basic '.base64_encode($clientId.':'.$clientSecret),
            ])
            ->post('https://api.twitter.com/2/oauth2/token', [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $redirectUri,
                'code_verifier' => $verifier,
            ]);

        if ($tokenResponse->failed()) {
            throw new \RuntimeException('Twitter token exchange failed.');
        }

        $token = $tokenResponse->json();
        $accessToken = $token['access_token'] ?? null;
        $refreshToken = $token['refresh_token'] ?? null;

        if (! is_string($accessToken) || $accessToken === '') {
            throw new \RuntimeException('Twitter token exchange did not return access_token.');
        }

        $me = Http::withToken($accessToken)->get('https://api.twitter.com/2/users/me', [
            'user.fields' => 'username,public_metrics',
        ]);

        if ($me->failed()) {
            throw new \RuntimeException('Twitter user lookup failed.');
        }

        $data = $me->json()['data'] ?? [];
        $twitterUserId = isset($data['id']) ? (string) $data['id'] : null;
        $username = isset($data['username']) ? (string) $data['username'] : null;

        $credential = IntegrationCredential::updateOrCreate(
            [
                'organization_id' => $client->organization_id,
                'provider' => $provider,
                'external_user_id' => $twitterUserId ?: $username,
            ],
            [
                'credential_type' => 'oauth',
                'label' => $username,
                'access_token' => $accessToken,
                'refresh_token' => is_string($refreshToken) ? $refreshToken : null,
                'expires_at' => isset($token['expires_in']) ? now()->addSeconds((int) $token['expires_in']) : null,
                'scopes' => isset($token['scope']) ? explode(' ', (string) $token['scope']) : null,
                'payload' => [
                    'twitter_user_id' => $twitterUserId,
                    'username' => $username,
                ],
                'last_verified_at' => now(),
            ]
        );

        ClientChannelConnection::updateOrCreate(
            [
                'organization_id' => $client->organization_id,
                'client_id' => $client->id,
                'channel_type' => 'twitter',
            ],
            [
                'integration_credential_id' => $credential->id,
                'account_id' => $twitterUserId,
                'account_name' => $username,
                'is_active' => true,
                'connected_at' => now(),
                'last_sync_status' => 'connected',
                'metadata' => [
                    'twitter_user_id' => $twitterUserId,
                    'username' => $username,
                ],
            ]
        );

        return redirect()->route('clients.integrations', $client->id)->with('success', 'Connected successfully.');
    }

    protected function handleLinkedInOrganicCallback(Client $client, string $provider, string $code)
    {
        $redirectUri = route('integrations.oauth.callback', ['provider' => $provider]);
        $clientId = config('services.linkedin.client_id', '');
        $clientSecret = config('services.linkedin.client_secret', '');

        $tokenResponse = Http::asForm()->post('https://www.linkedin.com/oauth/v2/accessToken', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);

        if ($tokenResponse->failed()) {
            throw new \RuntimeException('LinkedIn token exchange failed.');
        }

        $token = $tokenResponse->json();
        $accessToken = $token['access_token'] ?? null;

        if (! is_string($accessToken) || $accessToken === '') {
            throw new \RuntimeException('LinkedIn token exchange did not return access_token.');
        }

        $me = Http::withToken($accessToken)->get('https://api.linkedin.com/v2/me');
        $meData = $me->successful() ? $me->json() : [];
        $liId = isset($meData['id']) ? (string) $meData['id'] : null;

        $orgs = Http::withToken($accessToken)->get('https://api.linkedin.com/v2/organizationalEntityAcls', [
            'q' => 'roleAssignee',
            'role' => 'ADMINISTRATOR',
            'state' => 'APPROVED',
        ]);

        $elements = $orgs->successful() ? ($orgs->json()['elements'] ?? []) : [];
        $first = $elements[0] ?? null;
        $orgUrn = is_array($first) ? ($first['organizationalTarget'] ?? null) : null;
        $orgUrn = is_string($orgUrn) ? $orgUrn : null;

        $credential = IntegrationCredential::updateOrCreate(
            [
                'organization_id' => $client->organization_id,
                'provider' => $provider,
                'external_user_id' => $liId ?: 'linkedin',
            ],
            [
                'credential_type' => 'oauth',
                'label' => $liId,
                'access_token' => $accessToken,
                'refresh_token' => null,
                'expires_at' => isset($token['expires_in']) ? now()->addSeconds((int) $token['expires_in']) : null,
                'scopes' => isset($token['scope']) ? explode(' ', (string) $token['scope']) : null,
                'payload' => [
                    'linkedin_user_id' => $liId,
                    'organization_urn' => $orgUrn,
                ],
                'last_verified_at' => now(),
            ]
        );

        ClientChannelConnection::updateOrCreate(
            [
                'organization_id' => $client->organization_id,
                'client_id' => $client->id,
                'channel_type' => 'linkedin_organic',
            ],
            [
                'integration_credential_id' => $credential->id,
                'account_id' => $orgUrn,
                'account_name' => $orgUrn,
                'is_active' => true,
                'connected_at' => now(),
                'last_sync_status' => 'connected',
                'metadata' => [
                    'organization_urn' => $orgUrn,
                ],
            ]
        );

        return redirect()->route('clients.integrations', $client->id)->with('success', 'Connected successfully.');
    }
}
