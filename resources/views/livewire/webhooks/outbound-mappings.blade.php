<x-app-container>
    <x-page-header title="Outbound Webhook Mappings" />

    @include('livewire.webhooks._nav')

    @if (session()->has('success'))
        <div class="mb-4 rounded-md bg-green-100 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 rounded-md bg-red-100 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    @if (! $hasTable)
        <x-card>
            <p class="text-sm text-text-muted">Run migrations to enable outbound mapping management.</p>
        </x-card>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <x-card class="lg:col-span-1">
                <h3 class="text-base font-semibold text-text-primary mb-4">{{ $mappingId ? 'Edit Outbound Mapping' : 'Create Outbound Mapping' }}</h3>
                <form wire:submit.prevent="save" class="space-y-4">
                    <div>
                        <label class="text-xs font-semibold text-text-muted uppercase">Name</label>
                        <input wire:model.defer="name" type="text" class="mt-1 w-full rounded-md border-gray-300 text-sm" />
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-text-muted uppercase">Internal Field</label>
                        <input wire:model.defer="source_key" type="text" class="mt-1 w-full rounded-md border-gray-300 text-sm" />
                        @error('source_key') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-text-muted uppercase">Payload Key</label>
                        <input wire:model.defer="target_key" type="text" class="mt-1 w-full rounded-md border-gray-300 text-sm" />
                        @error('target_key') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-text-muted uppercase">Transform Rule</label>
                        <input wire:model.defer="transform_rule" type="text" class="mt-1 w-full rounded-md border-gray-300 text-sm" placeholder="none, lowercase, format_currency" />
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-text-muted uppercase">Outbound Webhook</label>
                        <select wire:model.defer="webhook_id" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                            <option value="">Any outbound webhook</option>
                            @foreach($outboundWebhooks as $webhook)
                                <option value="{{ $webhook->id }}">{{ $webhook->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <label class="inline-flex items-center gap-2 text-sm text-text-muted">
                        <input wire:model.defer="active" type="checkbox" class="rounded border-gray-300" /> Active
                    </label>

                    <div class="flex gap-2">
                        <button type="submit" class="rounded-md bg-primary px-4 py-2 text-xs font-semibold text-white">Save</button>
                        @if($mappingId)
                            <button type="button" wire:click="resetForm" class="rounded-md bg-gray-100 px-4 py-2 text-xs font-semibold text-text-muted">Cancel</button>
                        @endif
                    </div>
                </form>
            </x-card>

            <x-card class="lg:col-span-2">
                <h3 class="text-base font-semibold text-text-primary mb-4">Outbound Mapping Rules</h3>
                <div class="overflow-x-auto rounded-lg border border-gray-100">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-text-muted uppercase">Name</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-text-muted uppercase">Internal Field</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-text-muted uppercase">Payload Key</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-text-muted uppercase">Transform</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-text-muted uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($mappings as $mapping)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-semibold text-text-primary">{{ $mapping->name }}</td>
                                    <td class="px-4 py-3 text-sm text-text-muted">{{ $mapping->source_key }}</td>
                                    <td class="px-4 py-3 text-sm text-text-muted">{{ $mapping->target_key }}</td>
                                    <td class="px-4 py-3 text-sm text-text-muted">{{ $mapping->transform_rule ?: 'none' }}</td>
                                    <td class="px-4 py-3 text-right space-x-2">
                                        <button wire:click="edit('{{ $mapping->id }}')" class="text-xs font-semibold text-primary">Edit</button>
                                        <button wire:click="delete('{{ $mapping->id }}')" wire:confirm="Delete this mapping?" class="text-xs font-semibold text-red-600">Delete</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-4 py-4 text-sm text-text-muted" colspan="5">No outbound mappings configured.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    @endif
</x-app-container>
