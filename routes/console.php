<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// --- Scalable Data Pipeline (Phase 24) ---

// 1. Sync High-Level Metrics (Sparklines/Dashboard)
Schedule::command('ads:sync-metrics')->hourly()->withoutOverlapping();

// 2. Real-time Lead Intake
Schedule::command('ads:sync-leads')->everyFifteenMinutes()->withoutOverlapping();

// 3. Structural Discovery (New Campaigns/Ads)
Schedule::command('ads:sync-structure')->hourly()->withoutOverlapping();

// 4. Creative Catalog Sync
Schedule::command('ads:sync-creatives')->hourly()->withoutOverlapping();

// 5. Deep Performance Insights
Schedule::command('ads:sync-insights')->hourly()->withoutOverlapping();

// 6. Proactive Performance Watchdog (Phase 18)
Schedule::command('ads:evaluate-rules')->hourly()->withoutOverlapping();

// 7. Data Hygiene
Schedule::command('cleanup:old-data')->daily()->withoutOverlapping();

// --- Performance Intelligence Pipeline ---
use App\Jobs\Automation\ApplyApprovedAutomationActions;
use App\Jobs\Automation\GenerateAutomationActions;
use App\Jobs\Competitive\ComputeCompetitiveSignals;
use App\Jobs\Competitive\RunDailyCompetitiveSync;
use App\Jobs\Compliance\IntegrationHealthCheck;
use App\Jobs\Compliance\PurgeRetentionData;
use App\Jobs\Deliverables\GenerateDailyDeliverables;
use App\Jobs\Integrations\RunDailyIntegrationSync;
use App\Jobs\Intelligence\ComputeCompetitiveBenchmarks;
use App\Jobs\Intelligence\DetectCreativeFatigue;
use App\Jobs\Intelligence\FetchClientPerformanceData;
use App\Jobs\Intelligence\GenerateAiInsights;
use App\Jobs\Intelligence\GenerateDailyBriefing;
use App\Jobs\Intelligence\RunAnomalyDetection;
use App\Jobs\Intelligence\SendDailyBriefingEmail;
use App\Jobs\Operations\BottleneckAlerts;
use App\Jobs\Operations\ComputeDailyProductivity;
use App\Jobs\Playbooks\RunOnboardingPlaybooks;
use App\Jobs\Playbooks\RunRecurringServicePackages;
use App\Jobs\Seo\RunDailySeoPipeline;
use App\Jobs\Seo\RunDailySiteAudits;
use App\Jobs\SiteHealth\CheckDomainExpiry;
use App\Jobs\SiteHealth\SyncPageSpeedDailyMetrics;

Schedule::job(new RunOnboardingPlaybooks)->name('playbooks:onboarding')->dailyAt('00:50')->withoutOverlapping();
Schedule::job(new RunRecurringServicePackages)->name('playbooks:packages')->dailyAt('00:55')->withoutOverlapping();
Schedule::job(new RunDailyIntegrationSync)->name('integrations:sync')->dailyAt('01:00')->withoutOverlapping();
Schedule::job(new RunDailyCompetitiveSync)->name('competitive:sync')->dailyAt('01:10')->withoutOverlapping();
Schedule::job(new RunDailySeoPipeline)->name('seo:daily')->dailyAt('01:20')->withoutOverlapping();
Schedule::job(new RunDailySiteAudits)->name('seo:site-audits')->dailyAt('01:30')->withoutOverlapping();
Schedule::job(new FetchClientPerformanceData)->name('intelligence:fetch')->dailyAt('02:00')->withoutOverlapping();
Schedule::job(new ComputeDailyProductivity)->name('ops:productivity')->dailyAt('02:15')->withoutOverlapping();
Schedule::job(new ComputeCompetitiveBenchmarks)->name('intelligence:benchmarks')->dailyAt('03:00')->withoutOverlapping();
Schedule::job(new ComputeCompetitiveSignals)->name('competitive:signals')->dailyAt('03:30')->withoutOverlapping();
Schedule::job(new RunAnomalyDetection)->name('intelligence:anomalies')->dailyAt('04:00')->withoutOverlapping();
Schedule::job(new DetectCreativeFatigue)->name('intelligence:fatigue')->dailyAt('04:05')->withoutOverlapping();
Schedule::job(new GenerateAutomationActions)->name('automation:generate')->dailyAt('04:10')->withoutOverlapping();
Schedule::job(new ApplyApprovedAutomationActions)->name('automation:apply')->everyThirtyMinutes()->withoutOverlapping();
Schedule::job(new GenerateAiInsights)->name('intelligence:ai-insights')->dailyAt('05:00')->withoutOverlapping();
Schedule::job(new GenerateDailyBriefing)->name('intelligence:briefing')->dailyAt('06:00')->withoutOverlapping();
Schedule::job(new SendDailyBriefingEmail)->name('intelligence:email')->dailyAt('07:00')->withoutOverlapping();
Schedule::job(new GenerateDailyDeliverables)->name('deliverables:generate')->dailyAt('07:05')->withoutOverlapping();
Schedule::job(new SyncPageSpeedDailyMetrics)->name('site:pagespeed')->dailyAt('07:10')->withoutOverlapping();
Schedule::job(new CheckDomainExpiry)->name('site:domain-expiry')->dailyAt('07:20')->withoutOverlapping();
Schedule::job(new PurgeRetentionData)->name('compliance:retention')->dailyAt('08:00')->withoutOverlapping();
Schedule::job(new IntegrationHealthCheck)->name('compliance:integration-health')->dailyAt('08:10')->withoutOverlapping();
Schedule::job(new BottleneckAlerts)->name('ops:bottlenecks')->dailyAt('08:20')->withoutOverlapping();
