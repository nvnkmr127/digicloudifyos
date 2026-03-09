<?php

namespace App\Providers;

use App\Models\Campaign;
use App\Models\CreativeRequest;
use App\Models\Lead;
use App\Models\Task;
use App\Policies\CampaignPolicy;
use App\Policies\CreativeRequestPolicy;
use App\Policies\LeadPolicy;
use App\Policies\TaskPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Campaign::class => CampaignPolicy::class,
        Task::class => TaskPolicy::class,
        Lead::class => LeadPolicy::class,
        CreativeRequest::class => CreativeRequestPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });

        Gate::define('manage-organization', function ($user) {
            return $user->hasRole(['admin', 'super-admin']);
        });

        Gate::define('view-analytics', function ($user) {
            return $user->hasPermissionTo('view-analytics');
        });

        Gate::define('manage-workflow', function ($user) {
            return $user->hasPermissionTo('manage-workflow');
        });
    }
}
