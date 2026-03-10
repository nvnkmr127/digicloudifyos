<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Facebook Webhooks
Route::get('webhooks/facebook', [App\Http\Controllers\Webhooks\FacebookWebhookController::class, 'verify']);
Route::post('webhooks/facebook', [App\Http\Controllers\Webhooks\FacebookWebhookController::class, 'handle']);

Route::middleware(['auth', 'verified', 'organization'])->group(function () {
    Route::get('/dashboard', \App\Livewire\Dashboard\Index::class)->name('dashboard');

    // Core Modules
    Route::get('/campaigns', \App\Livewire\Campaigns\KanbanBoard::class)->name('campaigns.index');
    Route::get('/campaigns/create', \App\Livewire\Campaigns\CreateForm::class)->name('campaigns.create');
    Route::get('/campaigns/wizard', \App\Livewire\Campaigns\AdCreationWizard::class)->name('campaigns.wizard');
    Route::get('/campaigns/{campaign}', \App\Livewire\Campaigns\DetailView::class)->name('campaigns.show');
    Route::get('/campaigns/{campaign}/edit', \App\Livewire\Campaigns\Edit::class)->name('campaigns.edit');

    Route::get('/tasks', \App\Livewire\Tasks\KanbanBoard::class)->name('tasks.index');
    Route::get('/tasks/create', \App\Livewire\Tasks\Create::class)->name('tasks.create');
    Route::get('/tasks/{id}', \App\Livewire\Tasks\DetailView::class)->name('tasks.show');
    Route::get('/tasks/{task}/edit', \App\Livewire\Tasks\Edit::class)->name('tasks.edit');

    Route::get('/leads', \App\Livewire\Leads\KanbanBoard::class)->name('leads.index');
    Route::get('/leads/create', \App\Livewire\Leads\Create::class)->name('leads.create');
    Route::get('/leads/{id}', \App\Livewire\Leads\DetailView::class)->name('leads.show');
    Route::get('/leads/{lead}/edit', \App\Livewire\Leads\Edit::class)->name('leads.edit');

    // Additional Modules
    Route::get('/creatives', \App\Livewire\Creatives\RequestsBoard::class)->name('creatives.index');
    Route::get('/creatives/{id}', \App\Livewire\Creatives\RequestsBoard::class)->name('creatives.show');

    // Agency Management
    Route::get('/workflow-monitoring', \App\Livewire\WorkflowMonitoring\Dashboard::class)->name('workflow.index');
    Route::get('/reports', \App\Livewire\Reports\Dashboard::class)->name('reports.index');
    Route::get('/alerts', \App\Livewire\Alerts\Index::class)->name('alerts.index');
    Route::get('/clients', \App\Livewire\Clients\Index::class)->name('clients.index');
    Route::get('/clients/create', \App\Livewire\Clients\Create::class)->name('clients.create');
    Route::get('/clients/{client}/edit', \App\Livewire\Clients\Edit::class)->name('clients.edit');

    // Operational Modules
    Route::get('/projects', \App\Livewire\Projects\Index::class)->name('projects.index');
    Route::get('/projects/create', \App\Livewire\Projects\Create::class)->name('projects.create');
    Route::get('/projects/{project}', \App\Livewire\Projects\DetailView::class)->name('projects.show');
    Route::get('/projects/{project}/edit', \App\Livewire\Projects\Edit::class)->name('projects.edit');
    Route::get('/pipelines', \App\Livewire\Pipelines\Index::class)->name('pipelines.index');
    Route::get('/team', \App\Livewire\Team\Index::class)->name('team.index');
    Route::get('/users', \App\Livewire\Users\Index::class)->name('users.index');
    Route::get('/users/create', \App\Livewire\Users\Create::class)->name('users.create');
    Route::get('/users/{user}/edit', \App\Livewire\Users\Edit::class)->name('users.edit');

    // Communication & Marketing
    Route::get('/conversations', \App\Livewire\Conversations\Index::class)->name('conversations.index');
    Route::get('/social-planner', \App\Livewire\SocialPlanner\Index::class)->name('social-planner.index');

    // Finance & Sales
    Route::get('/orders', \App\Livewire\Orders\Index::class)->name('orders.index');
    Route::get('/proposals', \App\Livewire\Proposals\Index::class)->name('proposals.index');
    Route::get('/analytics', \App\Livewire\Analytics\Index::class)->name('analytics.index');
    Route::get('/invoices', \App\Livewire\Invoices\Index::class)->name('invoices.index');
    Route::get('/invoices/create', \App\Livewire\Invoices\Create::class)->name('invoices.create');
    Route::get('/invoices/{invoice}', \App\Livewire\Invoices\DetailView::class)->name('invoices.show');
    Route::get('/invoices/{invoice}/edit', \App\Livewire\Invoices\Edit::class)->name('invoices.edit');

    // Utilities
    Route::get('/automations', \App\Livewire\Automations\Index::class)->name('automations.index');
    Route::get('/time-tracking', \App\Livewire\TimeTracking\Index::class)->name('time-tracking.index');
    Route::get('/media', \App\Livewire\Media\Index::class)->name('media.index');
    Route::get('/calendars', \App\Livewire\Calendars\Index::class)->name('calendars.index');
    Route::get('/forms', \App\Livewire\Forms\Index::class)->name('forms.index');
    Route::get('/products', \App\Livewire\Products\Index::class)->name('products.index');
    Route::get('/products/create', \App\Livewire\Products\Create::class)->name('products.create');
    Route::get('/settings', \App\Livewire\Settings\Index::class)->name('settings');

    // Ads Integration
    Route::get('/ads/integration/{platform}', [App\Http\Controllers\AdsIntegrationController::class, 'redirect'])->name('ads.redirect');
    Route::get('/ads/callback/{platform}', [App\Http\Controllers\AdsIntegrationController::class, 'callback'])->name('ads.callback');
    Route::get('/auth/facebook/callback', [App\Http\Controllers\AdsIntegrationController::class, 'facebookCallback'])->name('auth.facebook.callback');
    Route::get('/ads', [App\Livewire\Ads\Index::class, 'render'])->name('ads.index');
    Route::get('/ads/analytics', \App\Livewire\Ads\Analytics::class)->name('ads.analytics');
    Route::get('/ads/leads', \App\Livewire\Ads\Leads::class)->name('ads.leads');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
