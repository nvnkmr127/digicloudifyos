<x-app-container>
    <div class="mb-4">
        <a href="{{ route('invoices.index') }}" wire:navigate class="text-sm text-text-muted hover:text-primary">
            &larr; Back to Invoices
        </a>
    </div>

    <x-page-header title="Edit Invoice: {{ $invoice->invoice_number }}" />

    <x-card>
        <form wire:submit="update" class="space-y-8">
            <!-- Basic Info -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="invoice_number" class="block text-sm font-medium text-text-primary">Invoice
                        Number</label>
                    <x-input id="invoice_number" type="text" wire:model="invoice_number" />
                    @error('invoice_number') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="client_id" class="block text-sm font-medium text-text-primary">Client</label>
                    <select id="client_id" wire:model="client_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Select a client...</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                    @error('client_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="project_id" class="block text-sm font-medium text-text-primary">Project
                        (Optional)</label>
                    <select id="project_id" wire:model="project_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">No Project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="issue_date" class="block text-sm font-medium text-text-primary">Issue Date</label>
                    <x-input id="issue_date" type="date" wire:model="issue_date" />
                    @error('issue_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="due_date" class="block text-sm font-medium text-text-primary">Due Date</label>
                    <x-input id="due_date" type="date" wire:model="due_date" />
                    @error('due_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-text-primary">Status</label>
                    <select id="status" wire:model="status"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="draft">Draft</option>
                        <option value="sent">Sent</option>
                        <option value="paid">Paid</option>
                        <option value="overdue">Overdue</option>
                    </select>
                </div>
            </div>

            <!-- Items -->
            <div>
                <h3 class="text-lg font-medium text-text-primary mb-4 border-b border-gray-100 pb-2">Invoice Items</h3>
                <div class="space-y-4">
                    @foreach($items as $index => $item)
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end bg-gray-50/50 p-4 rounded-lg relative">
                            <div class="md:col-span-6">
                                <label class="block text-xs font-medium text-text-muted mb-1">Description</label>
                                <x-input type="text" wire:model="items.{{ $index }}.description"
                                    placeholder="Item description..." />
                                @error('items.' . $index . '.description') <span
                                class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-text-muted mb-1">Quantity</label>
                                <x-input type="number" step="0.01" wire:model="items.{{ $index }}.quantity" />
                                @error('items.' . $index . '.quantity') <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-text-muted mb-1">Unit Price</label>
                                <x-input type="number" step="0.01" wire:model="items.{{ $index }}.unit_price" />
                                @error('items.' . $index . '.unit_price') <span
                                class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="md:col-span-1 text-right py-3 text-sm font-medium text-text-primary">
                                ${{ number_format($item['quantity'] * $item['unit_price'], 2) }}
                            </div>
                            <div class="md:col-span-1 text-right">
                                <button type="button" wire:click="removeItem({{ $index }})"
                                    class="text-red-500 hover:text-red-700 p-2">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4">
                    <x-button type="button" color="outline" wire:click="addItem" class="text-xs">+ Add Item</x-button>
                </div>
            </div>

            <!-- Footer / Notes -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="notes" class="block text-sm font-medium text-text-primary">Notes</label>
                    <x-textarea id="notes" rows="3" wire:model="notes" placeholder="Visible to client..."></x-textarea>
                </div>
                <div>
                    <label for="payment_terms" class="block text-sm font-medium text-text-primary">Payment Terms</label>
                    <x-textarea id="payment_terms" rows="3" wire:model="payment_terms"
                        placeholder="e.g. Net 30 days..."></x-textarea>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <x-button color="outline" href="{{ route('invoices.index') }}" wire:navigate>Cancel</x-button>
                <x-button color="primary" type="submit">Update Invoice</x-button>
            </div>
        </form>
    </x-card>
</x-app-container>