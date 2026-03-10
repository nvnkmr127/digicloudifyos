<?php

namespace App\Livewire\Products;

use Livewire\Component;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public function delete($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        session()->flash('success', 'Product deleted successfully.');
    }

    public function render()
    {
        $products = Product::where('organization_id', Auth::user()->organization_id ?? null)
            ->latest()
            ->get();

        return view('livewire.products.index', [
            'products' => $products
        ]);
    }
}
