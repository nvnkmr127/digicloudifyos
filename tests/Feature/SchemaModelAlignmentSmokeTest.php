<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Organization;
use App\Models\Product;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchemaModelAlignmentSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_items_relation_is_defined_and_works(): void
    {
        $org = Organization::factory()->create();

        $client = Client::create([
            'organization_id' => $org->id,
            'name' => 'Test Client',
            'status' => 'ACTIVE',
        ]);

        $order = Order::create([
            'organization_id' => $org->id,
            'client_id' => $client->id,
            'order_number' => 'TEST-ORDER-001',
            'total_amount' => 10.00,
            'status' => 'PENDING',
            'ordered_at' => now(),
        ]);

        $product = Product::create([
            'organization_id' => $org->id,
            'name' => 'Test Product',
            'price' => 10.00,
            'stock' => 0,
            'description' => null,
            'status' => 'active',
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 1000,
            'total_price' => 1000,
        ]);

        $order = Order::with('items.product')->findOrFail($order->id);
        $this->assertCount(1, $order->items);
        $this->assertSame($item->id, $order->items->first()->id);
        $this->assertSame($product->id, $order->items->first()->product->id);
    }
}
