<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_settings_or_client_management_routes(): void
    {
        $user = User::factory()->create([
            'role' => 'ANALYST',
        ]);

        $this->actingAs($user)->get('/settings')->assertForbidden();
        $this->actingAs($user)->get('/clients')->assertForbidden();
        $this->actingAs($user)->get('/clients/create')->assertForbidden();
    }

    public function test_admin_can_access_settings_and_clients_routes(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
        ]);

        $this->actingAs($admin)->get('/settings')->assertOk();
        $this->actingAs($admin)->get('/clients')->assertOk();
        $this->actingAs($admin)->get('/clients/create')->assertOk();
    }

    public function test_admin_cannot_edit_client_from_another_organization(): void
    {
        $adminOrg = Organization::factory()->create();
        $otherOrg = Organization::factory()->create();

        $admin = User::factory()->create([
            'organization_id' => $adminOrg->id,
            'role' => 'ADMIN',
        ]);

        $foreignClient = Client::create([
            'organization_id' => $otherOrg->id,
            'name' => 'Foreign Client',
            'email' => 'foreign@example.com',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($admin)
            ->get('/clients/' . $foreignClient->id . '/edit')
            ->assertForbidden();
    }
}
