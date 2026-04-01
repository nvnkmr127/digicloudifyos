<?php

namespace App\Livewire\Orders;

use App\Models\Order;
use Livewire\Component;

class Show extends Component
{
    public Order $order;

    public function mount($id)
    {
        $this->order = Order::with(['client', 'items.product'])->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.orders.show');
    }
}
