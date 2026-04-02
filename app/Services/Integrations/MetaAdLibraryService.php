<?php

namespace App\Services\Integrations;

use Illuminate\Support\Facades\Http;

class MetaAdLibraryService
{
    public function getAppAccessToken(): string
    {
        $appId = config('services.facebook.client_id', '');
        $appSecret = config('services.facebook.client_secret', '');

        if (! $appId || ! $appSecret) {
            throw new \RuntimeException('Meta app credentials not configured.');
        }

        return $appId.'|'.$appSecret;
    }

    public function fetchAdsForPage(string $pageId, array $params = []): array
    {
        $token = $this->getAppAccessToken();

        $base = [
            'access_token' => $token,
            'search_page_ids' => $pageId,
            'ad_active_status' => 'ACTIVE',
            'ad_type' => 'ALL',
            'fields' => implode(',', [
                'id',
                'page_id',
                'page_name',
                'ad_creation_time',
                'ad_delivery_start_time',
                'ad_delivery_stop_time',
                'ad_snapshot_url',
                'publisher_platforms',
                'ad_creative_bodies',
                'ad_creative_link_titles',
                'ad_creative_link_descriptions',
                'ad_creative_link_captions',
            ]),
            'limit' => 100,
        ];

        $query = array_merge($base, $params);

        $resp = Http::timeout(20)->retry(2, 200)->get('https://graph.facebook.com/v25.0/ads_archive', $query);
        if ($resp->failed()) {
            throw new \RuntimeException('Meta Ad Library request failed: '.$resp->status());
        }

        $json = $resp->json();

        return is_array($json) ? $json : [];
    }
}
