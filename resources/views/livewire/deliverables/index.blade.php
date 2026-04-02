<x-app-container>
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <x-page-header title="Deliverables" />
        <div class="flex items-center gap-3">
            <x-input type="date" wire:model.live="date" aria-label="Date" class="w-44" />
            <x-select wire:model.live="status" aria-label="Status" class="w-44">
                <option value="">All</option>
                <option value="generated">Generated</option>
                <option value="scheduled">Scheduled</option>
                <option value="failed">Failed</option>
            </x-select>
        </div>
    </div>

    <x-card class="mt-6">
        <div class="text-sm font-black text-gray-900">Generated Reports</div>
        <div class="text-xs text-gray-500">Automated weekly/monthly deliverables stored in-app.</div>

        <div class="mt-6 space-y-2">
            @forelse($items as $d)
                <div class="p-4 border border-gray-100 rounded-2xl bg-white">
                    <div class="flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <div class="text-sm font-bold text-gray-900 truncate">{{ $d->title }}</div>
                            <div class="text-xs text-gray-500">
                                Client: {{ $d->client->name ?? 'Client' }} • Date: {{ $d->deliverable_date->toDateString() }}
                            </div>
                        </div>
                        <div class="text-xs font-black text-gray-600 uppercase tracking-widest">{{ $d->status }}</div>
                    </div>

                    @if($d->status === 'generated' && $d->body_html)
                        <div class="mt-3">
                            <details>
                                <summary class="text-sm font-bold text-primary cursor-pointer">View</summary>
                                <div class="mt-3 border border-gray-100 rounded-2xl overflow-hidden">
                                    <iframe title="{{ $d->title }}" srcdoc="{{ e($d->body_html) }}" class="w-full h-[520px]"></iframe>
                                </div>
                            </details>
                        </div>
                    @endif

                    @if($d->status === 'failed' && $d->error_message)
                        <div class="mt-2 text-xs text-red-600">{{ $d->error_message }}</div>
                    @endif
                </div>
            @empty
                <div class="p-6 border border-dashed border-gray-200 rounded-2xl text-sm text-gray-600">
                    No deliverables found.
                </div>
            @endforelse
        </div>
    </x-card>
</x-app-container>
