<?php

namespace App\Livewire\Orders;

use App\Models\Order;
use Livewire\Component;

class Index extends Component
{
    public $search = '';

    public $status = '';

    public function render()
    {
        $orders = Order::query()
            ->with('client')
            ->when($this->search, function ($query) {
                $query->where('order_number', 'like', '%'.$this->search.'%')
                    ->orWhereHas('client', function ($q) {
                        $q->where('name', 'like', '%'.$this->search.'%');
                    });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->latest('ordered_at')
            ->paginate(10);

        return view('livewire.orders.index', [
            'orders' => $orders,
        ]);
    }
}
