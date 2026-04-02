<?php

return [
    'ai_provider' => env('AI_PROVIDER', 'gemini'),
    'gemini_api_key' => env('GEMINI_API_KEY'),
    'openai_api_key' => env('OPENAI_API_KEY'),
    'gemini_key' => env('GEMINI_API_KEY'),
    'openai_key' => env('OPENAI_API_KEY'),
    'thresholds' => [
        'ctr_drop' => env('PERF_CTR_DROP_THRESHOLD', 20),
        'cpc_spike' => env('PERF_CPC_SPIKE_THRESHOLD', 30),
        'roas_min' => env('PERF_ROAS_MIN_THRESHOLD', 1.5),
        'engagement_drop' => env('PERF_ENGAGEMENT_DROP_THRESHOLD', 25),
        'lead_drop' => 30,
        'budget_overrun' => 10,
        'budget_underpace' => 40,
    ],
    'briefing_email_enabled' => env('BRIEFING_EMAIL_ENABLED', true),
    'briefing_send_time' => env('BRIEFING_SEND_TIME', '07:00'),
];
