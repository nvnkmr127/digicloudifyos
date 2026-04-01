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

// --- Performance Intelligence Pipeline ---
use App\Jobs\Intelligence\FetchClientPerformanceData;
use App\Jobs\Intelligence\RunAnomalyDetection;
use App\Jobs\Intelligence\GenerateAiInsights;
use App\Jobs\Intelligence\GenerateDailyBriefing;
use App\Jobs\Intelligence\SendDailyBriefingEmail;

Schedule::job(new FetchClientPerformanceData)->name('intelligence:fetch')->dailyAt('02:00')->withoutOverlapping();
Schedule::job(new RunAnomalyDetection)->name('intelligence:anomalies')->dailyAt('04:00')->withoutOverlapping();
Schedule::job(new GenerateAiInsights)->name('intelligence:ai-insights')->dailyAt('05:00')->withoutOverlapping();
Schedule::job(new GenerateDailyBriefing)->name('intelligence:briefing')->dailyAt('06:00')->withoutOverlapping();
Schedule::job(new SendDailyBriefingEmail)->name('intelligence:email')->dailyAt('07:00')->withoutOverlapping();
