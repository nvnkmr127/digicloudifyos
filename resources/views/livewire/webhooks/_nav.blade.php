<div class="mb-6 overflow-x-auto">
    <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1">
        <a href="{{ route('webhooks.index') }}" class="px-3 py-2 text-xs font-semibold rounded-md {{ request()->routeIs('webhooks.index') ? 'bg-primary text-white' : 'text-text-muted hover:bg-gray-50' }}">Webhooks</a>
        <a href="{{ route('webhooks.inbound') }}" class="px-3 py-2 text-xs font-semibold rounded-md {{ request()->routeIs('webhooks.inbound') ? 'bg-primary text-white' : 'text-text-muted hover:bg-gray-50' }}">Inbound</a>
        <a href="{{ route('webhooks.outbound') }}" class="px-3 py-2 text-xs font-semibold rounded-md {{ request()->routeIs('webhooks.outbound') ? 'bg-primary text-white' : 'text-text-muted hover:bg-gray-50' }}">Outbound</a>
        <a href="{{ route('webhooks.api') }}" class="px-3 py-2 text-xs font-semibold rounded-md {{ request()->routeIs('webhooks.api') ? 'bg-primary text-white' : 'text-text-muted hover:bg-gray-50' }}">API</a>
        <a href="{{ route('webhooks.mappings.inbound') }}" class="px-3 py-2 text-xs font-semibold rounded-md {{ request()->routeIs('webhooks.mappings.inbound') ? 'bg-primary text-white' : 'text-text-muted hover:bg-gray-50' }}">Inbound Mappings</a>
        <a href="{{ route('webhooks.mappings.outbound') }}" class="px-3 py-2 text-xs font-semibold rounded-md {{ request()->routeIs('webhooks.mappings.outbound') ? 'bg-primary text-white' : 'text-text-muted hover:bg-gray-50' }}">Outbound Mappings</a>
    </div>
</div>
