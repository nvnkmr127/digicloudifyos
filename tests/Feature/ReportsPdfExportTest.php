<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsPdfExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_pdf_export_returns_pdf(): void
    {
        $org = Organization::factory()->createOne();
        $user = User::factory()->createOne(['organization_id' => $org->id, 'email_verified_at' => now()]);

        $this->actingAs($user)
            ->get('/reports/export/pdf?dateRange=this_month')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
