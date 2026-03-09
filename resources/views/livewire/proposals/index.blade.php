<x-app-container>
    <x-page-header title="Proposals & Documents">
        <x-button color="outline" class="mr-2">Templates</x-button>
        <x-button color="primary">New Document</x-button>
    </x-page-header>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <x-card class="bg-blue-50 border-none p-4">
            <h4 class="text-sm font-semibold text-text-muted">Drafts</h4>
            <p class="text-2xl font-bold text-primary mt-2">{{ $drafts }}</p>
        </x-card>
        <x-card class="bg-yellow-50 border-none p-4">
            <h4 class="text-sm font-semibold text-text-muted">Sent</h4>
            <p class="text-2xl font-bold text-primary mt-2">{{ $sent }}</p>
        </x-card>
        <x-card class="bg-green-50 border-none p-4">
            <h4 class="text-sm font-semibold text-text-muted">Paid</h4>
            <p class="text-2xl font-bold text-primary mt-2">{{ $paid }}</p>
        </x-card>
        <x-card class="bg-red-50 border-none p-4">
            <h4 class="text-sm font-semibold text-text-muted">Overdue</h4>
            <p class="text-2xl font-bold text-primary mt-2">{{ $overdue }}</p>
        </x-card>
    </div>

    <x-card class="p-0">
        <div class="p-4 border-b border-gray-100 flex justify-between items-center">
            <div class="flex space-x-2">
                <x-input type="text" placeholder="Search documents..." class="w-64" />
                <x-select>
                    <option>All Statuses</option>
                    <option>Draft</option>
                    <option>Sent</option>
                    <option>Paid</option>
                </x-select>
            </div>
        </div>
        <x-table>
            <x-slot name="header">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">
                        Document Name</th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Client
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Status
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Value
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Date
                        Sent</th>
                    <th scope="col"
                        class="px-6 py-3 text-right text-xs font-medium text-text-muted uppercase tracking-wider">
                        Actions</th>
                </tr>
            </x-slot>
            <x-slot name="body">
                @forelse($invoices as $invoice)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-gray-400 mr-3" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                <span class="text-sm font-medium text-text-primary">{{ $invoice->invoice_number }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted">
                            {{ $invoice->client->name ?? 'Unknown' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="bg-{{ strtolower($invoice->status) === 'paid' ? 'green' : (strtolower($invoice->status) === 'sent' ? 'yellow' : 'gray') }}-100 text-{{ strtolower($invoice->status) === 'paid' ? 'green' : (strtolower($invoice->status) === 'sent' ? 'yellow' : 'gray') }}-800 text-xs px-2 py-1 rounded-full font-medium">{{ ucfirst($invoice->status) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted">
                            ${{ number_format($invoice->total_amount, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted">
                            {{ $invoice->issue_date ? $invoice->issue_date->format('M d, Y') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="#" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No documents found.</td>
                    </tr>
                @endforelse
            </x-slot>
        </x-table>
    </x-card>
</x-app-container>