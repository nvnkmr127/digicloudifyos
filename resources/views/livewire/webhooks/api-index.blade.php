<x-app-container>
    <x-page-header title="Webhook API" />

    @include('livewire.webhooks._nav')

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card>
            <h3 class="text-base font-semibold text-text-primary">Authentication</h3>
            <p class="text-sm text-text-muted mt-2">Use bearer tokens for authenticated API calls where applicable.</p>
            <div class="mt-4 rounded-md bg-gray-50 p-3 text-xs text-text-muted">Authorization: Bearer &lt;token&gt;</div>
        </x-card>

        <x-card>
            <h3 class="text-base font-semibold text-text-primary">Signature Verification</h3>
            <p class="text-sm text-text-muted mt-2">Inbound providers should be verified using HMAC signatures.</p>
            <div class="mt-4 rounded-md bg-gray-50 p-3 text-xs text-text-muted">X-Hub-Signature-256: sha256=&lt;signature&gt;</div>
        </x-card>

        <x-card>
            <h3 class="text-base font-semibold text-text-primary">Inbound Callback Endpoint</h3>
            <p class="text-sm text-text-muted mt-2">Use this endpoint for Facebook lead webhook subscriptions.</p>
            <div class="mt-4 rounded-md bg-gray-50 p-3 text-xs text-text-muted">POST {{ url('webhooks/facebook') }}</div>
        </x-card>

        <x-card>
            <h3 class="text-base font-semibold text-text-primary">Verification Endpoint</h3>
            <p class="text-sm text-text-muted mt-2">Facebook validation request endpoint.</p>
            <div class="mt-4 rounded-md bg-gray-50 p-3 text-xs text-text-muted">GET {{ url('webhooks/facebook') }}</div>
        </x-card>

        <x-card class="lg:col-span-2">
            <h3 class="text-base font-semibold text-text-primary">API Areas</h3>
            <ul class="mt-3 space-y-2 text-sm text-text-muted">
                <li>Inbound endpoint registration and validation.</li>
                <li>Outbound destination setup and retry behavior.</li>
                <li>Payload mapping and transformation rules.</li>
                <li>Delivery logs and error diagnostics.</li>
            </ul>
        </x-card>
    </div>
</x-app-container>
