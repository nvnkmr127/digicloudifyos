<x-app-container>
    <x-page-header title="Orders">
        <x-button color="primary" href="{{ route('orders.create') }}" wire:navigate class="rounded-2xl shadow-lg px-8">
            + New manual order
        </x-button>
    </x-page-header>

    <x-card>
        <x-toolbar class="mb-6" variant="subtle">
            <x-slot name="left">
                <x-input wire:model.live.debounce.300ms="search" type="search" placeholder="Search orders…" aria-label="Search orders" class="w-full sm:w-96" />
                <x-select wire:model.live="status" class="w-full sm:w-56">
                    <option value="">All Statuses</option>
                    <option value="PENDING">Pending</option>
                    <option value="PROCESSING">Processing</option>
                    <option value="SHIPPED">Shipped</option>
                    <option value="DELIVERED">Delivered</option>
                    <option value="CANCELLED">Cancelled</option>
                </x-select>
            </x-slot>
        </x-toolbar>

        <div wire:loading class="mb-4 text-sm text-text-muted">Loading…</div>

        <x-table>
            <x-slot name="header">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left">Order #</th>
                    <th scope="col" class="px-6 py-3 text-left">Customer</th>
                    <th scope="col" class="px-6 py-3 text-left">Date</th>
                    <th scope="col" class="px-6 py-3 text-left">Status</th>
                    <th scope="col" class="px-6 py-3 text-left">Total</th>
                    <th scope="col" class="px-6 py-3 text-right">Actions</th>
                </tr>
            </x-slot>

            @forelse($orders as $order)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap font-medium text-text-primary uppercase tracking-wider">
                        #{{ $order->order_number }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-text-muted">
                        {{ $order->client->name ?? 'Guest Client' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-text-muted">
                        {{ $order->ordered_at->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-3 py-1 text-[10px] font-semibold rounded-full uppercase tracking-widest
                            {{ $order->status === 'DELIVERED' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $order->status === 'PROCESSING' ? 'bg-blue-100 text-blue-700' : '' }}
                            {{ $order->status === 'PENDING' ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $order->status === 'CANCELLED' ? 'bg-red-100 text-red-700' : '' }}
                        ">
                            {{ $order->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-text-primary font-semibold">
                        ${{ number_format($order->total_amount, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('orders.show', $order->id) }}" wire:navigate
                            class="text-primary hover:text-primary-dark font-bold uppercase tracking-widest text-xs">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6">
                        <x-empty-state
                            title="No orders found"
                            description="Try adjusting your search or create a new order."
                        >
                            <x-slot name="actions">
                                <x-button href="{{ route('orders.create') }}" wire:navigate>
                                    New Order
                                </x-button>
                            </x-slot>
                        </x-empty-state>
                    </td>
                </tr>
            @endforelse
        </x-table>

        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    </x-card>
</x-app-container>
