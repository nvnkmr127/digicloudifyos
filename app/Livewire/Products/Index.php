<?php

namespace App\Livewire\Products;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public function delete($id)
    {
        // Enforce role-based access control (B027)
        if (! Auth::user()->hasRole(['ADMIN', 'OWNER'])) {
            session()->flash('error', 'You do not have permission to delete products.');

            return;
        }

        $product = Product::where('organization_id', Auth::user()->organization_id)->findOrFail($id);
        $product->delete();

        session()->flash('success', 'Product deleted successfully.');
    }

    public function render()
    {
        $products = Product::where('organization_id', Auth::user()->organization_id)
            ->latest()
            ->paginate(12);

        return view('livewire.products.index', [
            'products' => $products,
        ]);
    }
}
