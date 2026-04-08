<div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
    <div class="flex flex-col gap-6 md:flex-row md:items-start md:justify-between mb-8">
        <div>
            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">
                <a class="hover:underline" href="{{ route('intelligence.overview') }}">Intelligence</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900 dark:text-gray-100">{{ $client->name }}</span>
            </div>
            <h1 class="text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Client Workspace</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                Snapshot window: {{ $start->toFormattedDateString() }} → {{ $end->toFormattedDateString() }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            <x-button variant="{{ $dateRange === '1d' ? 'primary' : 'outline' }}" wire:click="setDateRange('1d')">1d</x-button>
            <x-button variant="{{ $dateRange === '7d' ? 'primary' : 'outline' }}" wire:click="setDateRange('7d')">7d</x-button>
            <x-button variant="{{ $dateRange === '30d' ? 'primary' : 'outline' }}" wire:click="setDateRange('30d')">30d</x-button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Health</h2>
                    <div class="flex items-center gap-3">
                        <x-intelligence.health-score-ring :score="$client->current_health_score" size="md" />
                        <div class="text-right">
                            <div class="text-xs text-gray-500 dark:text-gray-400">Current score</div>
                            <div class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ $client->current_health_score ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                    Last 30 days trend points: {{ $healthScores->count() }}
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">Connected accounts</h2>
                @if($connections->isEmpty())
                    <div class="text-sm text-gray-500 dark:text-gray-400">No active connections yet.</div>
                @else
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($connections as $conn)
                            <div class="py-3 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3 min-w-0">
                                    <x-intelligence.channel-icon :channel="$conn->channel_type" size="sm" />
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                                            {{ str_replace('_', ' ', $conn->channel_type) }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                            {{ $conn->account_name ?? $conn->account_id ?? '—' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                    Last sync: {{ $conn->last_synced_at?->diffForHumans() ?? '—' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">Snapshots (by channel)</h2>
                @if($snapshots->isEmpty())
                    <div class="text-sm text-gray-500 dark:text-gray-400">No snapshot data in this range yet.</div>
                @else
                    <div class="space-y-5">
                        @foreach($snapshots as $channel => $rows)
                            <div class="rounded-xl border border-gray-100 dark:border-gray-800 p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <x-intelligence.channel-icon :channel="$channel" size="sm" />
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            {{ str_replace('_', ' ', $channel) }}
                                        </div>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        Latest: {{ optional($rows->first()->snapshot_date)->format('M j') }}
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    @php $latest = $rows->first(); @endphp
                                    <div>
                                        <div class="text-[11px] text-gray-500 dark:text-gray-400">Spend</div>
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format((float) $latest->spend, 2) }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[11px] text-gray-500 dark:text-gray-400">Leads</div>
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $latest->leads ?? 0 }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[11px] text-gray-500 dark:text-gray-400">CTR</div>
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $latest->ctr ? number_format((float) $latest->ctr * 100, 2).'%' : '—' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[11px] text-gray-500 dark:text-gray-400">ROAS</div>
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $latest->roas ? number_format((float) $latest->roas, 2).'x' : '—' }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">Active alerts</h2>
                @forelse($anomalies as $anomaly)
                    <div class="py-2">
                        <x-intelligence.anomaly-badge :severity="$anomaly->severity" />
                        <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ str_replace('_', ' ', $anomaly->anomaly_type) }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $anomaly->metric_name }}: {{ number_format((float) $anomaly->current_value, 2) }}
                            vs {{ number_format((float) $anomaly->baseline_value, 2) }}
                            ({{ number_format((float) $anomaly->deviation_percentage, 2) }}%)
                        </div>
                    </div>
                    @unless($loop->last)
                        <div class="my-3 border-t border-gray-100 dark:border-gray-800"></div>
                    @endunless
                @empty
                    <div class="text-sm text-gray-500 dark:text-gray-400">No unresolved anomalies.</div>
                @endforelse
            </div>

            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">Active AI insights</h2>
                @forelse($insights as $insight)
                    <div class="py-2">
                        <x-intelligence.priority-badge :priority="$insight->priority" />
                        <div class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $insight->title }}</div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $insight->recommended_action }}</div>
                    </div>
                    @unless($loop->last)
                        <div class="my-3 border-t border-gray-100 dark:border-gray-800"></div>
                    @endunless
                @empty
                    <div class="text-sm text-gray-500 dark:text-gray-400">No active insights.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

