<?php

namespace App\Livewire\Products;

use Livewire\Component;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class Create extends Component
{
    public $name = '';
    public $sku = '';
    public $price = 0;
    public $stock = 0;
    public $description = '';

    protected $rules = [
        'name' => 'required|min:3',
        'sku' => 'nullable|unique:products,sku',
        'price' => 'required|numeric|min:0',
        'stock' => 'required|integer|min:0',
        'description' => 'nullable',
    ];

    public function save()
    {
        $this->validate();

        Product::create([
            'organization_id' => Auth::user()->organization_id ?? null,
            'name' => $this->name,
            'sku' => $this->sku ?: 'SKU-' . strtoupper(uniqid()),
            'price' => $this->price,
            'stock' => $this->stock,
            'description' => $this->description,
        ]);

        session()->flash('success', 'Product created successfully.');

        return redirect()->route('products.index');
    }

    public function render()
    {
        return view('livewire.products.create');
    }
}
