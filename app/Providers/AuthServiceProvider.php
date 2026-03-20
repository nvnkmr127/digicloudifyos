<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\CreativeRequest;
use App\Models\Lead;
use App\Models\Task;
use App\Policies\CampaignPolicy;
use App\Policies\ClientPolicy;
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
        Client::class => ClientPolicy::class,
        CreativeRequest::class => CreativeRequestPolicy::class,
        \App\Models\Project::class => \App\Policies\ProjectPolicy::class,
        \App\Models\Pipeline::class => \App\Policies\PipelinePolicy::class,
        \App\Models\Opportunity::class => \App\Policies\OpportunityPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function ($user, $ability) {
            return $user->hasRole('OWNER') ? true : null;
        });

        Gate::define('manage-organization', function ($user) {
            return $user->hasRole(['OWNER', 'ADMIN']);
        });

        Gate::define('view-analytics', function ($user) {
            return $user->hasPermissionTo('view-analytics');
        });

        Gate::define('manage-workflow', function ($user) {
            return $user->hasPermissionTo('manage-workflow');
        });
    }
}
