<x-app-container>
    <x-page-header title="Products">
        <x-button color="primary" href="{{ route('products.create') }}" wire:navigate>
            Create Product
        </x-button>
    </x-page-header>

    <x-card>
        <x-table>
            <x-slot name="header">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left">Product</th>
                    <th scope="col" class="px-6 py-3 text-left">SKU</th>
                    <th scope="col" class="px-6 py-3 text-left">Price</th>
                    <th scope="col" class="px-6 py-3 text-left">Stock</th>
                    <th scope="col" class="px-6 py-3 text-right">Actions</th>
                </tr>
            </x-slot>

            @forelse($products as $product)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div
                                    class="h-10 w-10 rounded-lg bg-indigo-50 flex items-center justify-center text-primary">
                                    <span class="text-xs font-bold">{{ substr($product->name, 0, 2) }}</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-text-primary">
                                    {{ $product->name }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-text-muted">
                        {{ $product->sku }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-text-primary">
                        ${{ number_format($product->price, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $product->stock > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $product->stock > 0 ? 'In Stock (' . $product->stock . ')' : 'Out of Stock' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('products.edit', $product->id) }}" wire:navigate
                            class="text-primary hover:text-indigo-900 mr-3">Edit</a>
                        <button type="button" class="text-red-600 hover:text-red-900"
                            x-on:click.prevent="$dispatch('open-modal', 'confirm-product-deletion-{{ $product->id }}')">
                            Delete
                        </button>

                        <x-modal name="confirm-product-deletion-{{ $product->id }}">
                            <div class="p-6">
                                <h2 class="text-lg font-medium text-text-primary">
                                    Delete Product
                                </h2>
                                <p class="mt-1 text-sm text-text-muted">
                                    Are you sure you want to delete this product?
                                </p>
                                <div class="mt-6 flex justify-end gap-3 text-left">
                                    <x-button color="outline" x-on:click="$dispatch('close')">Cancel</x-button>
                                    <x-button color="danger" wire:click="delete('{{ $product->id }}')"
                                        x-on:click="$dispatch('close')">Delete</x-button>
                                </div>
                            </div>
                        </x-modal>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-text-muted">
                        No products found. <a href="{{ route('products.create') }}" class="text-primary font-medium">Create
                            one?</a>
                    </td>
                </tr>
            @endforelse
        </x-table>
    </x-card>
</x-app-container>