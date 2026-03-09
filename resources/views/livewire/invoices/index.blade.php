<x-app-container>
    <x-page-header title="Invoicing & Billing">
        <x-button color="outline" class="mr-2">Generate Statement</x-button>
        <x-button color="primary">Create Invoice</x-button>
    </x-page-header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <x-card class="border-l-4 border-gray-300">
            <h4 class="text-sm font-semibold text-text-muted">Draft</h4>
            <p class="text-2xl font-bold text-gray-700 mt-1">$4,500.00</p>
            <p class="text-xs text-gray-400 mt-1">3 Invoices</p>
        </x-card>
        <x-card class="border-l-4 border-yellow-400">
            <h4 class="text-sm font-semibold text-text-muted">Pending/Sent</h4>
            <p class="text-2xl font-bold text-yellow-600 mt-1">$12,850.50</p>
            <p class="text-xs text-yellow-500 mt-1">8 Invoices (2 Due Soon)</p>
        </x-card>
        <x-card class="border-l-4 border-green-500">
            <h4 class="text-sm font-semibold text-text-muted">Paid (Last 30 Days)</h4>
            <p class="text-2xl font-bold text-green-600 mt-1">$45,200.00</p>
            <p class="text-xs text-green-500 mt-1">15 Invoices</p>
        </x-card>
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

            @foreach([['INV-2026-001', 'Draft', 'gray'], ['INV-2026-002', 'Sent', 'yellow'], ['INV-2026-003', 'Overdue', 'red'], ['INV-2026-004', 'Paid', 'green']] as $idx => $inv)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-bold text-primary">{{$inv[0]}}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <p class="text-sm font-bold text-text-primary">Acme Corp</p>
                        <p class="text-xs text-text-muted">Website Redesign</p>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-text-primary font-medium">
                        ${{ number_format(($idx + 2) * 1500, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-text-muted text-sm border-l border-gray-100">
                        <p>Issue: {{ date('M d, Y') }}</p>
                        <p class="font-bold {{ $inv[2] == 'red' ? 'text-red-500' : '' }}">Due:
                            {{ date('M d, Y', strtotime('+30 days')) }}</p>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{$inv[2]}}-100 text-{{$inv[2]}}-800">
                            {{$inv[1]}}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <x-button color="outline" class="text-xs px-2 py-1">View</x-button>
                    </td>
                </tr>
            @endforeach
        </x-table>
    </x-card>
</x-app-container>