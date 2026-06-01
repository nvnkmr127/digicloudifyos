<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormsPublicSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_form_renders_publicly_and_accepts_submission(): void
    {
        $org = Organization::factory()->createOne();
        $user = User::factory()->createOne(['organization_id' => $org->id, 'email_verified_at' => now()]);

        $form = Form::create([
            'organization_id' => $org->id,
            'name' => 'Public Form',
            'status' => 'ACTIVE',
            'fields' => [
                ['id' => 'a', 'type' => 'text', 'name' => 'full_name', 'label' => 'Full Name', 'placeholder' => '', 'required' => true],
                ['id' => 'b', 'type' => 'email', 'name' => 'email', 'label' => 'Email', 'placeholder' => '', 'required' => true],
            ],
            'slug' => 'public-form',
            'is_published' => true,
            'public_key' => 'test-key',
        ]);

        $this->actingAs($user)->get("/forms/{$form->id}")->assertOk();

        $this->get("/f/{$form->slug}?k={$form->public_key}")
            ->assertOk()
            ->assertSee('Public Form')
            ->assertSee('Full Name');

        $this->post("/f/{$form->slug}/submit", [
            'k' => $form->public_key,
            'full_name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ])->assertRedirect();

        $this->assertDatabaseCount('form_submissions', 1);
        $submission = FormSubmission::firstOrFail();
        $this->assertSame($form->id, $submission->form_id);
        $this->assertSame('Jane Doe', $submission->payload['full_name'] ?? null);
    }
}
