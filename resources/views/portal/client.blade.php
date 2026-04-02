<x-layouts.portal>
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <div class="text-xs font-black text-gray-400 uppercase tracking-widest">Client Portal</div>
            <div class="text-2xl font-black text-gray-900">{{ $client->name }}</div>
            <div class="text-sm text-gray-600">Report date: {{ $date }}</div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="p-6 bg-white rounded-2xl border border-gray-100">
                <div class="text-sm font-black text-gray-900">Today’s Actions</div>
                <div class="text-xs text-gray-500">Prioritized recommendations generated from performance monitoring and competitive signals.</div>

                <div class="mt-4 space-y-2">
                    @forelse($actionItems as $item)
                        <div class="p-4 rounded-2xl border border-gray-100 bg-gray-50">
                            <div class="text-sm font-bold text-gray-900">{{ $item->title }}</div>
                            <div class="text-xs text-gray-600 mt-1">{{ $item->description }}</div>
                            <div class="text-xs text-gray-700 mt-2 font-semibold">Action: {{ $item->action }}</div>
                        </div>
                    @empty
                        <div class="p-4 border border-dashed border-gray-200 rounded-2xl text-sm text-gray-600">
                            No action items for this date yet.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="p-6 bg-white rounded-2xl border border-gray-100">
                <div class="text-sm font-black text-gray-900">Performance Snapshots</div>
                <div class="text-xs text-gray-500">Cross-channel KPIs pulled daily from connected accounts.</div>

                <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                    @forelse($snapshots as $s)
                        <div class="p-4 rounded-2xl border border-gray-100 bg-white">
                            <div class="text-sm font-bold text-gray-900">{{ strtoupper($s->channel_type) }}</div>
                            <div class="mt-2 grid grid-cols-3 gap-2 text-xs">
                                <div>
                                    <div class="text-gray-500">Spend</div>
                                    <div class="font-bold text-gray-900">{{ number_format((float) $s->spend, 2) }}</div>
                                </div>
                                <div>
                                    <div class="text-gray-500">Revenue</div>
                                    <div class="font-bold text-gray-900">{{ number_format((float) $s->revenue, 2) }}</div>
                                </div>
                                <div>
                                    <div class="text-gray-500">ROAS</div>
                                    <div class="font-bold text-gray-900">{{ number_format((float) $s->roas, 2) }}</div>
                                </div>
                            </div>
                            <div class="mt-2 grid grid-cols-3 gap-2 text-xs">
                                <div>
                                    <div class="text-gray-500">Impr.</div>
                                    <div class="font-bold text-gray-900">{{ (int) $s->impressions }}</div>
                                </div>
                                <div>
                                    <div class="text-gray-500">Clicks</div>
                                    <div class="font-bold text-gray-900">{{ (int) $s->clicks }}</div>
                                </div>
                                <div>
                                    <div class="text-gray-500">Conv.</div>
                                    <div class="font-bold text-gray-900">{{ (int) $s->conversions }}</div>
                                </div>
                            </div>
                            @if(is_array($s->anomaly_flags) && count($s->anomaly_flags) > 0)
                                <div class="mt-3 text-xs text-red-700 font-bold">
                                    Alerts: {{ implode(', ', $s->anomaly_flags) }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="p-4 border border-dashed border-gray-200 rounded-2xl text-sm text-gray-600">
                            No snapshots for this date yet.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="p-6 bg-white rounded-2xl border border-gray-100">
                <div class="text-sm font-black text-gray-900">Insights</div>
                <div class="text-xs text-gray-500">AI-generated explanations and recommended next steps.</div>

                <div class="mt-4 space-y-2">
                    @forelse($insights as $insight)
                        <div class="p-4 rounded-2xl border border-gray-100 bg-gray-50">
                            <div class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ $insight->priority }}</div>
                            <div class="text-sm font-bold text-gray-900 mt-1">{{ $insight->title }}</div>
                            <div class="text-xs text-gray-600 mt-1">{{ $insight->issue_description }}</div>
                            <div class="text-xs text-gray-700 mt-2 font-semibold">Action: {{ $insight->recommended_action }}</div>
                        </div>
                    @empty
                        <div class="p-4 border border-dashed border-gray-200 rounded-2xl text-sm text-gray-600">
                            No insights for this date yet.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="p-6 bg-white rounded-2xl border border-gray-100">
                <div class="text-sm font-black text-gray-900">Support</div>
                <div class="text-xs text-gray-500">Questions about the data or recommended actions?</div>
                <div class="mt-3 text-sm font-bold text-gray-900">
                    {{ $client->privacy_contact_email ?: ($client->email ?: 'Contact your agency team') }}
                </div>
            </div>
        </div>
    </div>
</x-layouts.portal>

