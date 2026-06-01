<?php

namespace Tests\Feature;

use App\Models\AdAccount;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\Organization;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignDetailViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_detail_renders_and_shows_task_deadline(): void
    {
        $org = Organization::factory()->createOne();
        $user = User::factory()->createOne(['organization_id' => $org->id, 'email_verified_at' => now()]);

        $client = Client::create([
            'organization_id' => $org->id,
            'name' => 'Campaign Client',
            'status' => 'ACTIVE',
        ]);

        $adAccount = AdAccount::create([
            'organization_id' => $org->id,
            'client_id' => $client->id,
            'platform' => 'META_ADS',
            'account_name' => 'Test Account',
            'external_account_id' => 'ext-2',
            'currency_code' => 'USD',
            'timezone' => 'UTC',
            'status' => 'ACTIVE',
        ]);

        $campaign = Campaign::create([
            'organization_id' => $org->id,
            'client_id' => $client->id,
            'ad_account_id' => $adAccount->id,
            'name' => 'Campaign A',
            'objective' => 'traffic',
            'status' => 'planning',
        ]);

        $deadline = now()->addDays(3)->startOfDay();

        Task::create([
            'organization_id' => $org->id,
            'client_id' => $client->id,
            'campaign_id' => $campaign->id,
            'title' => 'Task 1',
            'priority' => 'medium',
            'status' => 'pending',
            'deadline' => $deadline,
        ]);

        $this->actingAs($user)
            ->get("/campaigns/{$campaign->id}")
            ->assertOk()
            ->assertSee('Campaign A')
            ->assertSee($deadline->format('M d'));
    }

    public function test_campaign_detail_renders_adsets_and_can_preview_ad(): void
    {
        $org = Organization::factory()->createOne();
        $user = User::factory()->createOne(['organization_id' => $org->id, 'email_verified_at' => now()]);

        $client = Client::create([
            'organization_id' => $org->id,
            'name' => 'Campaign Client',
            'status' => 'ACTIVE',
        ]);

        $adAccount = AdAccount::create([
            'organization_id' => $org->id,
            'client_id' => $client->id,
            'platform' => 'META_ADS',
            'account_name' => 'Test Account',
            'external_account_id' => 'ext-2',
            'currency_code' => 'USD',
            'timezone' => 'UTC',
            'status' => 'ACTIVE',
        ]);

        $campaign = Campaign::create([
            'organization_id' => $org->id,
            'client_id' => $client->id,
            'ad_account_id' => $adAccount->id,
            'name' => 'Campaign A',
            'objective' => 'traffic',
            'status' => 'planning',
        ]);

        $adSet = \App\Models\AdSet::create([
            'organization_id' => $org->id,
            'campaign_id' => $campaign->id,
            'name' => 'Ad Set 1',
            'external_adset_id' => 'adset-1',
            'daily_budget' => 20.00,
            'status' => 'ACTIVE',
        ]);

        $creative = \App\Models\AdCreative::create([
            'organization_id' => $org->id,
            'ad_account_id' => $adAccount->id,
            'external_creative_id' => 'creative-1',
            'name' => 'Creative 1',
            'title' => 'Title 1',
            'body' => 'Body text',
            'image_url' => 'https://example.com/image.jpg',
        ]);

        $ad = \App\Models\Ad::create([
            'organization_id' => $org->id,
            'ad_set_id' => $adSet->id,
            'ad_creative_id' => $creative->id,
            'name' => 'Ad 1',
            'external_ad_id' => 'ad-1',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($user);

        \Livewire\Livewire::test(\App\Livewire\Campaigns\DetailView::class, ['campaign' => $campaign])
            ->call('setTab', 'adsets')
            ->assertSee('Ad Set 1')
            ->assertSee('Ad 1')
            ->call('showAdPreview', $ad->id)
            ->assertSet('showAdModal', true)
            ->assertSet('selectedAd.id', $ad->id)
            ->assertSee('Creative Specifications')
            ->assertSee('Body text');
    }

    public function test_campaign_detail_ad_preview_is_tenant_isolated(): void
    {
        $orgA = Organization::factory()->createOne();
        $userA = User::factory()->createOne(['organization_id' => $orgA->id, 'email_verified_at' => now()]);

        $orgB = Organization::factory()->createOne();

        $clientA = Client::create([
            'organization_id' => $orgA->id,
            'name' => 'Campaign Client A',
            'status' => 'ACTIVE',
        ]);

        $adAccountA = AdAccount::create([
            'organization_id' => $orgA->id,
            'client_id' => $clientA->id,
            'platform' => 'META_ADS',
            'account_name' => 'Test Account A',
            'external_account_id' => 'ext-a',
            'currency_code' => 'USD',
            'timezone' => 'UTC',
            'status' => 'ACTIVE',
        ]);

        $campaignA = Campaign::create([
            'organization_id' => $orgA->id,
            'client_id' => $clientA->id,
            'ad_account_id' => $adAccountA->id,
            'name' => 'Campaign A',
            'objective' => 'traffic',
            'status' => 'planning',
        ]);

        $adSetA = \App\Models\AdSet::create([
            'organization_id' => $orgA->id,
            'campaign_id' => $campaignA->id,
            'name' => 'Ad Set A',
            'external_adset_id' => 'adset-a',
            'daily_budget' => 20.00,
            'status' => 'ACTIVE',
        ]);

        $adB = \App\Models\Ad::create([
            'organization_id' => $orgB->id,
            'ad_set_id' => $adSetA->id,
            'name' => 'Ad B',
            'external_ad_id' => 'ad-b',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($userA);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        \Livewire\Livewire::test(\App\Livewire\Campaigns\DetailView::class, ['campaign' => $campaignA])
            ->call('showAdPreview', $adB->id);
    }
}
