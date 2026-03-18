<x-app-container>
    <x-page-header title="Webhooks" />

    @include('livewire.webhooks._nav')

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">
        <x-card>
            <p class="text-xs uppercase text-text-muted font-semibold">Outbound Webhooks</p>
            <p class="text-2xl font-bold text-text-primary mt-2">{{ $outbound_count }}</p>
        </x-card>
        <x-card>
            <p class="text-xs uppercase text-text-muted font-semibold">Inbound Webhooks</p>
            <p class="text-2xl font-bold text-text-primary mt-2">{{ $inbound_count }}</p>
        </x-card>
        <x-card>
            <p class="text-xs uppercase text-text-muted font-semibold">Inbound Mappings</p>
            <p class="text-2xl font-bold text-text-primary mt-2">{{ $inbound_mappings_count }}</p>
        </x-card>
        <x-card>
            <p class="text-xs uppercase text-text-muted font-semibold">Outbound Mappings</p>
            <p class="text-2xl font-bold text-text-primary mt-2">{{ $outbound_mappings_count }}</p>
        </x-card>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <x-card>
            <h3 class="text-base font-semibold text-text-primary">Inbound Webhooks</h3>
            <p class="text-sm text-text-muted mt-2">Manage endpoints that receive payloads from external systems.</p>
            <a href="{{ route('webhooks.inbound') }}" class="inline-block mt-4 text-sm font-semibold text-primary hover:underline">Open Inbound</a>
        </x-card>

        <x-card>
            <h3 class="text-base font-semibold text-text-primary">Outbound Webhooks</h3>
            <p class="text-sm text-text-muted mt-2">Configure destinations where DC OS sends event notifications.</p>
            <a href="{{ route('webhooks.outbound') }}" class="inline-block mt-4 text-sm font-semibold text-primary hover:underline">Open Outbound</a>
        </x-card>

        <x-card>
            <h3 class="text-base font-semibold text-text-primary">Webhook API</h3>
            <p class="text-sm text-text-muted mt-2">Reference authentication, signatures, and endpoint contracts.</p>
            <a href="{{ route('webhooks.api') }}" class="inline-block mt-4 text-sm font-semibold text-primary hover:underline">Open API</a>
        </x-card>

        <x-card>
            <h3 class="text-base font-semibold text-text-primary">Inbound Mappings</h3>
            <p class="text-sm text-text-muted mt-2">Map inbound payload fields into internal entities and workflows.</p>
            <a href="{{ route('webhooks.mappings.inbound') }}" class="inline-block mt-4 text-sm font-semibold text-primary hover:underline">Open Inbound Mappings</a>
        </x-card>

        <x-card>
            <h3 class="text-base font-semibold text-text-primary">Outbound Mappings</h3>
            <p class="text-sm text-text-muted mt-2">Control outbound payload schema and transform internal events.</p>
            <a href="{{ route('webhooks.mappings.outbound') }}" class="inline-block mt-4 text-sm font-semibold text-primary hover:underline">Open Outbound Mappings</a>
        </x-card>
    </div>

    <x-card class="mt-6">
        <h3 class="text-base font-semibold text-text-primary mb-4">Recent Outbound Deliveries</h3>
        <div class="overflow-x-auto rounded-lg border border-gray-100">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-text-muted uppercase">Event</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-text-muted uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-text-muted uppercase">Delivered</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($recent_deliveries as $delivery)
                        <tr>
                            <td class="px-4 py-3 text-sm text-text-primary">{{ $delivery->event }}</td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 rounded text-xs font-semibold {{ $delivery->isSuccessful() ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $delivery->response_status ?: 'FAILED' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-text-muted">{{ $delivery->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-4 text-sm text-text-muted" colspan="3">No deliveries yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-app-container>
