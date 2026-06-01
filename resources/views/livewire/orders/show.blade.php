<x-app-container>
    @php
        $orderStatus = strtoupper($order->status ?? '');
        $orderStatusVariant = match ($orderStatus) {
            'PAID', 'COMPLETED', 'DELIVERED' => 'success',
            'PENDING', 'PROCESSING', 'SHIPPED' => 'info',
            'CANCELLED' => 'danger',
            default => 'neutral',
        };
    @endphp

    <x-page-header title="Order {{ $order->order_number }}">
        <x-badge :variant="$orderStatusVariant" size="xs">{{ $order->status ?? 'Unknown' }}</x-badge>
        <x-button variant="outline">Print Invoice</x-button>
        <x-button variant="primary">Process Order</x-button>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-card>
                <x-section title="Order Summary" description="Line items and totals" />
                <x-table>
                    <x-slot name="header">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left">Item</th>
                            <th scope="col" class="px-6 py-3 text-center">Qty</th>
                            <th scope="col" class="px-6 py-3 text-right">Price</th>
                            <th scope="col" class="px-6 py-3 text-right">Total</th>
                        </tr>
                    </x-slot>
                    @forelse($order->items as $item)
                        <tr>
                            <td class="px-6 py-4 text-sm font-semibold text-text-primary">{{ $item->product->name ?? 'Unknown Product' }}</td>
                            <td class="px-6 py-4 text-center text-sm text-text-muted">{{ $item->quantity }}</td>
                            <td class="px-6 py-4 text-right text-sm text-text-primary">${{ number_format($item->unit_price / 100, 2) }}</td>
                            <td class="px-6 py-4 text-right text-sm font-semibold text-text-primary">${{ number_format($item->total_price / 100, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6">
                                <x-empty-state title="No line items" description="This order has no products attached." />
                            </td>
                        </tr>
                    @endforelse
                </x-table>
                
                <div class="mt-6 border-t border-gray-100 pt-6 flex justify-end">
                    <div class="w-full max-w-xs space-y-3 text-sm">
                        <div class="flex justify-between text-text-muted">
                            <span>Subtotal</span>
                            <span class="text-text-primary">${{ number_format($order->total_amount / 100, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-text-muted">
                            <span>Tax</span>
                            <span class="text-text-primary">$0.00</span>
                        </div>
                        <div class="flex justify-between pt-3 border-t border-gray-100">
                            <span class="font-semibold text-text-primary">Total</span>
                            <span class="font-semibold text-text-primary">${{ number_format($order->total_amount / 100, 2) }}</span>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>

        <div class="space-y-6">
            <x-card>
                <x-section title="Customer" description="Client information for this order" />
                <div class="flex items-center mt-4">
                    <div class="h-10 w-10 bg-primary-soft rounded-element flex items-center justify-center font-semibold text-primary">
                        {{ strtoupper(substr($order->client?->name ?? 'U', 0, 2)) }}
                    </div>
                    <div class="ml-3 min-w-0">
                        <p class="text-sm font-semibold text-text-primary truncate">{{ $order->client?->name ?? 'N/A' }}</p>
                        <p class="text-xs text-text-muted truncate">{{ $order->client?->email ?? 'no-email@agency.com' }}</p>
                    </div>
                </div>
                @if($order->client_id)
                    <div class="mt-4">
                        <x-button variant="outline" class="w-full" href="{{ route('clients.edit', $order->client_id) }}" wire:navigate>
                            View Client
                        </x-button>
                    </div>
                @endif
            </x-card>

            <x-card>
                <x-section title="Timeline" description="Order lifecycle events" />
                <div class="mt-4 space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="h-2 w-2 rounded-full bg-success mt-2"></div>
                        <div>
                            <p class="text-sm font-semibold text-text-primary">Order placed</p>
                            <p class="text-xs text-text-muted">{{ $order->created_at->format('M d, Y · H:i') }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="h-2 w-2 rounded-full bg-info mt-2"></div>
                        <div>
                            <p class="text-sm font-semibold text-text-primary">{{ $order->status ?? 'Awaiting fulfillment' }}</p>
                            <p class="text-xs text-text-muted">Current status</p>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</x-app-container>
