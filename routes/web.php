<?php

use App\Http\Controllers\AdsIntegrationController;
use App\Http\Controllers\Clients\PrivacyController;
use App\Http\Controllers\Integrations\OAuthController;
use App\Http\Controllers\Portal\ClientPortalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Proposals\ShareController;
use App\Http\Controllers\PublicFormsController;
use App\Http\Controllers\Reports\PdfExportController;
use App\Http\Controllers\Webhooks\FacebookWebhookController;
use App\Livewire\Ads\Analytics;
use App\Livewire\Ads\Leads;
use App\Livewire\Automation\Approvals;
use App\Livewire\Automation\Rules;
use App\Livewire\Campaigns\AdCreationWizard;
use App\Livewire\Campaigns\CreateForm;
use App\Livewire\Campaigns\DetailView;
use App\Livewire\Campaigns\Edit;
use App\Livewire\Campaigns\KanbanBoard;
use App\Livewire\Clients\BrandKit;
use App\Livewire\Clients\Integrations;
use App\Livewire\Clients\OnboardingWizard;
use App\Livewire\Clients\PerformanceDashboard;
use App\Livewire\Creatives\RequestsBoard;
use App\Livewire\Dashboard\Index;
use App\Livewire\Dashboards\Builder;
use App\Livewire\Forms\Submissions;
use App\Livewire\Intelligence\AlertCenter;
use App\Livewire\Intelligence\BriefingDashboard;
use App\Livewire\Intelligence\ClientWorkspace;
use App\Livewire\Intelligence\InsightsFeed;
use App\Livewire\Intelligence\Overview;
use App\Livewire\Orders\Show;
use App\Livewire\Tasks\Create;
use App\Livewire\Webhooks\ApiIndex;
use App\Livewire\Webhooks\InboundIndex;
use App\Livewire\Webhooks\InboundMappings;
use App\Livewire\Webhooks\OutboundIndex;
use App\Livewire\Webhooks\OutboundMappings;
use App\Livewire\WorkflowMonitoring\Dashboard;
use App\Livewire\WorkflowMonitoring\Logs;
use App\Models\Client;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Bypass org global scope during model binding so policies/components can return 403 for cross-org access.
Route::bind('client', fn ($value) => Client::withoutGlobalScope('organization')->findOrFail($value));

// Facebook Webhooks
Route::get('webhooks/facebook', [FacebookWebhookController::class, 'verify']);
Route::post('webhooks/facebook', [FacebookWebhookController::class, 'handle']);

