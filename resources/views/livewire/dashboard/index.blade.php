<x-app-container>
    <x-page-header title="Dashboard">
        <x-button color="primary">Create Report</x-button>
    </x-page-header>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Stat Card 1 -->
        <x-card>
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-indigo-100 text-primary mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                        </path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-text-muted">Total Clients</p>
                    <p class="text-2xl font-semibold text-text-primary">{{ number_format($total_clients) }}</p>
                </div>
            </div>
        </x-card>

        <!-- Stat Card 2 -->
        <x-card>
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-accent mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-text-muted">Campaigns</p>
                    <p class="text-2xl font-semibold text-text-primary">{{ number_format($campaigns_count) }}</p>
                </div>
            </div>
        </x-card>

        <!-- Stat Card 3 -->
        <x-card>
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-secondary mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-text-muted">Projects</p>
                    <p class="text-2xl font-semibold text-text-primary">{{ number_format($projects_count) }}</p>
                </div>
            </div>
        </x-card>

        <!-- Stat Card 4 -->
        <x-card>
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                        </path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-text-muted">Revenue</p>
                    <p class="text-2xl font-semibold text-text-primary">${{ number_format($total_revenue, 2) }}</p>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Recent Activity Example -->
    <x-card>
        <h2 class="text-lg font-semibold text-text-primary mb-4">Recent Activity</h2>
        <x-table>
            <x-slot name="header">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left">Document</th>
                    <th scope="col" class="px-6 py-3 text-left">Client</th>
                    <th scope="col" class="px-6 py-3 text-left">Date</th>
                    <th scope="col" class="px-6 py-3 text-right">Status</th>
                </tr>
            </x-slot>

            @forelse($recent_documents as $doc)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $doc->invoice_number }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $doc->client->name ?? 'Unknown' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $doc->created_at->diffForHumans() }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ strtolower($doc->status) === 'paid' ? 'green' : (strtolower($doc->status) === 'sent' ? 'yellow' : 'gray') }}-100 text-{{ strtolower($doc->status) === 'paid' ? 'green' : (strtolower($doc->status) === 'sent' ? 'yellow' : 'gray') }}-800">{{ ucfirst($doc->status) }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No recent documents found.</td>
                </tr>
            @endforelse
        </x-table>
    </x-card>
</x-app-container>