<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendar_page_renders_for_authenticated_user(): void
    {
        $org = Organization::factory()->createOne();
        $user = User::factory()->createOne(['organization_id' => $org->id]);

        $client = Client::create([
            'organization_id' => $org->id,
            'name' => 'Calendar Client',
            'status' => 'ACTIVE',
        ]);

        Task::create([
            'organization_id' => $org->id,
            'client_id' => $client->id,
            'title' => 'Calendar Task',
            'priority' => 'medium',
            'status' => 'pending',
            'deadline' => now()->addDay(),
        ]);

        Project::create([
            'organization_id' => $org->id,
            'client_id' => $client->id,
            'name' => 'Calendar Project',
            'project_code' => 'CAL-PRJ-001',
            'status' => 'planning',
            'priority' => 'medium',
            'end_date' => now()->addDays(2)->toDateString(),
        ]);

        $this->actingAs($user)
            ->get('/calendars')
            ->assertOk()
            ->assertSee('Unified Event Calendar')
            ->assertSee('Calendar Task')
            ->assertSee('Deadline: Calendar Project');
    }
}
