<x-app-container>
    <x-page-header title="Outbound Webhooks" />

    @include('livewire.webhooks._nav')

    @if (session()->has('success'))
        <div class="mb-4 rounded-md bg-green-100 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 rounded-md bg-red-100 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    @if (! $hasTable)
        <x-card>
            <p class="text-sm text-text-muted">Run migrations to enable outbound webhooks management.</p>
        </x-card>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <x-card class="lg:col-span-1">
                <h3 class="text-base font-semibold text-text-primary mb-4">{{ $webhookId ? 'Edit Outbound Webhook' : 'Create Outbound Webhook' }}</h3>
                <form wire:submit.prevent="save" class="space-y-4">
                    <div>
                        <label class="text-xs font-semibold text-text-muted uppercase">Name</label>
                        <input wire:model.defer="name" type="text" class="mt-1 w-full rounded-md border-gray-300 text-sm" />
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-text-muted uppercase">URL</label>
                        <input wire:model.defer="url" type="url" class="mt-1 w-full rounded-md border-gray-300 text-sm" placeholder="https://example.com/webhook" />
                        @error('url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-text-muted uppercase">Events (comma separated)</label>
                        <input wire:model.defer="events" type="text" class="mt-1 w-full rounded-md border-gray-300 text-sm" />
                        @error('events') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-text-muted uppercase">Secret (optional)</label>
                        <input wire:model.defer="secret" type="text" class="mt-1 w-full rounded-md border-gray-300 text-sm" />
                    </div>

                    <label class="inline-flex items-center gap-2 text-sm text-text-muted">
                        <input wire:model.defer="active" type="checkbox" class="rounded border-gray-300" /> Active
                    </label>

                    <div class="flex gap-2">
                        <button type="submit" class="rounded-md bg-primary px-4 py-2 text-xs font-semibold text-white">Save</button>
                        @if($webhookId)
                            <button type="button" wire:click="resetForm" class="rounded-md bg-gray-100 px-4 py-2 text-xs font-semibold text-text-muted">Cancel</button>
                        @endif
                    </div>
                </form>
            </x-card>

            <x-card class="lg:col-span-2">
                <h3 class="text-base font-semibold text-text-primary mb-4">Outbound Destinations</h3>
                <div class="overflow-x-auto rounded-lg border border-gray-100">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-text-muted uppercase">Name</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-text-muted uppercase">URL</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-text-muted uppercase">Events</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-text-muted uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($webhooks as $webhook)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-semibold text-text-primary">{{ $webhook->name }}</td>
                                    <td class="px-4 py-3 text-xs text-text-muted">{{ $webhook->url }}</td>
                                    <td class="px-4 py-3 text-xs text-text-muted">{{ implode(', ', $webhook->events ?? []) }}</td>
                                    <td class="px-4 py-3 text-right space-x-2">
                                        <button wire:click="edit('{{ $webhook->id }}')" class="text-xs font-semibold text-primary">Edit</button>
                                        <button wire:click="delete('{{ $webhook->id }}')" wire:confirm="Delete this webhook?" class="text-xs font-semibold text-red-600">Delete</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-4 text-sm text-text-muted">No outbound webhooks configured.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        @if($hasDeliveryTable)
            <x-card class="mt-6">
                <h3 class="text-base font-semibold text-text-primary mb-4">Recent Deliveries</h3>
                <div class="overflow-x-auto rounded-lg border border-gray-100">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-text-muted uppercase">Event</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-text-muted uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-text-muted uppercase">Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($recentDeliveries as $delivery)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-text-primary">{{ $delivery->event }}</td>
                                    <td class="px-4 py-3 text-sm">{{ $delivery->response_status ?: 'FAILED' }}</td>
                                    <td class="px-4 py-3 text-sm text-text-muted">{{ $delivery->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-4 text-sm text-text-muted">No delivery logs yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        @endif
    @endif
</x-app-container>
