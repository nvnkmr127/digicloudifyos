<x-app-container>
    <x-page-header title="Catalog New Product" />

    <div class="max-w-4xl">
        <x-card class="p-10 border-none shadow-2xl rounded-[3rem]">
            <form wire:submit="save" class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="md:col-span-2">
                        <x-input-label for="name">Product Name</x-input-label>
                        <x-text-input wire:model="name" id="name" class="w-full mt-2 rounded-2xl" placeholder="e.g., Enterprise SEO Package" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="sku">SKU / ID</x-input-label>
                        <x-text-input wire:model="sku" id="sku" class="w-full mt-2 rounded-2xl" placeholder="SEO-001" />
                        <x-input-error :messages="$errors->get('sku')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="price">Unit Price ($)</x-input-label>
                        <x-text-input wire:model="price" type="number" step="0.01" id="price" class="w-full mt-2 rounded-2xl" placeholder="1200.00" />
                        <x-input-error :messages="$errors->get('price')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="stock">Initial Stock</x-input-label>
                        <x-text-input wire:model="stock" type="number" id="stock" class="w-full mt-2 rounded-2xl" placeholder="0" />
                        <x-input-error :messages="$errors->get('stock')" class="mt-2" />
                    </div>

                    <div class="md:col-span-2">
                        <x-input-label for="description">Product Description</x-input-label>
                        <x-textarea wire:model="description" id="description" class="w-full mt-2 rounded-2xl" placeholder="Details of what is included in this offer..." rows="4"></x-textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>
                </div>

                <div class="mt-10 flex justify-end space-x-4">
                    <x-button color="outline" type="button" class="rounded-2xl px-10 py-4" onclick="history.back()">Cancel</x-button>
                    <x-button color="primary" type="submit" class="rounded-2xl px-12 py-4 shadow-xl shadow-indigo-100">Add to Catalog</x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-container>