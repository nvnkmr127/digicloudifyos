<?php

namespace App\Livewire\Orders;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public Order $order;

    public function mount($id)
    {
        $organizationId = Auth::user()?->organization_id;

        $this->order = Order::with(['client', 'items.product'])
            ->where('organization_id', $organizationId)
            ->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.orders.show');
    }
}
