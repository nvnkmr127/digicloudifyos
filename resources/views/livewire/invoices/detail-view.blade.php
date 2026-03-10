<x-app-container>
    <div class="mb-4 flex justify-between items-center">
        <a href="{{ route('invoices.index') }}" wire:navigate class="text-sm text-text-muted hover:text-primary">
            &larr; Back to Invoices
        </a>
        <div class="flex gap-2">
            <x-button color="outline" onclick="window.print()">Print</x-button>
            <x-button color="secondary" href="{{ route('invoices.edit', $invoice) }}" wire:navigate>Edit</x-button>
        </div>
    </div>

    <x-card
        class="bg-white shadow-lg p-8 max-w-4xl mx-auto border-t-8 border-primary print:shadow-none print:border-none">
        <!-- Invoice Header -->
        <div class="flex justify-between items-start mb-12">
            <div>
                <h1 class="text-3xl font-bold text-primary">{{ config('app.name') }}</h1>
                <p class="text-text-muted text-sm mt-1">123 Business Street, Suite 456<br>San Francisco, CA 94103</p>
            </div>
            <div class="text-right">
                <h2 class="text-4xl font-light text-text-primary uppercase tracking-wider mb-2">Invoice</h2>
                <p class="text-text-muted font-medium">#{{ $invoice->invoice_number }}</p>
            </div>
        </div>

        <!-- Addresses -->
        <div class="grid grid-cols-2 gap-12 mb-12">
            <div>
                <h3 class="text-xs font-bold text-text-muted uppercase tracking-widest mb-3">Bill To:</h3>
                <p class="text-lg font-bold text-text-primary">{{ $invoice->client->name }}</p>
                @if($invoice->client->email)
                    <p class="text-text-muted">{{ $invoice->client->email }}</p>
                @endif
                @if($invoice->project)
                    <p class="text-sm text-primary mt-2">Project: {{ $invoice->project->name }}</p>
                @endif
            </div>
            <div class="text-right">
                <div class="space-y-2">
                    <p><span class="text-text-muted text-xs font-bold uppercase mr-2 text-left inline-block w-24">Issue
                            Date:</span> {{ $invoice->issue_date ? $invoice->issue_date->format('M d, Y') : '-' }}</p>
                    <p><span class="text-text-muted text-xs font-bold uppercase mr-2 text-left inline-block w-24">Due
                            Date:</span> {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : '-' }}</p>
                    <p><span
                            class="text-text-muted text-xs font-bold uppercase mr-2 text-left inline-block w-24">Status:</span>
                        <span
                            class="px-2 py-0.5 rounded-full text-xs font-bold 
                            {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : ($invoice->status === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ strtoupper($invoice->status) }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="mb-12">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b-2 border-primary/10">
                        <th class="py-4 font-bold text-text-primary">Description</th>
                        <th class="py-4 font-bold text-text-primary text-center w-24">Qty</th>
                        <th class="py-4 font-bold text-text-primary text-right w-32">Unit Price</th>
                        <th class="py-4 font-bold text-text-primary text-right w-32">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($invoice->items as $item)
                        <tr>
                            <td class="py-4 text-text-primary font-medium">{{ $item->description }}</td>
                            <td class="py-4 text-text-muted text-center">{{ $item->quantity }}</td>
                            <td class="py-4 text-text-muted text-right">${{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-4 text-text-primary font-bold text-right">${{ number_format($item->amount, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="flex justify-end">
            <div class="w-64 space-y-3">
                <div class="flex justify-between text-text-muted">
                    <span>Subtotal</span>
                    <span>${{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                @if($invoice->tax_amount > 0)
                    <div class="flex justify-between text-text-muted">
                        <span>Tax</span>
                        <span>${{ number_format($invoice->tax_amount, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between border-t border-gray-100 pt-3 text-xl font-bold text-primary">
                    <span>Total</span>
                    <span>${{ number_format($invoice->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Notes -->
        @if($invoice->notes || $invoice->payment_terms)
            <div class="mt-16 pt-8 border-t border-gray-100 grid grid-cols-2 gap-8">
                @if($invoice->notes)
                    <div>
                        <h4 class="text-xs font-bold text-text-muted uppercase tracking-widest mb-2">Notes:</h4>
                        <p class="text-sm text-text-muted">{{ $invoice->notes }}</p>
                    </div>
                @endif
                @if($invoice->payment_terms)
                    <div>
                        <h4 class="text-xs font-bold text-text-muted uppercase tracking-widest mb-2">Payment Terms:</h4>
                        <p class="text-sm text-text-muted">{{ $invoice->payment_terms }}</p>
                    </div>
                @endif
            </div>
        @endif
    </x-card>
</x-app-container>