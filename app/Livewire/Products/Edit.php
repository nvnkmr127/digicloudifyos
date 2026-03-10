<?php

namespace App\Livewire\Products;

use Livewire\Component;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class Edit extends Component
{
    public Product $product;
    public $name;
    public $sku;
    public $price;
    public $stock;
    public $description;

    protected $rules = [
        'name' => 'required|min:3',
        'sku' => 'nullable',
        'price' => 'required|numeric|min:0',
        'stock' => 'required|integer|min:0',
        'description' => 'nullable',
    ];

    public function mount(Product $product)
    {
        $this->product = $product;
        $this->name = $product->name;
        $this->sku = $product->sku;
        $this->price = $product->price;
        $this->stock = $product->stock;
        $this->description = $product->description;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|min:3',
            'sku' => 'nullable|unique:products,sku,' . $this->product->id,
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable',
        ]);

        $this->product->update([
            'name' => $this->name,
            'sku' => $this->sku,
            'price' => $this->price,
            'stock' => $this->stock,
            'description' => $this->description,
        ]);

        session()->flash('success', 'Product updated successfully.');

        return redirect()->route('products.index');
    }

    public function render()
    {
        return view('livewire.products.edit');
    }
}
