<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntegrationsOAuthStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_oauth_redirect_sets_state_and_includes_it_in_redirect_url(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
        ]);
        if (! $admin instanceof \Illuminate\Contracts\Auth\Authenticatable) {
            $this->fail('Admin user is not authenticatable.');
        }

        $client = Client::create([
            'organization_id' => $admin->organization_id,
            'name' => 'Acme',
            'email' => 'acme@example.com',
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($admin)->get('/integrations/oauth/google_analytics?client_id=' . $client->id);

        $response->assertRedirect();

        $state = session('integrations.oauth.state.google_analytics');
        $this->assertIsString($state);
        $this->assertNotSame('', $state);

        $location = $response->headers->get('Location');
        $this->assertIsString($location);
        $this->assertStringContainsString('state=' . $state, $location);
    }

    public function test_oauth_callback_rejects_invalid_state(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
        ]);
        if (! $admin instanceof \Illuminate\Contracts\Auth\Authenticatable) {
            $this->fail('Admin user is not authenticatable.');
        }

        $client = Client::create([
            'organization_id' => $admin->organization_id,
            'name' => 'Acme',
            'email' => 'acme@example.com',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($admin)->withSession([
            'integrations.oauth.client_id.google_analytics' => $client->id,
            'integrations.oauth.state.google_analytics' => 'expected-state',
        ])->get('/integrations/oauth/google_analytics/callback?state=bad-state&code=abc')
            ->assertRedirect('/settings');
    }
}
