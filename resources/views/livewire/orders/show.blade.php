<x-app-container>
    <x-page-header title="Order Detail: {{ $order->order_number }}">
        <div class="flex space-x-2">
            <x-button color="outline" class="rounded-xl">Print Invoice</x-button>
            <x-button color="primary" class="rounded-xl shadow-lg">Process Order</x-button>
        </div>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-8">
            <x-card class="p-10 border-none shadow-2xl rounded-[3rem]">
                <h3 class="text-xl font-black text-gray-900 tracking-tight mb-8 uppercase italic">Order Summary</h3>
                <x-table>
                    <x-slot name="header">
                        <tr>
                            <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-gray-400">Item</th>
                            <th class="px-6 py-4 text-center text-[10px] font-black uppercase tracking-widest text-gray-400">Qty</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black uppercase tracking-widest text-gray-400">Price</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black uppercase tracking-widest text-gray-400">Total</th>
                        </tr>
                    </x-slot>
                    @forelse($order->items as $item)
                        <tr>
                            <td class="px-6 py-4 text-sm font-black text-gray-900">{{ $item->product->name ?? 'Unknown Product' }}</td>
                            <td class="px-6 py-4 text-center text-sm font-bold text-gray-400 tracking-widest">{{ $item->quantity }}</td>
                            <td class="px-6 py-4 text-right text-sm font-bold text-gray-900 tracking-widest">${{ number_format($item->unit_price / 100, 2) }}</td>
                            <td class="px-6 py-4 text-right text-sm font-black text-indigo-600 tracking-tighter">${{ number_format($item->total_price / 100, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-300 italic">No line items found.</td>
                        </tr>
                    @endforelse
                </x-table>
                
                <div class="mt-10 border-t border-gray-100 pt-8 flex justify-end">
                    <div class="w-64 space-y-4">
                        <div class="flex justify-between items-center text-sm font-bold text-gray-400 uppercase tracking-widest">
                            <span>Subtotal</span>
                            <span class="text-gray-900">${{ number_format($order->total_amount / 100, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm font-bold text-gray-400 uppercase tracking-widest">
                            <span>Tax (0%)</span>
                            <span class="text-gray-900">$0.00</span>
                        </div>
                        <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                            <span class="text-lg font-black text-gray-900 uppercase tracking-tighter">Grand Total</span>
                            <span class="text-2xl font-black text-branding">${{ number_format($order->total_amount / 100, 2) }}</span>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>

        <div class="space-y-8">
            <x-card class="p-8 border-none shadow-2xl rounded-[3rem] bg-gray-50/50">
                <h3 class="text-base font-black text-gray-900 tracking-tight mb-6 uppercase">Customer Intelligence</h3>
                <div class="flex items-center mb-6">
                    <div class="h-12 w-12 bg-white rounded-2xl shadow-sm flex items-center justify-center font-black text-indigo-400">
                        {{ strtoupper(substr($order->client->name ?? 'U', 0, 2)) }}
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-black text-gray-900 tracking-tight">{{ $order->client->name ?? 'N/A' }}</p>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ $order->client->email ?? 'no-email@agency.com' }}</p>
                    </div>
                </div>
                <x-button color="outline" class="w-full rounded-xl py-3 text-[10px] font-black uppercase tracking-[0.2em]">View Client Profile</x-button>
            </x-card>

            <x-card class="p-8 border-none shadow-2xl rounded-[3rem]">
                <h3 class="text-base font-black text-gray-900 tracking-tight mb-6 uppercase italic">Order Timeline</h3>
                <div class="space-y-6">
                    <div class="flex items-start">
                        <div class="h-2 w-2 rounded-full bg-green-500 mt-1.5 shadow-sm shadow-green-100"></div>
                        <div class="ml-4">
                            <p class="text-[10px] font-black text-gray-900 uppercase tracking-widest">Order Placed</p>
                            <p class="text-[10px] font-bold text-gray-400 mt-1">{{ $order->created_at->format('M d, Y - H:i') }}</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="h-2 w-2 rounded-full bg-indigo-500 mt-1.5 shadow-sm shadow-indigo-100 animate-pulse"></div>
                        <div class="ml-4">
                            <p class="text-[10px] font-black text-branding uppercase tracking-widest">Awaiting Fulfillment</p>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</x-app-container>