<?php

namespace App\Services\Playbooks;

use App\Models\PlaybookTemplate;
use App\Models\ServicePackage;

class ServicePackageService
{
    public function ensureDefaults(string $orgId): void
    {
        $existing = ServicePackage::where('organization_id', $orgId)->count();
        if ($existing > 0) {
            return;
        }

        $onboarding = PlaybookTemplate::where('organization_id', $orgId)->where('category', 'onboarding')->pluck('id', 'name');
        $seo = PlaybookTemplate::where('organization_id', $orgId)->where('category', 'seo')->pluck('id', 'name');
        $branding = PlaybookTemplate::where('organization_id', $orgId)->where('category', 'branding')->pluck('id', 'name');

        $leadGenId = $onboarding->get('Lead Gen Revenue Sprint (14-day onboarding)');
        $seoId = $seo->get('SEO 30/60/90 Foundation');
        $brandId = $branding->get('Brand Kit & Creative System (setup)');

        ServicePackage::create([
            'organization_id' => $orgId,
            'name' => 'Lead Gen Starter (Monthly Ops)',
            'industry' => null,
            'cadence' => 'monthly',
            'day_of_month' => 1,
            'is_active' => true,
            'config' => [
                'playbook_template_ids' => array_values(array_filter([$leadGenId])),
            ],
        ]);

        ServicePackage::create([
            'organization_id' => $orgId,
            'name' => 'SEO Growth (Monthly Ops)',
            'industry' => null,
            'cadence' => 'monthly',
            'day_of_month' => 2,
            'is_active' => true,
            'config' => [
                'playbook_template_ids' => array_values(array_filter([$seoId])),
            ],
        ]);

        ServicePackage::create([
            'organization_id' => $orgId,
            'name' => 'Branding & Creative (Monthly Ops)',
            'industry' => null,
            'cadence' => 'monthly',
            'day_of_month' => 3,
            'is_active' => true,
            'config' => [
                'playbook_template_ids' => array_values(array_filter([$brandId])),
            ],
        ]);
    }
}
