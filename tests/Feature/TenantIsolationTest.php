<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Order;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_show_is_not_accessible_cross_organization(): void
    {
        $orgA = $this->createOrganization();
        $orgB = $this->createOrganization();

        $userB = $this->createUser([
            'organization_id' => $orgB->id,
            'role' => 'ADMIN',
        ]);

        $orderA = Order::create([
            'organization_id' => $orgA->id,
            'order_number' => 'ORD-'.uniqid(),
            'total_amount' => 10.00,
            'status' => 'PENDING',
        ]);

        $this->actingAs($userB)
            ->get(route('orders.show', ['id' => $orderA->id]))
            ->assertNotFound();
    }

    public function test_contact_show_is_not_accessible_cross_organization(): void
    {
        $orgA = $this->createOrganization();
        $orgB = $this->createOrganization();

        $userB = $this->createUser([
            'organization_id' => $orgB->id,
            'role' => 'ADMIN',
        ]);

        $contactA = Contact::create([
            'organization_id' => $orgA->id,
            'first_name' => 'Test',
            'last_name' => 'Contact',
            'email' => 'contact-'.uniqid().'@example.com',
        ]);

        $this->actingAs($userB)
            ->get(route('contacts.show', ['id' => $contactA->id]))
            ->assertNotFound();
    }

    private function createOrganization(): Organization
    {
        $org = Organization::factory()->create();
        if (! $org instanceof Organization) {
            throw new \RuntimeException('Failed to create organization.');
        }

        return $org;
    }

    private function createUser(array $attributes): User
    {
        $user = User::factory()->create($attributes);
        if (! $user instanceof User) {
            throw new \RuntimeException('Failed to create user.');
        }

        return $user;
    }
}
