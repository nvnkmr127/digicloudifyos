<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
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
            \App\Repositories\CampaignRepository::class,
            \App\Repositories\CampaignRepository::class
        );

        $this->app->singleton(
            \App\Services\CampaignService::class,
            \App\Services\CampaignService::class
        );

        $this->app->singleton(
            \App\Services\AnalyticsService::class,
            \App\Services\AnalyticsService::class
        );

        $this->app->singleton(
            \App\Services\WebhookService::class,
            \App\Services\WebhookService::class
        );

        $this->app->singleton(
            \App\Services\ExportService::class,
            \App\Services\ExportService::class
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
    }
}
