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
    ],

    'linkedin' => [
        'client_id' => env('LINKEDIN_CLIENT_ID'),
        'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
        'redirect' => env('LINKEDIN_REDIRECT_URI'),
    ],
];
