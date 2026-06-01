<?php

namespace Tests\Feature\Intelligence;

use App\Livewire\Intelligence\InsightsFeed;
use App\Models\AiInsight;
use App\Models\Client;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntelligenceTenancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_intelligence_client_workspace_route_is_accessible_for_same_org(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);

        $client = Client::create([
            'organization_id' => $org->id,
            'name' => 'Acme Co',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($user)
            ->get(route('intelligence.client.workspace', $client))
            ->assertOk();
    }

    public function test_intelligence_client_workspace_denies_other_org(): void
    {
        $orgA = Organization::factory()->create();
        $orgB = Organization::factory()->create();

        $user = User::factory()->create(['organization_id' => $orgA->id]);
        $client = Client::create([
            'organization_id' => $orgB->id,
            'name' => 'Other Org Client',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($user)
            ->get(route('intelligence.client.workspace', $client))
            ->assertForbidden();
    }

    public function test_cannot_complete_other_org_insight(): void
    {
        $orgA = Organization::factory()->create();
        $orgB = Organization::factory()->create();

        $user = User::factory()->create(['organization_id' => $orgA->id]);
        $clientB = Client::create([
            'organization_id' => $orgB->id,
            'name' => 'Other Org Client',
            'status' => 'ACTIVE',
        ]);

        $insight = AiInsight::create([
            'organization_id' => $orgB->id,
            'client_id' => $clientB->id,
            'insight_date' => today(),
            'priority' => 'high',
            'category' => 'ad_performance',
            'title' => 'Test Insight',
            'issue_description' => 'Test issue description',
            'recommended_action' => 'Test recommended action',
            'is_completed' => false,
            'is_dismissed' => false,
        ]);

        $this->actingAs($user);

        $component = new InsightsFeed;

        try {
            $component->complete($insight->id);
            $this->fail('Expected ModelNotFoundException when completing insight from another organization.');
        } catch (ModelNotFoundException $e) {
            $this->assertTrue(true);
        }

        $this->assertDatabaseHas('ai_insights', [
            'id' => $insight->id,
            'is_completed' => 0,
        ]);
    }

    public function test_cannot_dismiss_other_org_insight(): void
    {
        $orgA = Organization::factory()->create();
        $orgB = Organization::factory()->create();

        $user = User::factory()->create(['organization_id' => $orgA->id]);
        $clientB = Client::create([
            'organization_id' => $orgB->id,
            'name' => 'Other Org Client',
            'status' => 'ACTIVE',
        ]);

        $insight = AiInsight::create([
            'organization_id' => $orgB->id,
            'client_id' => $clientB->id,
            'insight_date' => today(),
            'priority' => 'high',
            'category' => 'ad_performance',
            'title' => 'Test Insight',
            'issue_description' => 'Test issue description',
            'recommended_action' => 'Test recommended action',
            'is_completed' => false,
            'is_dismissed' => false,
        ]);

        $this->actingAs($user);

        $component = new InsightsFeed;

        try {
            $component->dismiss($insight->id);
            $this->fail('Expected ModelNotFoundException when dismissing insight from another organization.');
        } catch (ModelNotFoundException $e) {
            $this->assertTrue(true);
        }

        $this->assertDatabaseHas('ai_insights', [
            'id' => $insight->id,
            'is_dismissed' => 0,
        ]);
    }
}
