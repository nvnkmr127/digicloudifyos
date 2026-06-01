<?php

namespace Tests\Feature;

use App\Livewire\CreativeRequests\Index;
use App\Models\AdAccount;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreativeRequestsCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_creative_request_creation_matches_schema(): void
    {
        $org = Organization::factory()->createOne();
        $user = User::factory()->createOne(['organization_id' => $org->id]);

        $client = Client::create([
            'organization_id' => $org->id,
            'name' => 'Creative Client',
            'status' => 'ACTIVE',
        ]);

        $adAccount = AdAccount::create([
            'organization_id' => $org->id,
            'client_id' => $client->id,
            'platform' => 'META_ADS',
            'account_name' => 'Test Account',
            'external_account_id' => 'ext-1',
            'currency_code' => 'USD',
            'timezone' => 'UTC',
            'status' => 'ACTIVE',
        ]);

        $campaign = Campaign::create([
            'organization_id' => $org->id,
            'client_id' => $client->id,
            'ad_account_id' => $adAccount->id,
            'name' => 'Creative Campaign',
            'objective' => 'traffic',
            'status' => 'planning',
        ]);

        $this->actingAs($user);

        Livewire::test(Index::class)
            ->set('showCreateModal', true)
            ->set('client_id', $client->id)
            ->set('campaign_id', $campaign->id)
            ->set('type', 'image')
            ->set('title', 'New Creative Request')
            ->set('description', 'Do the thing')
            ->set('priority', 'medium')
            ->set('deadline', now()->addWeek()->toDateString())
            ->call('createCreativeRequest');

        $this->assertDatabaseHas('creative_requests', [
            'organization_id' => $org->id,
            'client_id' => $client->id,
            'campaign_id' => $campaign->id,
            'type' => 'image',
            'title' => 'New Creative Request',
        ]);
    }
}
