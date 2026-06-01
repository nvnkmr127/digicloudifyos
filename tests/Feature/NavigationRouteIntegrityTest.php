<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class NavigationRouteIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_expected_named_routes_exist(): void
    {
        $expected = [
            'dashboard',
            'dashboards.index',
            'campaigns.index',
            'tasks.index',
            'leads.index',
            'contacts.index',
            'creatives.index',
            'clients.index',
            'workflow.index',
            'reports.index',
            'alerts.index',
            'projects.index',
            'playbooks.index',
            'service-packages.index',
            'deliverables.index',
            'pipelines.index',
            'conversations.index',
            'social-planner.index',
            'orders.index',
            'proposals.index',
            'invoices.index',
            'creative-requests.index',
            'feedback.index',
            'analytics.index',
            'seo.index',
            'site-health.index',
            'workload.index',
            'productivity.index',
            'automation.rules',
            'automation.approvals',
            'team.index',
            'users.index',
            'automations.index',
            'time-tracking.index',
            'time-tracking.approvals',
            'media.index',
            'calendars.index',
            'forms.index',
            'products.index',
            'settings',
            'webhooks.index',
            'webhooks.inbound',
            'webhooks.outbound',
            'webhooks.api',
            'webhooks.mappings.inbound',
            'webhooks.mappings.outbound',
            'broadcasts.index',
        ];

        foreach ($expected as $name) {
            $this->assertTrue(Route::has($name), "Missing named route: {$name}");
        }
    }
}
