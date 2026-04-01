<x-app-container>
    <x-page-header title="Architect New Order">
         <x-button color="outline" href="{{ route('orders.index') }}" wire:navigate class="mr-3 rounded-2xl">
            Cancel
        </x-button>
        <x-button color="primary" class="rounded-2xl shadow-lg px-8 shadow-indigo-100" wire:click="saveOrder">
            Commit Order to Ledger
        </x-button>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <!-- Order Context -->
        <div class="lg:col-span-1 space-y-8">
            <x-card class="p-10 border-none shadow-2xl rounded-[3rem] bg-indigo-600 text-white relative overflow-hidden">
                <div class="absolute -right-10 -top-10 h-40 w-40 bg-white opacity-5 rounded-full"></div>
                <h3 class="text-xl font-black mb-10 tracking-tighter uppercase italic">Order Intelligence</h3>
                
                <div class="space-y-8 relative z-10">
                    <div>
                        <x-input-label class="text-indigo-200 opacity-70">Client Node</x-input-label>
                        <x-select wire:model="client_id" class="w-full mt-2 bg-indigo-500/30 border-indigo-400/50 text-white rounded-2xl p-4">
                            <option value="" class="text-gray-900 text-gray-400">Select Identity...</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" class="text-gray-900">{{ $client->name }}</option>
                            @endforeach
                        </x-select>
                        <x-input-error :messages="$errors->get('client_id')" class="mt-2 text-white/70" />
                    </div>

                    <div>
                        <x-input-label class="text-indigo-200 opacity-70">Deployment Status</x-input-label>
                        <x-select wire:model="status" class="w-full mt-2 bg-indigo-500/30 border-indigo-400/50 text-white rounded-2xl p-4">
                            <option value="PENDING" class="text-gray-900">Pending - Ledger Only</option>
                            <option value="PROCESSING" class="text-gray-900">Processing - Fulfillment Start</option>
                            <option value="SHIPPED" class="text-gray-900">Shipped - Transient Node</option>
                            <option value="DELIVERED" class="text-gray-900">Delivered - Success</option>
                        </x-select>
                    </div>

                    <div>
                        <x-input-label class="text-indigo-200 opacity-70">Operational Notes</x-input-label>
                        <x-textarea wire:model="notes" class="w-full mt-2 bg-indigo-500/30 border-indigo-400/50 text-white placeholder-indigo-300 rounded-2xl p-4 text-sm" rows="4" placeholder="Fulfillment constraints or logic..."></x-textarea>
                    </div>
                </div>
            </x-card>

            <div class="bg-indigo-50/50 p-8 rounded-[2.5rem] border border-indigo-100 flex items-start gap-4 shadow-inner">
                <div class="h-10 w-10 flex-shrink-0 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                     <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-1">Fiscal Pulse</p>
                     <p class="text-xl font-black text-gray-900 tracking-tight tracking-widest leading-none">
                         ${{ number_format(collect($items)->sum(fn($i) => ($i['price'] ?? 0) * ($i['quantity'] ?? 1)), 2) }}
                     </p>
                     <p class="text-[9px] font-bold text-gray-300 uppercase mt-1 tracking-widest italic">Calculated Real-Time Flux</p>
                </div>
            </div>
        </div>

        <!-- Line Items -->
        <div class="lg:col-span-2 space-y-6">
            <div class="flex items-center justify-between mb-2">
                 <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em]">Payload Manifest ({{ count($items) }} Items)</h3>
                 <button wire:click="addItem" class="text-xs font-black text-indigo-600 uppercase tracking-widest hover:underline">+ Add Product Node</button>
            </div>

            <div class="space-y-4">
                @foreach($items as $index => $item)
                    <x-card class="p-8 border-none shadow-xl rounded-[2.5rem] bg-white group hover:border-indigo-100 transition duration-300 relative">
                        <div class="flex flex-col md:flex-row gap-6">
                            <div class="flex-1">
                                <x-input-label class="text-[9px]">Product Sequence</x-input-label>
                                <x-select wire:model.live="items.{{ $index }}.product_id" class="w-full mt-1 rounded-xl text-xs font-black border-gray-100 p-2">
                                    <option value="">Select Catalog Item...</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                    @endforeach
                                </x-select>
                                <x-input-error :messages="$errors->get('items.' . $index . '.product_id')" class="mt-1" />
                            </div>

                            <div class="w-full md:w-32">
                                <x-input-label class="text-[9px]">Quantity</x-input-label>
                                <x-text-input type="number" wire:model.live="items.{{ $index }}.quantity" class="w-full mt-1 rounded-xl text-xs font-black border-gray-100" min="1" />
                            </div>

                            <div class="w-full md:w-40 text-right pr-6">
                                <x-input-label class="text-[9px]">Unit Price</x-input-label>
                                <div class="text-lg font-black text-gray-900 mt-2 tracking-tighter italic">
                                    ${{ number_format($item['price'], 2) }}
                                </div>
                                <span class="text-[8px] font-bold text-gray-300 uppercase tracking-widest">Total: ${{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                            </div>

                            <div class="flex items-end pb-1">
                                <button wire:click="removeItem({{ $index }})" class="p-2 text-gray-200 hover:text-rose-400 transition">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </x-card>
                @endforeach

                <button wire:click="addItem" class="w-full py-8 border-4 border-dashed border-gray-50 rounded-[2.5rem] flex flex-col items-center justify-center text-gray-300 hover:border-indigo-100 hover:text-indigo-400 transition group">
                     <svg class="h-8 w-8 mb-2 group-hover:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                     <span class="text-[10px] font-black uppercase tracking-[0.2em]">Extend Manifest</span>
                </button>
            </div>
        </div>
    </div>
</x-app-container>
