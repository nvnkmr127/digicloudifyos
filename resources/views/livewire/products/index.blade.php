<x-app-container>
    <x-page-header title="Product Inventory">
        <a href="{{ route('products.create') }}" wire:navigate>
            <x-button color="primary" class="rounded-2xl shadow-lg">
                Add Product
            </x-button>
        </a>
    </x-page-header>

    @if (session()->has('success'))
        <div class="mb-8 p-6 bg-green-50 border border-green-100 text-green-700 rounded-[2rem] font-black uppercase tracking-widest text-[10px]">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-[3rem] shadow-2xl overflow-hidden border-none text-text-primary">
        <x-table>
            <x-slot name="header">
                <tr>
                    <th scope="col" class="px-8 py-6 text-left text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Product Details</th>
                    <th scope="col" class="px-8 py-6 text-left text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">SKU</th>
                    <th scope="col" class="px-8 py-6 text-left text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Price</th>
                    <th scope="col" class="px-8 py-6 text-left text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Stock</th>
                    <th scope="col" class="px-8 py-6 text-left text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Status</th>
                    <th scope="col" class="px-8 py-6 text-right text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Actions</th>
                </tr>
            </x-slot>

            @forelse($products as $product)
                <tr class="hover:bg-gray-50/50 transition duration-300">
                    <td class="px-8 py-6 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="h-12 w-12 flex-shrink-0 bg-gray-100 rounded-2xl flex items-center justify-center font-black text-indigo-400 text-xs shadow-inner">
                                {{ strtoupper(substr($product->name, 0, 2)) }}
                            </div>
                            <div class="ml-6">
                                <div class="text-sm font-black text-gray-900 tracking-tight">{{ $product->name }}</div>
                                <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-1">{{ str($product->description)->limit(30) }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-8 py-6 whitespace-nowrap text-xs font-black text-gray-400 tracking-widest uppercase">
                        {{ $product->sku ?? '---' }}
                    </td>
                    <td class="px-8 py-6 whitespace-nowrap text-sm font-black text-gray-900">
                        ${{ number_format($product->price, 2) }}
                    </td>
                    <td class="px-8 py-6 whitespace-nowrap">
                        <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-[10px] font-black tracking-widest uppercase">
                            {{ $product->stock ?? 0 }} In Stock
                        </span>
                    </td>
                    <td class="px-8 py-6 whitespace-nowrap">
                        <span class="px-3 py-1 {{ $product->status === 'ACTIVE' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400' }} rounded-full text-[10px] font-black tracking-widest uppercase">
                            {{ $product->status }}
                        </span>
                    </td>
                    <td class="px-8 py-6 whitespace-nowrap text-right">
                        <button wire:click="delete('{{ $product->id }}')" 
                            wire:confirm="Are you sure you want to remove this product?"
                            class="text-red-400 hover:text-red-600 transition p-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-8 py-20 text-center">
                        <div class="text-gray-300 mb-4 flex justify-center">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight">No products listed</h3>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-2">Add items to your catalog to start generating orders</p>
                    </td>
                </tr>
            @endforelse
        </x-table>
    </div>

</x-app-container>