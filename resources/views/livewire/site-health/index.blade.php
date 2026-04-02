<x-app-container>
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <x-page-header title="Site Health" />
        <div class="w-56">
            <x-input type="date" wire:model.live="date" />
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card>
            <div class="text-sm font-black text-gray-900">Page Speed</div>
            <div class="text-xs text-gray-500">Daily PageSpeed performance scores from Google PSI (if configured).</div>

            <div class="mt-4 space-y-2">
                @forelse($pagespeed as $p)
                    <div class="p-4 border border-gray-100 rounded-2xl bg-white">
                        <div class="flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <div class="text-sm font-bold text-gray-900 truncate">{{ $p->client->name ?? 'Client' }}</div>
                                <div class="text-xs text-gray-500 truncate">{{ $p->url }}</div>
                            </div>
                            <div class="text-right text-xs font-bold text-gray-700">
                                <div>M: {{ $p->performance_mobile ?? '—' }}</div>
                                <div>D: {{ $p->performance_desktop ?? '—' }}</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-6 border border-dashed border-gray-200 rounded-2xl text-sm text-gray-600">
                        No PageSpeed data for this date.
                    </div>
                @endforelse
            </div>
        </x-card>

        <x-card>
            <div class="text-sm font-black text-gray-900">Domain Expiry</div>
            <div class="text-xs text-gray-500">RDAP-based domain expiration checks.</div>

            <div class="mt-4 space-y-2">
                @forelse($domains as $d)
                    <div class="p-4 border border-gray-100 rounded-2xl bg-white">
                        <div class="flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <div class="text-sm font-bold text-gray-900 truncate">{{ $d->client->name ?? 'Client' }}</div>
                                <div class="text-xs text-gray-500 truncate">{{ $d->domain }}</div>
                                <div class="text-xs text-gray-500">
                                    Expires: {{ $d->expires_on?->toDateString() ?? '—' }}
                                </div>
                            </div>
                            <div class="text-right text-xs font-bold text-gray-700">
                                {{ $d->days_remaining ?? '—' }} days
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-6 border border-dashed border-gray-200 rounded-2xl text-sm text-gray-600">
                        No domain expiry data for this date.
                    </div>
                @endforelse
            </div>
        </x-card>
    </div>
</x-app-container>

