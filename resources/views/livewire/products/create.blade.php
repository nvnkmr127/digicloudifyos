<x-app-container>
    <div class="mb-4">
        <a href="{{ route('products.index') }}" wire:navigate class="text-sm text-text-muted hover:text-primary">
            &larr; Back to Products
        </a>
    </div>

    <x-page-header title="Create Product" />

    <x-card>
        <form wire:submit="save" class="space-y-6 max-w-2xl">
            <div>
                <label for="name" class="block text-sm font-medium text-text-primary">Product Name</label>
                <x-input id="name" type="text" placeholder="e.g. Ergonomic Office Chair" />
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="sku" class="block text-sm font-medium text-text-primary">SKU</label>
                    <x-input id="sku" type="text" placeholder="SKU-12345" />
                </div>
                <div>
                    <label for="price" class="block text-sm font-medium text-text-primary">Price</label>
                    <x-input id="price" type="number" step="0.01" placeholder="99.99" />
                </div>
            </div>

            <div>
                <label for="stock" class="block text-sm font-medium text-text-primary">Initial Stock</label>
                <x-input id="stock" type="number" placeholder="50" />
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-text-primary">Description</label>
                <x-textarea id="description" rows="4" placeholder="Detailed product description..."></x-textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <x-button color="outline" href="{{ route('products.index') }}" wire:navigate>Cancel</x-button>
                <x-button color="primary" type="submit">Save Product</x-button>
            </div>
        </form>
    </x-card>
</x-app-container>