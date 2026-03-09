<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', \App\Livewire\Dashboard\Index::class)->name('dashboard');

// Marketing Agency OS Routes (HighLevel equivalent features)
Route::get('/pipelines', \App\Livewire\Pipelines\Index::class)->name('pipelines.index');
Route::get('/conversations', \App\Livewire\Conversations\Index::class)->name('conversations.index');
Route::get('/calendars', \App\Livewire\Calendars\Index::class)->name('calendars.index');
Route::get('/automations', \App\Livewire\Automations\Index::class)->name('automations.index');

// Full Agency Modules
Route::get('/proposals', \App\Livewire\Proposals\Index::class)->name('proposals.index');
Route::get('/time-tracking', \App\Livewire\TimeTracking\Index::class)->name('time-tracking.index');
Route::get('/analytics', \App\Livewire\Analytics\Index::class)->name('analytics.index');
Route::get('/forms', \App\Livewire\Forms\Index::class)->name('forms.index');
Route::get('/media', \App\Livewire\Media\Index::class)->name('media.index');
Route::get('/social-planner', \App\Livewire\SocialPlanner\Index::class)->name('social-planner.index');


Route::get('/clients', \App\Livewire\Clients\Index::class)->name('clients.index');
Route::get('/campaigns', \App\Livewire\Campaigns\Index::class)->name('campaigns.index');
Route::get('/projects', \App\Livewire\Projects\Index::class)->name('projects.index');
Route::get('/team', \App\Livewire\Team\Index::class)->name('team.index');
Route::get('/invoices', \App\Livewire\Invoices\Index::class)->name('invoices.index');

// Settings
Route::get('/settings', \App\Livewire\Settings\Index::class)->name('settings');

require __DIR__ . '/auth.php';
