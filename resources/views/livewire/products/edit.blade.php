<x-app-container>
    <div class="mb-4">
        <a href="{{ route('products.index') }}" wire:navigate class="text-sm text-text-muted hover:text-primary">
            &larr; Back to Products
        </a>
    </div>

    <x-page-header title="Edit Product" />

    <x-card>
        <form wire:submit="update" class="space-y-6 max-w-2xl">
            <div>
                <label for="name" class="block text-sm font-medium text-text-primary">Product Name</label>
                <x-input id="name" type="text" placeholder="e.g. Ergonomic Office Chair" wire:model="name" />
                @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="sku" class="block text-sm font-medium text-text-primary">SKU</label>
                    <x-input id="sku" type="text" placeholder="SKU-12345" wire:model="sku" />
                    @error('sku') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="price" class="block text-sm font-medium text-text-primary">Price ($)</label>
                    <x-input id="price" type="number" step="0.01" placeholder="99.99" wire:model="price" />
                    @error('price') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label for="stock" class="block text-sm font-medium text-text-primary">Stock Quantity</label>
                <x-input id="stock" type="number" placeholder="20" wire:model="stock" />
                @error('stock') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-text-primary">Description</label>
                <x-textarea id="description" rows="4" placeholder="Detailed product description..."
                    wire:model="description"></x-textarea>
                @error('description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <x-button color="outline" href="{{ route('products.index') }}" wire:navigate>Cancel</x-button>
                <x-button color="primary" type="submit">Update Product</x-button>
            </div>
        </form>
    </x-card>
</x-app-container>