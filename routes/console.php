<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// --- Scalable Data Pipeline (Phase 24) ---

// 1. Sync High-Level Metrics (Sparklines/Dashboard)
Schedule::command('ads:sync-metrics')->hourly();

// 2. Real-time Lead Intake
Schedule::command('ads:sync-leads')->everyFifteenMinutes();

// 3. Structural Discovery (New Campaigns/Ads)
Schedule::command('ads:sync-structure')->hourly();

// 4. Creative Catalog Sync
Schedule::command('ads:sync-creatives')->hourly();

// 5. Deep Performance Insights
Schedule::command('ads:sync-insights')->hourly();

// 6. Proactive Performance Watchdog (Phase 18)
Schedule::command('ads:evaluate-rules')->hourly();

// 7. Data Hygiene
Schedule::command('cleanup:old-data')->daily();
