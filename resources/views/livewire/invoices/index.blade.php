<x-app-container>
    <x-page-header title="Invoicing & Billing">
        <x-button color="primary" href="{{ route('invoices.create') }}" wire:navigate>Create Invoice</x-button>
    </x-page-header>

    <!-- Search -->
    <div class="mb-6 max-w-md">
        <x-input wire:model.live="search" type="text" placeholder="Search by invoice # or client name..." />
    </div>

    <x-card>
        <x-table>
            <x-slot name="header">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left">Invoice #</th>
                    <th scope="col" class="px-6 py-3 text-left">Client & Project</th>
                    <th scope="col" class="px-6 py-3 text-left">Amount</th>
                    <th scope="col" class="px-6 py-3 text-left">Issue / Due Date</th>
                    <th scope="col" class="px-6 py-3 text-left">Status</th>
                    <th scope="col" class="px-6 py-3 text-right">Actions</th>
                </tr>
            </x-slot>

            @forelse($invoices as $invoice)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-bold text-primary">{{ $invoice->invoice_number }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <p class="text-sm font-bold text-text-primary">{{ $invoice->client->name ?? 'N/A' }}</p>
                        <p class="text-xs text-text-muted">{{ $invoice->project->name ?? 'N/A' }}</p>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-text-primary font-medium">
                        ${{ number_format($invoice->total_amount, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-text-muted text-sm border-l border-gray-100">
                        <p>Issue: {{ $invoice->issue_date ? $invoice->issue_date->format('M d, Y') : 'N/A' }}</p>
                        <p class="font-bold {{ $invoice->isOverdue() ? 'text-red-500' : '' }}">Due:
                            {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}
                        </p>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : ($invoice->status === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('invoices.show', $invoice->id) }}" wire:navigate
                                class="text-xs text-primary hover:text-indigo-900 border border-primary/20 px-3 py-1 rounded">View</a>
                            <a href="{{ route('invoices.edit', $invoice->id) }}" wire:navigate
                                class="text-xs text-secondary hover:text-secondary-dark border border-secondary/20 px-3 py-1 rounded">Edit</a>
                            <button type="button"
                                class="text-xs text-red-600 hover:text-red-900 border border-red-200 px-3 py-1 rounded"
                                x-on:click.prevent="$dispatch('open-modal', 'confirm-invoice-deletion-{{ $invoice->id }}')">
                                Delete
                            </button>
                        </div>

                        <x-modal name="confirm-invoice-deletion-{{ $invoice->id }}">
                            <div class="p-6">
                                <h2 class="text-lg font-medium text-text-primary">Delete Invoice</h2>
                                <p class="mt-1 text-sm text-text-muted">Are you sure you want to delete this invoice? This
                                    action cannot be undone.</p>
                                <div class="mt-6 flex justify-end gap-3 text-left">
                                    <x-button color="outline" x-on:click="$dispatch('close')">Cancel</x-button>
                                    <x-button color="danger" wire:click="delete('{{ $invoice->id }}')"
                                        x-on:click="$dispatch('close')">Delete</x-button>
                                </div>
                            </div>
                        </x-modal>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-text-muted">
                        No invoices found. <a href="{{ route('invoices.create') }}" class="text-primary font-medium">Create
                            your first invoice?</a>
                    </td>
                </tr>
            @endforelse
        </x-table>
    </x-card>

    <div class="mt-4">
        {{ $invoices->links() }}
    </div>
</x-app-container>