<?php

namespace App\Livewire\Orders;

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Create extends Component
{
    public $client_id = '';

    public $status = 'PENDING';

    public $notes = '';

    public $items = []; // {product_id, quantity, price}

    protected function rules()
    {
        $orgId = Auth::user()->organization_id;

        return [
            'client_id' => [
                'required',
                Rule::exists('clients', 'id')->where('organization_id', $orgId),
            ],
            'status' => 'required|in:PENDING,PROCESSING,SHIPPED,DELIVERED,CANCELLED',
            'items' => 'required|array|min:1',
            'items.*.product_id' => [
                'required',
                Rule::exists('products', 'id')->where('organization_id', $orgId),
            ],
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }

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
            // Security: already scoped to organization
            $product = Product::where('organization_id', Auth::user()->organization_id)->find($value);
            if ($product) {
                $this->items[$index]['price'] = (float) $product->price;
            }
        }
    }

    public function saveOrder()
    {
        $this->validate();

        $orgId = Auth::user()->organization_id;

        // Security: Fetch products from DB to get authoritative prices and verify ownership
        $productIds = collect($this->items)->pluck('product_id')->unique();
        $products = Product::where('organization_id', $orgId)
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        // B015: Prevent save if any product is missing (possible IDOR attempt or race condition)
        if ($products->count() !== $productIds->count()) {
            $this->addError('items', 'One or more selected products are invalid or no longer available.');

            return;
        }

        $total = 0;
        $orderItems = [];

        foreach ($this->items as $item) {
            $product = $products->get($item['product_id']);
            $unitPrice = (float) $product->price; // Authority price from DB, ignore Livewire state
            $quantity = (int) $item['quantity'];
            $itemTotal = $unitPrice * $quantity;

            $total += $itemTotal;
            $orderItems[] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $itemTotal,
            ];
        }

        // Sequential-at-save numbering to prevent race conditions (B021)
        // Wrapped in transaction for safety
        $orderNum = DB::transaction(function () use ($orgId, $total, $orderItems) {
            $lastOrder = Order::where('organization_id', $orgId)
                ->lockForUpdate()
                ->latest()
                ->first();

            $nextNum = $lastOrder ? ((int) str_replace('ORD-', '', $lastOrder->order_number)) + 1 : 1;
            $orderNum = 'ORD-'.str_pad($nextNum, 5, '0', STR_PAD_LEFT);

            $order = Order::create([
                'organization_id' => $orgId,
                'client_id' => $this->client_id,
                'order_number' => $orderNum,
                'status' => $this->status,
                'total_amount' => $total,
                'ordered_at' => now(),
                'notes' => $this->notes,
            ]);

            foreach ($orderItems as $itemData) {
                $order->items()->create($itemData);
            }

            return $orderNum;
        });

        session()->flash('success', 'Order #'.$order->order_number.' has been synchronized to the ledger.');

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
