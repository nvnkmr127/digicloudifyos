<?php

return [
    'facebook' => [
        'client_id' => env('META_APP_ID'),
        'client_secret' => env('META_APP_SECRET'),
        'redirect_uri' => env('META_REDIRECT_URI'),
        'webhook_verify_token' => env('META_VERIFY_TOKEN'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
        'developer_token' => env('GOOGLE_ADS_DEVELOPER_TOKEN'),
    ],

    'linkedin' => [
        'client_id' => env('LINKEDIN_CLIENT_ID'),
        'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
        'redirect' => env('LINKEDIN_REDIRECT_URI'),
    ],

    'shopify' => [
        'client_id' => env('SHOPIFY_CLIENT_ID'),
        'client_secret' => env('SHOPIFY_CLIENT_SECRET'),
        'scopes' => env('SHOPIFY_SCOPES', 'read_orders,read_products'),
    ],

    'woocommerce' => [
        'default_currency' => env('WOOCOMMERCE_DEFAULT_CURRENCY', 'USD'),
    ],

    'twitter' => [
        'client_id' => env('TWITTER_CLIENT_ID'),
        'client_secret' => env('TWITTER_CLIENT_SECRET'),
    ],

    'amazon_sp_api' => [
        'lwa_client_id' => env('AMAZON_LWA_CLIENT_ID'),
        'lwa_client_secret' => env('AMAZON_LWA_CLIENT_SECRET'),
        'aws_region' => env('AMAZON_SPAPI_AWS_REGION', 'us-east-1'),
        'endpoint' => env('AMAZON_SPAPI_ENDPOINT', 'https://sellingpartnerapi-na.amazon.com'),
        'marketplace_id' => env('AMAZON_SPAPI_MARKETPLACE_ID', 'ATVPDKIKX0DER'),
    ],

    'social_listening' => [
        'webhook_secret' => env('SOCIAL_LISTENING_WEBHOOK_SECRET'),
    ],

    'pagespeed' => [
        'api_key' => env('PAGESPEED_API_KEY'),
    ],
];
