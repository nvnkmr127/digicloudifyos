<?php

namespace Tests\Feature\Intelligence;

use App\Models\Client;
use App\Models\Organization;
use App\Models\User;
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
}
