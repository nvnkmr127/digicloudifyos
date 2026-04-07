<?php

namespace Tests\Unit;

use App\Models\Campaign;
use App\Models\User;
use App\Policies\CampaignPolicy;
use Tests\TestCase;

class CampaignPolicyTest extends TestCase
{
    public function test_delete_denies_active_campaigns(): void
    {
        $user = new User;
        $user->organization_id = 'org-1';
        $user->role = 'ADMIN';

        $campaign = new Campaign;
        $campaign->organization_id = 'org-1';
        $campaign->status = 'running';

        $policy = new CampaignPolicy;

        $this->assertFalse($policy->delete($user, $campaign));
    }

    public function test_delete_allows_inactive_campaign_when_permission_and_org_match(): void
    {
        $user = new User;
        $user->organization_id = 'org-1';
        $user->role = 'ADMIN';

        $campaign = new Campaign;
        $campaign->organization_id = 'org-1';
        $campaign->status = 'completed';

        $policy = new CampaignPolicy;

        $this->assertTrue($policy->delete($user, $campaign));
    }
}