Route::middleware(['auth', 'verified', 'organization'])->group(function () {
    Route::get('/dashboard', Index::class)->name('dashboard');
    Route::get('/dashboards', App\Livewire\Dashboards\Index::class)->name('dashboards.index');
    Route::get('/dashboards/builder', Builder::class)
        ->middleware('can:view-analytics')
        ->name('dashboards.builder');

    // Core Modules
    Route::get('/campaigns', KanbanBoard::class)->name('campaigns.index');
    Route::get('/campaigns/create', CreateForm::class)->name('campaigns.create');
    Route::get('/campaigns/wizard', AdCreationWizard::class)->name('campaigns.wizard');
    Route::get('/campaigns/{campaign}', DetailView::class)->name('campaigns.show');
    Route::get('/campaigns/{campaign}/edit', Edit::class)->name('campaigns.edit');

    Route::get('/tasks', App\Livewire\Tasks\KanbanBoard::class)->name('tasks.index');
    Route::get('/tasks/create', Create::class)->name('tasks.create');
    Route::get('/tasks/{id}', App\Livewire\Tasks\DetailView::class)->name('tasks.show');
    Route::get('/tasks/{task}/edit', App\Livewire\Tasks\Edit::class)->name('tasks.edit');

    Route::get('/leads', App\Livewire\Leads\KanbanBoard::class)->name('leads.index');
    Route::get('/leads/create', App\Livewire\Leads\Create::class)->name('leads.create');
    Route::get('/leads/{id}', App\Livewire\Leads\DetailView::class)->name('leads.show');
    Route::get('/leads/{lead}/edit', App\Livewire\Leads\Edit::class)->name('leads.edit');

    // Additional Modules
    Route::get('/creatives', RequestsBoard::class)->name('creatives.index');
    Route::get('/creatives/{id}', RequestsBoard::class)->name('creatives.show');

    // Agency Management
    Route::get('/workflow', Dashboard::class)->name('workflow.index');
    Route::get('/workflow/logs', Logs::class)->name('workflow.logs');
    Route::get('/reports', App\Livewire\Reports\Index::class)->name('reports.index');
    Route::get('/reports/export/pdf', PdfExportController::class)->name('reports.export.pdf');
    Route::get('/deliverables', App\Livewire\Deliverables\Index::class)
        ->middleware('can:view-analytics')
        ->name('deliverables.index');
    Route::get('/alerts', App\Livewire\Alerts\Index::class)->name('alerts.index');
    Route::get('/clients', App\Livewire\Clients\Index::class)
        ->middleware('can:manage-organization')
        ->name('clients.index');
    Route::get('/clients/performance', PerformanceDashboard::class)
        ->middleware('can:manage-organization')
        ->name('clients.performance');
    Route::get('/clients/create', App\Livewire\Clients\Create::class)
        ->middleware('can:manage-organization')
        ->name('clients.create');
    Route::get('/clients/{client}/onboarding', OnboardingWizard::class)
        ->middleware('can:manage-organization')
        ->name('clients.onboarding');
    Route::get('/clients/{client}/edit', App\Livewire\Clients\Edit::class)
        ->middleware('can:manage-organization')
        ->name('clients.edit');
    Route::get('/clients/{client}/integrations', Integrations::class)
        ->middleware('can:manage-organization')
        ->name('clients.integrations');
    Route::get('/clients/{client}/brand-kit', BrandKit::class)
        ->middleware('can:manage-organization')
        ->name('clients.brand-kit');
    Route::get('/clients/{client}/privacy/export', [PrivacyController::class, 'export'])
        ->middleware('can:manage-organization')
        ->name('clients.privacy.export');
    Route::post('/clients/{client}/privacy/erase', [PrivacyController::class, 'erase'])
        ->middleware('can:manage-organization')
        ->name('clients.privacy.erase');

    // Operational Modules
    Route::get('/projects', App\Livewire\Projects\Index::class)->name('projects.index');
    Route::get('/projects/create', App\Livewire\Projects\Create::class)->name('projects.create');
    Route::get('/projects/{project}', App\Livewire\Projects\DetailView::class)->name('projects.show');
    Route::get('/projects/{project}/edit', App\Livewire\Projects\Edit::class)->name('projects.edit');
    Route::get('/pipelines', App\Livewire\Pipelines\Index::class)->name('pipelines.index');
    Route::get('/opportunities/create', App\Livewire\Opportunities\Create::class)->name('opportunities.create');
    Route::get('/opportunities/{id}', App\Livewire\Opportunities\Show::class)->name('opportunities.show');
    Route::get('/team', App\Livewire\Team\Index::class)->name('team.index');
    Route::get('/users', App\Livewire\Users\Index::class)
        ->middleware('can:manage-organization')
        ->name('users.index');
    Route::get('/users/create', App\Livewire\Users\Create::class)
        ->middleware('can:manage-organization')
        ->name('users.create');
    Route::get('/users/{user}/edit', App\Livewire\Users\Edit::class)
        ->middleware('can:manage-organization')
        ->name('users.edit');

    // Communication & Marketing
    Route::get('/conversations', App\Livewire\Conversations\Index::class)->name('conversations.index');
    Route::get('/social-planner', App\Livewire\SocialPlanner\Index::class)->name('social-planner.index');

    // Finance & Sales
    Route::get('/orders', App\Livewire\Orders\Index::class)->name('orders.index');
    Route::get('/orders/create', App\Livewire\Orders\Create::class)->name('orders.create');
    Route::get('/orders/{id}', Show::class)->name('orders.show');
    Route::get('/proposals', App\Livewire\Proposals\Index::class)->name('proposals.index');
    Route::get('/proposals/create', App\Livewire\Proposals\Create::class)->name('proposals.create');
    Route::get('/proposals/{proposal}', App\Livewire\Proposals\Show::class)->name('proposals.show');
    Route::get('/proposals/{proposal}/edit', App\Livewire\Proposals\Edit::class)->name('proposals.edit');
    Route::get('/analytics', App\Livewire\Analytics\Index::class)->name('analytics.index');
    Route::get('/analytics-management', App\Livewire\AnalyticsManagement\Dashboard::class)->name('analytics-management.dashboard');
    Route::get('/workload', App\Livewire\Workload\Index::class)
        ->middleware('can:view-analytics')
        ->name('workload.index');
    Route::get('/productivity', App\Livewire\Productivity\Index::class)
        ->middleware('can:view-analytics')
        ->name('productivity.index');
    Route::get('/seo', App\Livewire\Seo\Index::class)
        ->middleware('can:view-analytics')
        ->name('seo.index');
    Route::get('/site-health', App\Livewire\SiteHealth\Index::class)
        ->middleware('can:view-analytics')
        ->name('site-health.index');
    Route::get('/playbooks', App\Livewire\Playbooks\Index::class)
        ->middleware('can:view-analytics')
        ->name('playbooks.index');
    Route::get('/service-packages', App\Livewire\ServicePackages\Index::class)
        ->middleware('can:manage-organization')
        ->name('service-packages.index');
    Route::get('/invoices', App\Livewire\Invoices\Index::class)->name('invoices.index');
    Route::get('/invoices/create', App\Livewire\Invoices\Create::class)->name('invoices.create');
    Route::get('/invoices/{invoice}', App\Livewire\Invoices\DetailView::class)->name('invoices.show');
    Route::get('/invoices/{invoice}/edit', App\Livewire\Invoices\Edit::class)->name('invoices.edit');

    // CRM
    Route::get('/contacts', App\Livewire\Contacts\Index::class)->name('contacts.index');
    Route::get('/contacts/create', App\Livewire\Contacts\Create::class)->name('contacts.create');
    Route::get('/contacts/{id}', App\Livewire\Contacts\Show::class)->name('contacts.show');
    Route::get('/contacts/{id}/edit', App\Livewire\Contacts\Edit::class)->name('contacts.edit');

    // Utilities
    Route::get('/automations', App\Livewire\Automations\Index::class)->name('automations.index');
    Route::get('/automations/create', App\Livewire\Automations\Builder::class)->name('automations.create');
    Route::get('/automations/{id}/edit', App\Livewire\Automations\Builder::class)->name('automations.edit');
    Route::get('/automation/rules', Rules::class)
        ->middleware('can:manage-workflow')
        ->name('automation.rules');
    Route::get('/automation/approvals', Approvals::class)
        ->middleware('can:manage-workflow')
        ->name('automation.approvals');
    Route::get('/time-tracking', App\Livewire\TimeTracking\Index::class)->name('time-tracking.index');
    Route::get('/time-tracking/approvals', App\Livewire\TimeTracking\Approvals::class)
        ->middleware('can:manage-workflow')
        ->name('time-tracking.approvals');
    Route::get('/media', App\Livewire\Media\Index::class)->name('media.index');
    Route::get('/calendars', App\Livewire\Calendars\Index::class)->name('calendars.index');
    Route::get('/forms', App\Livewire\Forms\Index::class)->name('forms.index');
    Route::get('/forms/create', App\Livewire\Forms\Create::class)->name('forms.create');
    Route::get('/forms/{form}', App\Livewire\Forms\Show::class)->name('forms.show');
    Route::get('/forms/{form}/submissions', Submissions::class)->name('forms.submissions');
    Route::get('/broadcasts', App\Livewire\Broadcasts\Index::class)->name('broadcasts.index');
    Route::get('/creative-requests', App\Livewire\CreativeRequests\Index::class)->name('creative-requests.index');
    Route::get('/feedback', App\Livewire\Feedback\Index::class)->name('feedback.index');
    Route::get('/products', App\Livewire\Products\Index::class)->name('products.index');
    Route::get('/products/create', App\Livewire\Products\Create::class)->name('products.create');
    Route::get('/products/{product}/edit', App\Livewire\Products\Edit::class)->name('products.edit');
    Route::get('/settings', App\Livewire\Settings\Index::class)
        ->middleware('can:manage-organization')
        ->name('settings');

    // Webhooks
    Route::get('/webhooks', App\Livewire\Webhooks\Index::class)
        ->middleware('can:manage-organization')
        ->name('webhooks.index');
    Route::get('/webhooks/inbound', InboundIndex::class)
        ->middleware('can:manage-organization')
        ->name('webhooks.inbound');
    Route::get('/webhooks/outbound', OutboundIndex::class)
        ->middleware('can:manage-organization')
        ->name('webhooks.outbound');
    Route::get('/webhooks/api', ApiIndex::class)
        ->middleware('can:manage-organization')
        ->name('webhooks.api');
    Route::get('/webhooks/mappings/inbound', InboundMappings::class)
        ->middleware('can:manage-organization')
        ->name('webhooks.mappings.inbound');
    Route::get('/webhooks/mappings/outbound', OutboundMappings::class)
        ->middleware('can:manage-organization')
        ->name('webhooks.mappings.outbound');

    // Ads Integration
    Route::get('/ads/integration/{platform}', [AdsIntegrationController::class, 'redirect'])->name('ads.redirect');
    Route::get('/ads/callback/{platform}', [AdsIntegrationController::class, 'callback'])->name('ads.callback');
    Route::get('/auth/facebook/callback', [AdsIntegrationController::class, 'facebookCallback'])->name('auth.facebook.callback');
    Route::get('/ads', App\Livewire\Ads\Index::class)->name('ads.index');
    Route::get('/ads/analytics', Analytics::class)->name('ads.analytics');
    Route::get('/ads/leads', Leads::class)->name('ads.leads');

    // Integrations (OAuth)
    Route::get('/integrations/oauth/{provider}', [OAuthController::class, 'redirect'])
        ->middleware('can:manage-organization')
        ->name('integrations.oauth.redirect');
    Route::get('/integrations/oauth/{provider}/callback', [OAuthController::class, 'callback'])
        ->middleware('can:manage-organization')
        ->name('integrations.oauth.callback');

    // Performance Intelligence
    Route::get('/intelligence', Overview::class)->name('intelligence.overview');
    Route::get('/intelligence/briefing', BriefingDashboard::class)->name('intelligence.briefing');
    Route::get('/intelligence/briefing/{id}', BriefingDashboard::class)->name('intelligence.briefing.show');
    Route::get('/intelligence/insights', InsightsFeed::class)->name('intelligence.insights');
    Route::get('/intelligence/alerts', AlertCenter::class)->name('intelligence.alerts');
    Route::get('/intelligence/clients/{client}/workspace', ClientWorkspace::class)
        ->middleware('can:view,client')
        ->name('intelligence.client.workspace');

    // Legacy: keep old URL working (redirect to new canonical route)
    Route::get('/intelligence/client/{client}', function (Client $client) {
        return redirect()->route('intelligence.client.workspace', $client);
    })
        ->middleware('can:view,client')
        ->name('intelligence.client');

    // Notifications
    Route::get('/notifications', App\Livewire\Notifications\Index::class)->name('notifications.index');
});

Route::get('/portal/clients/{client}', [ClientPortalController::class, 'show'])
    ->middleware(['signed', 'throttle:60,1'])
    ->name('client.portal');

Route::get('/share/proposals/{proposal}', [ShareController::class, 'show'])
    ->middleware(['signed', 'throttle:60,1'])
    ->name('proposals.share');

Route::get('/f/{slug}', [PublicFormsController::class, 'show'])
    ->middleware(['throttle:60,1'])
    ->name('public.forms.show');
Route::post('/f/{slug}/submit', [PublicFormsController::class, 'submit'])
    ->middleware(['throttle:30,1'])
    ->name('public.forms.submit');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
