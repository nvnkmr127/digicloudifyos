<x-app-container>
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <x-page-header title="SEO & Content Intelligence" />
        <div class="w-56">
            <x-input type="date" wire:model.live="date" />
        </div>
    </div>

    <x-card class="mt-6">
        <div class="text-sm font-black text-gray-900">Opportunities</div>
        <div class="text-xs text-gray-500">Search Console-driven opportunities (CTR, page-2 queries, content decay).</div>

        <div class="mt-6 space-y-2">
            @forelse($items as $o)
                <div class="p-4 border border-gray-100 rounded-2xl bg-white">
                    <div class="flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <div class="text-sm font-bold text-gray-900 truncate">{{ $o->title }}</div>
                            <div class="text-xs text-gray-500">
                                Client: {{ $o->client->name ?? 'Client' }} • Type: {{ $o->opportunity_type }}
                            </div>
                        </div>
                        <div class="text-xs font-black text-gray-600 uppercase tracking-widest">
                            {{ $o->severity }}
                        </div>
                    </div>

                    @if(is_array($o->payload) && (isset($o->payload['queries']) || isset($o->payload['pages'])))
                        <div class="mt-3 text-xs text-gray-700">
                            @if(isset($o->payload['queries']))
                                Top queries: {{ collect($o->payload['queries'])->take(5)->pluck('query')->implode(', ') }}
                            @endif
                            @if(isset($o->payload['pages']))
                                Top pages: {{ collect($o->payload['pages'])->take(5)->pluck('page')->implode(', ') }}
                            @endif
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-6 border border-dashed border-gray-200 rounded-2xl text-sm text-gray-600">
                    No SEO opportunities for this date.
                </div>
            @endforelse
        </div>
    </x-card>

    <x-card class="mt-6">
        <div class="text-sm font-black text-gray-900">Google Business Profile (Local SEO)</div>
        <div class="text-xs text-gray-500">Aggregated performance signals from connected locations.</div>

        <div class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-4">
            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                <div class="text-xs text-gray-500">Impressions</div>
                <div class="text-xl font-black text-gray-900">{{ $gbpTotals['impressions'] }}</div>
            </div>
            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                <div class="text-xs text-gray-500">Calls</div>
                <div class="text-xl font-black text-gray-900">{{ $gbpTotals['call_clicks'] }}</div>
            </div>
            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                <div class="text-xs text-gray-500">Website Clicks</div>
                <div class="text-xl font-black text-gray-900">{{ $gbpTotals['website_clicks'] }}</div>
            </div>
            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                <div class="text-xs text-gray-500">Directions</div>
                <div class="text-xl font-black text-gray-900">{{ $gbpTotals['directions_requests'] }}</div>
            </div>
        </div>

        <div class="mt-6">
            <div class="text-xs font-black text-gray-400 uppercase tracking-widest">Search Keywords @if($gbpMonth) ({{ $gbpMonth }}) @endif</div>
            <div class="mt-3 space-y-2">
                @forelse($gbpKeywords as $k)
                    <div class="p-3 border border-gray-100 rounded-2xl bg-white flex items-center justify-between gap-4">
                        <div class="text-sm font-bold text-gray-900 truncate">{{ $k->keyword }}</div>
                        <div class="text-sm font-black text-gray-900">{{ (int) $k->impressions }}</div>
                    </div>
                @empty
                    <div class="p-6 border border-dashed border-gray-200 rounded-2xl text-sm text-gray-600">
                        No GBP keyword data yet. Connect Google Business Profile and wait for the monthly sync.
                    </div>
                @endforelse
            </div>
        </div>
    </x-card>

    <x-card class="mt-6">
        <div class="text-sm font-black text-gray-900">Site Audit Issues</div>
        <div class="text-xs text-gray-500">Lightweight technical and on-page checks (homepage + internal link sampling).</div>

        <div class="mt-6 space-y-2">
            @forelse($auditIssues as $i)
                <div class="p-4 border border-gray-100 rounded-2xl bg-white">
                    <div class="flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <div class="text-sm font-bold text-gray-900 truncate">{{ $i->title ?? $i->issue_type }}</div>
                            <div class="text-xs text-gray-500 truncate">
                                Client: {{ $i->audit->client->name ?? 'Client' }} • Type: {{ $i->issue_type }}
                                @if($i->url) • {{ $i->url }} @endif
                            </div>
                        </div>
                        <div class="text-xs font-black text-gray-600 uppercase tracking-widest">{{ $i->severity }}</div>
                    </div>
                </div>
            @empty
                <div class="p-6 border border-dashed border-gray-200 rounded-2xl text-sm text-gray-600">
                    No site audit issues for this date.
                </div>
            @endforelse
        </div>
    </x-card>
</x-app-container>
