<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Organization;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ProposalsCrudAndShareTest extends TestCase
{
    use RefreshDatabase;

    public function test_proposal_show_and_signed_share_work(): void
    {
        $org = Organization::factory()->createOne();
        $user = User::factory()->createOne(['organization_id' => $org->id, 'email_verified_at' => now()]);

        $client = Client::create([
            'organization_id' => $org->id,
            'name' => 'Proposal Client',
            'status' => 'ACTIVE',
        ]);

        $proposal = Proposal::create([
            'organization_id' => $org->id,
            'client_id' => $client->id,
            'title' => 'Proposal A',
            'proposal_number' => 'PROP-001',
            'total_amount' => 123.45,
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->get("/proposals/{$proposal->id}")
            ->assertOk()
            ->assertSee('Proposal A');

        $signed = URL::signedRoute('proposals.share', ['proposal' => $proposal->id]);
        $this->get($signed)->assertOk()->assertSee('Proposal A');
    }
}
