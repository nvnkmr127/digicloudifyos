<?php

namespace App\Providers;

use App\Contracts\OrganizationContextInterface;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Proposal;
use App\Observers\ClientObserver;
use App\Observers\InvoiceObserver;
use App\Observers\LeadObserver;
use App\Observers\ProposalObserver;
use App\Repositories\CampaignRepository;
use App\Repositories\LeadRepository;
use App\Services\AnalyticsService;
use App\Services\AuthOrganizationContext;
use App\Services\CampaignService;
use App\Services\ExportService;
use App\Services\LeadService;
use App\Services\WebhookService;
use App\View\Composers\NavigationComposer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register service bindings
        $this->app->singleton(
            CampaignRepository::class,
            CampaignRepository::class
        );

        $this->app->singleton(
            CampaignService::class,
            CampaignService::class
        );

        $this->app->singleton(
            AnalyticsService::class,
            AnalyticsService::class
        );

        $this->app->singleton(
            WebhookService::class,
            WebhookService::class
        );

        $this->app->singleton(
            ExportService::class,
            ExportService::class
        );

        $this->app->singleton(
            LeadRepository::class,
            LeadRepository::class
        );

        $this->app->singleton(
            LeadService::class,
            LeadService::class
        );
        $this->app->singleton(
            OrganizationContextInterface::class,
            AuthOrganizationContext::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default string length for older MySQL versions
        Schema::defaultStringLength(191);

        // Configure model behavior
        Model::preventLazyLoading(! app()->isProduction());
        Model::preventSilentlyDiscardingAttributes(! app()->isProduction());

        // Register custom validation rules
        Validator::extend('organization_exists', function ($attribute, $value, $parameters, $validator) {
            $model = $parameters[0] ?? null;
            $field = $parameters[1] ?? 'id';

            if (! $model || ! $value) {
                return false;
            }

            $organizationId = request()->user()?->organization_id;
            if (! $organizationId) {
                return false;
            }

            return app($model)::where($field, $value)
                ->where('organization_id', $organizationId)
                ->exists();
        });

        // Register custom validation messages
        Validator::replacer('organization_exists', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $attribute, 'The selected :attribute is invalid or does not belong to your organization.');
        });
        Lead::observe(LeadObserver::class);
        Invoice::observe(InvoiceObserver::class);
        Proposal::observe(ProposalObserver::class);
        Client::observe(ClientObserver::class);

        // Sidebar Intelligence Badges
        View::composer(
            'components.layouts.sidebar-navigation',
            NavigationComposer::class
        );
    }
}
