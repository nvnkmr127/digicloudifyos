<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TwitterOAuthPkceStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_twitter_oauth_redirect_sets_state_and_pkce_verifier(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
        ]);

        if (! $admin instanceof Authenticatable) {
            $this->fail('Admin user is not authenticatable.');
        }

        $client = Client::create([
            'organization_id' => $admin->organization_id,
            'name' => 'Acme',
            'email' => 'acme@example.com',
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($admin)->get('/integrations/oauth/twitter?client_id='.$client->id);
        $response->assertRedirect();

        $state = session('integrations.oauth.state.twitter');
        $verifier = session('integrations.oauth.pkce.twitter');

        $this->assertIsString($state);
        $this->assertNotSame('', $state);
        $this->assertIsString($verifier);
        $this->assertNotSame('', $verifier);
    }
}
