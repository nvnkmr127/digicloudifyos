<x-app-container>
    <x-page-header title="Clients">
        <x-button color="primary" href="{{ route('clients.create') }}" wire:navigate>
            Create Client
        </x-button>
    </x-page-header>

    <x-card>
        <!-- Search -->
        <div class="mb-6 max-w-md">
            <x-input wire:model.live="search" type="text" placeholder="Search by name, email or industry..." />
        </div>

        <x-table>
            <x-slot name="header">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left">Client Identity</th>
                    <th scope="col" class="px-6 py-3 text-left">Email</th>
                    <th scope="col" class="px-6 py-3 text-left">Industry</th>
                    <th scope="col" class="px-6 py-3 text-left">Status</th>
                    <th scope="col" class="px-6 py-3 text-center">Campaigns</th>
                    <th scope="col" class="px-6 py-3 text-right">Actions</th>
                </tr>
            </x-slot>

            @forelse($clients as $client)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div
                                class="h-10 w-10 rounded-lg bg-indigo-50 flex items-center justify-center text-primary font-bold mr-3">
                                {{ substr($client->name, 0, 1) }}
                            </div>
                            <div class="text-sm font-medium text-text-primary">{{ $client->name }}</div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted">
                        {{ $client->email ?: 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted">
                        {{ $client->industry ?: 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $statusColors = [
                                'ACTIVE' => 'bg-green-100 text-green-700',
                                'INACTIVE' => 'bg-gray-100 text-gray-600',
                                'ARCHIVED' => 'bg-red-100 text-red-700',
                            ];
                            $color = $statusColors[$client->status] ?? 'bg-gray-100 text-gray-600';
                        @endphp
                        <span class="{{ $color }} px-2 py-1 rounded-full text-xs font-semibold">
                            {{ $client->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-text-primary">
                        {{ $client->campaigns_count }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('clients.edit', $client->id) }}" wire:navigate
                            class="text-primary hover:text-indigo-900 mr-3">Edit</a>
                        <button type="button" class="text-red-600 hover:text-red-900"
                            x-on:click.prevent="$dispatch('open-modal', 'confirm-client-deletion-{{ $client->id }}')">
                            Delete
                        </button>

                        <x-modal name="confirm-client-deletion-{{ $client->id }}">
                            <div class="p-6">
                                <h2 class="text-lg font-medium text-text-primary">
                                    Delete Client
                                </h2>
                                <p class="mt-1 text-sm text-text-muted">
                                    Are you sure you want to delete this client? All associated data will be lost.
                                </p>
                                <div class="mt-6 flex justify-end gap-3 text-left">
                                    <x-button color="outline" x-on:click="$dispatch('close')">Cancel</x-button>
                                    <x-button color="danger" wire:click="delete('{{ $client->id }}')"
                                        x-on:click="$dispatch('close')">Delete</x-button>
                                </div>
                            </div>
                        </x-modal>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-text-muted">
                        No clients found. <a href="{{ route('clients.create') }}" class="text-primary font-medium">Add your
                            first client?</a>
                    </td>
                </tr>
            @endforelse
        </x-table>

        <div class="mt-4">
            {{ $clients->links() }}
        </div>
    </x-card>
</x-app-container>