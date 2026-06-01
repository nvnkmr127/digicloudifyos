<x-app-container>
    <x-page-header title="Clients">
        <x-button color="primary" href="{{ route('clients.create') }}" wire:navigate>
            Create Client
        </x-button>
    </x-page-header>

    <x-card>
        <x-toolbar class="mb-6" variant="subtle">
            <x-slot name="left">
                <x-input wire:model.live.debounce.300ms="search" type="search" placeholder="Search clients…" aria-label="Search clients" class="w-full sm:w-96" />
            </x-slot>
        </x-toolbar>

        <div wire:loading class="mb-4 text-sm text-text-muted">Loading…</div>

        <x-table>
            <x-slot name="header">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left">Client Identity</th>
                    <th scope="col" class="px-6 py-3 text-left">Email</th>
                    <th scope="col" class="px-6 py-3 text-left">Industry</th>
                    <th scope="col" class="px-6 py-3 text-left">Onboarding</th>
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
                                class="h-10 w-10 rounded-lg bg-primary-soft flex items-center justify-center text-primary font-bold mr-3">
                                {{ substr($client->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="text-sm font-medium text-text-primary">{{ $client->name }}</div>
                                <div class="text-xs text-text-muted">
                                    @if($client->website_url)
                                        <span>{{ $client->website_url }}</span>
                                    @endif
                                    @if($client->website_url && $client->phone)
                                        <span class="mx-1">•</span>
                                    @endif
                                    @if($client->phone)
                                        <span>{{ $client->phone }}</span>
                                    @endif
                                    @if(! $client->website_url && ! $client->phone)
                                        <span>N/A</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted">
                        {{ $client->email ?: 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted">
                        {{ $client->industry ?: 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-col gap-1 w-24">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-semibold text-gray-400 capitalize">{{ $client->onboarding_progress }}%</span>
                            </div>
                            <div class="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                                @php
                                    $progress = $client->onboarding_progress;
                                    $color = $progress < 30 ? 'bg-red-500' : ($progress < 70 ? 'bg-amber-500' : 'bg-green-500');
                                @endphp
                                <div x-data="{ progress: @js($progress) }" class="h-full {{ $color }} transition-all duration-500" :style="`width: ${progress}%`"></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <x-status-badge :status="$client->status" type="client" />
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-text-primary">
                        {{ $client->campaigns_count }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('clients.integrations', $client->id) }}" wire:navigate
                            class="text-primary hover:text-indigo-900 mr-3">Integrations</a>
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
                    <td colspan="7" class="px-6">
                        <x-empty-state
                            title="No clients found"
                            description="Try adjusting your search, or create your first client."
                        >
                            <x-slot name="actions">
                                <x-button href="{{ route('clients.create') }}" wire:navigate>
                                    Create Client
                                </x-button>
                            </x-slot>
                        </x-empty-state>
                    </td>
                </tr>
            @endforelse
        </x-table>

        <div class="mt-4">
            {{ $clients->links() }}
        </div>
    </x-card>
</x-app-container>
