<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\Organization;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsServiceLeadMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_lead_metrics_use_canonical_status_values(): void
    {
        $org = Organization::factory()->create();

        Lead::create([
            'organization_id' => $org->id,
            'name' => 'Lead A',
            'source' => 'manual',
            'status' => LeadStatus::New->value,
        ]);

        Lead::create([
            'organization_id' => $org->id,
            'name' => 'Lead B',
            'source' => 'manual',
            'status' => LeadStatus::Interested->value,
        ]);

        Lead::create([
            'organization_id' => $org->id,
            'name' => 'Lead C',
            'source' => 'manual',
            'status' => LeadStatus::Won->value,
        ]);

        $metrics = app(AnalyticsService::class)->getDashboardMetrics($org->id, '90days');

        $this->assertSame(3, $metrics['leads']['total']);
        $this->assertSame(1, $metrics['leads']['new']);
        $this->assertSame(1, $metrics['leads']['qualified']);
        $this->assertSame(1, $metrics['leads']['converted']);
        $this->assertEqualsWithDelta(33.3333, $metrics['leads']['conversion_rate'], 0.01);
    }
}
