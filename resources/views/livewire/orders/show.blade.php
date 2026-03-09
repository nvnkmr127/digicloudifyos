<x-app-container>
    <div class="mb-4">
        <a href="{{ route('orders.index') }}" wire:navigate class="text-sm text-text-muted hover:text-primary">
            &larr; Back to Orders
        </a>
    </div>

    <x-page-header title="Order #ORD-1001" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order details main -->
        <div class="lg:col-span-2 space-y-6">
            <x-card>
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-text-primary">Order Items</h2>
                    <span
                        class="px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">Processing</span>
                </div>

                <div class="border divide-y divide-gray-100 rounded-lg">
                    @foreach([1, 2] as $item)
                        <div class="p-4 flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div
                                    class="h-16 w-16 bg-gray-100 rounded-md flex-shrink-0 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-text-primary">Enterprise Desk Product {{ $item }}
                                    </h3>
                                    <p class="text-sm text-text-muted">SKU: {{ 1000 + $item }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-text-primary">${{ number_format(199.00 * $item, 2) }}</p>
                                <p class="text-sm text-text-muted">Qty: {{ $item }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 flex justify-end">
                    <dl class="text-sm text-right space-y-2">
                        <div class="flex justify-between gap-8">
                            <dt class="text-text-muted">Subtotal:</dt>
                            <dd class="text-text-primary font-medium">$597.00</dd>
                        </div>
                        <div class="flex justify-between gap-8">
                            <dt class="text-text-muted">Shipping:</dt>
                            <dd class="text-text-primary font-medium">$25.00</dd>
                        </div>
                        <div class="flex justify-between gap-8">
                            <dt class="text-text-muted">Tax:</dt>
                            <dd class="text-text-primary font-medium">$42.00</dd>
                        </div>
                        <div class="flex justify-between gap-8 pt-2 border-t border-gray-100 mt-2">
                            <dt class="text-base font-semibold text-text-primary">Total:</dt>
                            <dd class="text-base font-semibold text-primary">$664.00</dd>
                        </div>
                    </dl>
                </div>
            </x-card>
        </div>

        <!-- Order sidebar details -->
        <div class="space-y-6">
            <x-card>
                <h3 class="text-md font-semibold text-text-primary border-b border-gray-100 pb-2 mb-4">Customer Details
                </h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm font-medium text-text-primary">Jane Smith</p>
                        <p class="text-sm text-text-muted">jane.smith@example.com</p>
                        <p class="text-sm text-text-muted">+1 (555) 123-4567</p>
                    </div>
                </div>
            </x-card>

            <x-card>
                <h3 class="text-md font-semibold text-text-primary border-b border-gray-100 pb-2 mb-4">Shipping Address
                </h3>
                <address class="not-italic text-sm text-text-muted space-y-1">
                    <p>Jane Smith</p>
                    <p>123 Enterprise Avenue</p>
                    <p>Suite 400</p>
                    <p>San Francisco, CA 94107</p>
                </address>
            </x-card>

            <x-card>
                <h3 class="text-md font-semibold text-text-primary border-b border-gray-100 pb-2 mb-4">Actions</h3>
                <div class="space-y-3">
                    <x-button color="primary" class="w-full justify-center">Mark as Shipped</x-button>
                    <x-button color="outline" class="w-full justify-center">Download Invoice</x-button>
                </div>
            </x-card>
        </div>
    </div>
</x-app-container>