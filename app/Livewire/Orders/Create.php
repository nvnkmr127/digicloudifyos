<?php

namespace App\Livewire\Orders;

use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    public $client_id = '';
    public $status = 'PENDING';
    public $notes = '';
    
    public $items = []; // {product_id, quantity, price}

    protected $rules = [
        'client_id' => 'required|exists:clients,id',
        'status' => 'required|in:PENDING,PROCESSING,SHIPPED,DELIVERED,CANCELLED',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
    ];

    public function mount()
    {
        $this->addItem();
    }

    public function addItem()
    {
        $this->items[] = [
            'product_id' => '',
            'quantity' => 1,
            'price' => 0,
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function updatedItems($value, $key)
    {
        // If product_id changes, update price
        if (str_ends_with($key, '.product_id')) {
            $index = explode('.', $key)[0];
            $product = Product::find($value);
            if ($product) {
                $this->items[$index]['price'] = $product->price;
            }
        }
    }

    public function saveOrder()
    {
        $this->validate();

        $total = collect($this->items)->sum(fn($item) => $item['price'] * $item['quantity']);

        $order = Order::create([
            'organization_id' => Auth::user()->organization_id,
            'client_id' => $this->client_id,
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'status' => $this->status,
            'total_amount' => $total,
            'ordered_at' => now(),
            'notes' => $this->notes,
        ]);

        foreach ($this->items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
                'total_price' => $item['price'] * $item['quantity'],
            ]);
        }

        session()->flash('success', 'Order #' . $order->order_number . ' has been synchronized to the ledger.');

        return redirect()->route('orders.index');
    }

    public function render()
    {
        $orgId = Auth::user()->organization_id;
        return view('livewire.orders.create', [
            'clients' => Client::where('organization_id', $orgId)->get(),
            'products' => Product::where('organization_id', $orgId)->get(),
        ])->layout('layouts.app');
    }
}
