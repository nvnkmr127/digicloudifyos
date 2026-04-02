<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MerchantCenterOAuthStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_merchant_center_oauth_redirect_sets_state_and_includes_it_in_redirect_url(): void
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

        $response = $this->actingAs($admin)->get('/integrations/oauth/google_merchant_center?client_id='.$client->id);
        $response->assertRedirect();

        $state = session('integrations.oauth.state.google_merchant_center');
        $this->assertIsString($state);
        $this->assertNotSame('', $state);

        $location = $response->headers->get('Location');
        $this->assertIsString($location);
        $this->assertStringContainsString('state='.$state, $location);
    }
}
