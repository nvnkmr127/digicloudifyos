<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DebugTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_debug(): void
    {
        // Create user
        $user = User::factory()->create(['email' => 'debugtest@example.com']);

        // Debug user
        $this->assertEquals('debugtest@example.com', $user->email);
        $this->assertNotNull($user->password);

        // Check password
        $check = Hash::check('password', $user->password);
        $this->assertTrue($check, 'Password hash should be valid');

        // Try direct auth
        $authAttempt = Auth::attempt(['email' => 'debugtest@example.com', 'password' => 'password']);
        $this->assertTrue($authAttempt, 'Auth attempt should succeed');

        // Check if authenticated
        $this->assertTrue(Auth::check(), 'Should be authenticated after attempt');

        // Clear auth for fresh test
        Auth::logout();

        // Now try POST request with CSRF token
        $response = $this->withSession(['_token' => 'test'])
            ->post('/login', [
                'email' => 'debugtest@example.com',
                'password' => 'password',
                '_token' => 'test',
            ]);

        $response->assertRedirect('/dashboard');

        // Check authentication using the test helper
        $this->assertAuthenticated();
    }
}
