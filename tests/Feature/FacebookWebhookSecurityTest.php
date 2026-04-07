<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacebookWebhookSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_verify_fails_closed_when_token_not_configured(): void
    {
        config(['services.facebook.webhook_verify_token' => null]);

        $this->get('/webhooks/facebook?hub_mode=subscribe&hub_verify_token=anything&hub_challenge=123')
            ->assertStatus(503);
    }

    public function test_verify_succeeds_with_correct_token(): void
    {
        config(['services.facebook.webhook_verify_token' => 'token123']);

        $this->get('/webhooks/facebook?hub_mode=subscribe&hub_verify_token=token123&hub_challenge=123')
            ->assertStatus(200)
            ->assertSee('123');
    }

    public function test_signature_mismatch_is_rejected(): void
    {
        config(['services.facebook.client_secret' => 'secret']);

        $payload = json_encode(['object' => 'page', 'entry' => []]);

        $this->withHeader('X-Hub-Signature-256', 'sha256=deadbeef')
            ->post('/webhooks/facebook', [], [], [], [], [], $payload)
            ->assertStatus(401);
    }
}

