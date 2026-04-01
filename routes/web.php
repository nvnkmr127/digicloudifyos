<?php

use App\Http\Controllers\ProfileController;
use App\Models\Client;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Bypass org global scope during model binding so policies/components can return 403 for cross-org access.
Route::bind('client', fn ($value) => Client::withoutGlobalScope('organization')->findOrFail($value));

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
    Route::get('/workflow', \App\Livewire\WorkflowMonitoring\Dashboard::class)->name('workflow.index');
    Route::get('/workflow/logs', \App\Livewire\WorkflowMonitoring\Logs::class)->name('workflow.logs');
    Route::get('/reports', \App\Livewire\Reports\Index::class)->name('reports.index');
    Route::get('/alerts', \App\Livewire\Alerts\Index::class)->name('alerts.index');
    Route::get('/clients', \App\Livewire\Clients\Index::class)
        ->middleware('can:manage-organization')
        ->name('clients.index');
    Route::get('/clients/performance', \App\Livewire\Clients\PerformanceDashboard::class)
        ->middleware('can:manage-organization')
        ->name('clients.performance');
    Route::get('/clients/create', \App\Livewire\Clients\Create::class)
        ->middleware('can:manage-organization')
        ->name('clients.create');
    Route::get('/clients/{client}/edit', \App\Livewire\Clients\Edit::class)
        ->middleware('can:manage-organization')
        ->name('clients.edit');
    Route::get('/clients/{client}/integrations', \App\Livewire\Clients\Integrations::class)
        ->middleware('can:manage-organization')
        ->name('clients.integrations');
    Route::get('/clients/{client}/privacy/export', [\App\Http\Controllers\Clients\PrivacyController::class, 'export'])
        ->middleware('can:manage-organization')
        ->name('clients.privacy.export');
    Route::post('/clients/{client}/privacy/erase', [\App\Http\Controllers\Clients\PrivacyController::class, 'erase'])
        ->middleware('can:manage-organization')
        ->name('clients.privacy.erase');

    // Operational Modules
    Route::get('/projects', \App\Livewire\Projects\Index::class)->name('projects.index');
    Route::get('/projects/create', \App\Livewire\Projects\Create::class)->name('projects.create');
    Route::get('/projects/{project}', \App\Livewire\Projects\DetailView::class)->name('projects.show');
    Route::get('/projects/{project}/edit', \App\Livewire\Projects\Edit::class)->name('projects.edit');
    Route::get('/pipelines', \App\Livewire\Pipelines\Index::class)->name('pipelines.index');
    Route::get('/opportunities/create', \App\Livewire\Opportunities\Create::class)->name('opportunities.create');
    Route::get('/team', \App\Livewire\Team\Index::class)->name('team.index');
    Route::get('/users', \App\Livewire\Users\Index::class)
        ->middleware('can:manage-organization')
        ->name('users.index');
    Route::get('/users/create', \App\Livewire\Users\Create::class)
        ->middleware('can:manage-organization')
        ->name('users.create');
    Route::get('/users/{user}/edit', \App\Livewire\Users\Edit::class)
        ->middleware('can:manage-organization')
        ->name('users.edit');

    // Communication & Marketing
    Route::get('/conversations', \App\Livewire\Conversations\Index::class)->name('conversations.index');
    Route::get('/social-planner', \App\Livewire\SocialPlanner\Index::class)->name('social-planner.index');

    // Finance & Sales
    Route::get('/orders', \App\Livewire\Orders\Index::class)->name('orders.index');
    Route::get('/orders/create', \App\Livewire\Orders\Create::class)->name('orders.create');
    Route::get('/orders/{id}', \App\Livewire\Orders\Show::class)->name('orders.show');
    Route::get('/proposals', \App\Livewire\Proposals\Index::class)->name('proposals.index');
    Route::get('/proposals/create', \App\Livewire\Proposals\Create::class)->name('proposals.create');
    Route::get('/analytics', \App\Livewire\Analytics\Index::class)->name('analytics.index');
    Route::get('/analytics-management', \App\Livewire\AnalyticsManagement\Dashboard::class)->name('analytics-management.dashboard');
    Route::get('/invoices', \App\Livewire\Invoices\Index::class)->name('invoices.index');
    Route::get('/invoices/create', \App\Livewire\Invoices\Create::class)->name('invoices.create');
    Route::get('/invoices/{invoice}', \App\Livewire\Invoices\DetailView::class)->name('invoices.show');
    Route::get('/invoices/{invoice}/edit', \App\Livewire\Invoices\Edit::class)->name('invoices.edit');

    // CRM
    Route::get('/contacts', \App\Livewire\Contacts\Index::class)->name('contacts.index');
    Route::get('/contacts/create', \App\Livewire\Contacts\Create::class)->name('contacts.create');
    Route::get('/contacts/{id}', \App\Livewire\Contacts\Show::class)->name('contacts.show');

    // Utilities
    Route::get('/automations', \App\Livewire\Automations\Index::class)->name('automations.index');
    Route::get('/automations/create', \App\Livewire\Automations\Builder::class)->name('automations.create');
    Route::get('/automations/{id}/edit', \App\Livewire\Automations\Builder::class)->name('automations.edit');
    Route::get('/time-tracking', \App\Livewire\TimeTracking\Index::class)->name('time-tracking.index');
    Route::get('/media', \App\Livewire\Media\Index::class)->name('media.index');
    Route::get('/calendars', \App\Livewire\Calendars\Index::class)->name('calendars.index');
    Route::get('/forms', \App\Livewire\Forms\Index::class)->name('forms.index');
    Route::get('/forms/create', \App\Livewire\Forms\Create::class)->name('forms.create');
    Route::get('/creative-requests', \App\Livewire\CreativeRequests\Index::class)->name('creative-requests.index');
    Route::get('/feedback', \App\Livewire\Feedback\Index::class)->name('feedback.index');
    Route::get('/products', \App\Livewire\Products\Index::class)->name('products.index');
    Route::get('/products/create', \App\Livewire\Products\Create::class)->name('products.create');
    Route::get('/settings', \App\Livewire\Settings\Index::class)
        ->middleware('can:manage-organization')
        ->name('settings');

    // Webhooks
    Route::get('/webhooks', \App\Livewire\Webhooks\Index::class)
        ->middleware('can:manage-organization')
        ->name('webhooks.index');
    Route::get('/webhooks/inbound', \App\Livewire\Webhooks\InboundIndex::class)
        ->middleware('can:manage-organization')
        ->name('webhooks.inbound');
    Route::get('/webhooks/outbound', \App\Livewire\Webhooks\OutboundIndex::class)
        ->middleware('can:manage-organization')
        ->name('webhooks.outbound');
    Route::get('/webhooks/api', \App\Livewire\Webhooks\ApiIndex::class)
        ->middleware('can:manage-organization')
        ->name('webhooks.api');
    Route::get('/webhooks/mappings/inbound', \App\Livewire\Webhooks\InboundMappings::class)
        ->middleware('can:manage-organization')
        ->name('webhooks.mappings.inbound');
    Route::get('/webhooks/mappings/outbound', \App\Livewire\Webhooks\OutboundMappings::class)
        ->middleware('can:manage-organization')
        ->name('webhooks.mappings.outbound');

    // Ads Integration
    Route::get('/ads/integration/{platform}', [App\Http\Controllers\AdsIntegrationController::class, 'redirect'])->name('ads.redirect');
    Route::get('/ads/callback/{platform}', [App\Http\Controllers\AdsIntegrationController::class, 'callback'])->name('ads.callback');
    Route::get('/auth/facebook/callback', [App\Http\Controllers\AdsIntegrationController::class, 'facebookCallback'])->name('auth.facebook.callback');
    Route::get('/ads', \App\Livewire\Ads\Index::class)->name('ads.index');
    Route::get('/ads/analytics', \App\Livewire\Ads\Analytics::class)->name('ads.analytics');
    Route::get('/ads/leads', \App\Livewire\Ads\Leads::class)->name('ads.leads');

    // Integrations (OAuth)
    Route::get('/integrations/oauth/{provider}', [\App\Http\Controllers\Integrations\OAuthController::class, 'redirect'])
        ->middleware('can:manage-organization')
        ->name('integrations.oauth.redirect');
    Route::get('/integrations/oauth/{provider}/callback', [\App\Http\Controllers\Integrations\OAuthController::class, 'callback'])
        ->middleware('can:manage-organization')
        ->name('integrations.oauth.callback');

    // Performance Intelligence
    Route::get('/intelligence', \App\Livewire\Intelligence\Overview::class)->name('intelligence.overview');
    Route::get('/intelligence/briefing', \App\Livewire\Intelligence\BriefingDashboard::class)->name('intelligence.briefing');
    Route::get('/intelligence/briefing/{id}', \App\Livewire\Intelligence\BriefingDashboard::class)->name('intelligence.briefing.show');
    Route::get('/intelligence/insights', \App\Livewire\Intelligence\InsightsFeed::class)->name('intelligence.insights');
    Route::get('/intelligence/alerts', \App\Livewire\Intelligence\AlertCenter::class)->name('intelligence.alerts');
    Route::get('/intelligence/client/{client}', \App\Livewire\Intelligence\ClientPerformanceCenter::class)->name('intelligence.client');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
