<x-app-container>
    <x-page-header title="Orders">
        <!-- Action area for orders if needed -->
    </x-page-header>

    <x-card>
        <div class="flex flex-col sm:flex-row gap-4 mb-6">
            <x-input type="search" placeholder="Search orders by ID or customer..." class="max-w-md" />
            <x-select class="max-w-xs">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
            </x-select>
        </div>

        <x-table>
            <x-slot name="header">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left">Order ID</th>
                    <th scope="col" class="px-6 py-3 text-left">Customer</th>
                    <th scope="col" class="px-6 py-3 text-left">Date</th>
                    <th scope="col" class="px-6 py-3 text-left">Status</th>
                    <th scope="col" class="px-6 py-3 text-left">Total</th>
                    <th scope="col" class="px-6 py-3 text-right">Actions</th>
                </tr>
            </x-slot>

            @foreach([1001, 1002, 1003, 1004] as $order)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap font-medium text-text-primary">
                        #ORD-{{ $order }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-text-muted">
                        Acme Corp Customer {{ $order }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-text-muted">
                        {{ now()->subDays($order % 5)->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($order % 3 == 0)
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Processing</span>
                        @elseif($order % 2 == 0)
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Delivered</span>
                        @else
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-text-primary font-medium">
                        ${{ number_format($order * 1.5, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('orders.show', $order) }}" wire:navigate
                            class="text-primary hover:text-indigo-900">View Details</a>
                    </td>
                </tr>
            @endforeach
        </x-table>
    </x-card>
</x-app-container>