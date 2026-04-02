<?php

namespace App\Livewire\Products;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    public $name;

    public $sku;

    public $price;

    public $stock = 0;

    public $description;

    protected $rules = [
        'name' => 'required|min:3',
        'sku' => 'nullable|string',
        'price' => 'required|numeric|min:0',
        'stock' => 'nullable|integer|min:0',
        'description' => 'nullable|string',
    ];

    public function save()
    {
        $this->validate();

        Product::create([
            'organization_id' => Auth::user()->organization_id,
            'name' => $this->name,
            'sku' => $this->sku,
            'price' => $this->price,
            'stock' => $this->stock,
            'description' => $this->description,
            'status' => 'ACTIVE',
        ]);

        session()->flash('success', 'Product added to catalog.');

        return redirect()->route('products.index');
    }

    public function render()
    {
        return view('livewire.products.create');
    }
}
