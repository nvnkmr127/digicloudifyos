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

            @foreach([1, 2, 3] as $product)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div
                                    class="h-10 w-10 rounded-lg bg-gray-100 flex items-center justify-center text-text-muted">
                                    <!-- Image placeholder -->
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-text-primary">
                                    Enterprise Desk {{ $product }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-text-muted">
                        SKU-{{ str_pad($product, 4, '0', STR_PAD_LEFT) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-text-primary">
                        ${{ number_format(199 * $product, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            In Stock ({{ 20 * $product }})
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('products.edit', $product) }}" wire:navigate
                            class="text-primary hover:text-indigo-900 mr-3">Edit</a>
                        <button type="button" class="text-red-600 hover:text-red-900" x-data=""
                            x-on:click.prevent="$dispatch('open-modal', 'confirm-product-deletion-{{ $product }}')">
                            Delete
                        </button>

                        <x-modal name="confirm-product-deletion-{{ $product }}">
                            <div class="p-6">
                                <h2 class="text-lg font-medium text-text-primary">
                                    Delete Product
                                </h2>
                                <p class="mt-1 text-sm text-text-muted">
                                    Are you sure you want to delete this product?
                                </p>
                                <div class="mt-6 flex justify-end gap-3 text-left">
                                    <x-button color="outline" x-on:click="$dispatch('close')">Cancel</x-button>
                                    <x-button color="danger" x-on:click="$dispatch('close')">Delete</x-button>
                                </div>
                            </div>
                        </x-modal>
                    </td>
                </tr>
            @endforeach
        </x-table>
    </x-card>
</x-app-container>