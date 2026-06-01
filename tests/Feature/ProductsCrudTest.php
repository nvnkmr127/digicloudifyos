<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductsCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_edit_route_exists_and_renders(): void
    {
        $org = Organization::factory()->createOne();
        $user = User::factory()->createOne(['organization_id' => $org->id, 'email_verified_at' => now()]);

        $product = Product::create([
            'organization_id' => $org->id,
            'name' => 'Old Name',
            'price' => 10.00,
            'stock' => 1,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get("/products/{$product->id}/edit")
            ->assertOk()
            ->assertSee('Old Name');
    }
}
