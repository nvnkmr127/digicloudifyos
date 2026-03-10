<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

use Illuminate\Support\Facades\Schedule;

Schedule::command('ads:sync-metrics')->hourly();
Schedule::command('ads:sync-leads')->everyFifteenMinutes();
Schedule::command('ads:sync-structure')->daily();
Schedule::command('ads:sync-insights')->dailyAt('02:00');
Schedule::command('ads:evaluate-rules')->daily();
