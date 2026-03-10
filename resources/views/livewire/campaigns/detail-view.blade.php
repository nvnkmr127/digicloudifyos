<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Campaign Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <!-- Notifications -->
            @if (session()->has('message'))
                <div
                    class="bg-indigo-100 border border-indigo-400 text-indigo-700 px-6 py-4 rounded-3xl relative flex items-center shadow-sm">
                    <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-bold">{{ session('message') }}</span>
                </div>
            @endif

            @if (session()->has('error'))
                <div
                    class="bg-rose-100 border border-rose-400 text-rose-700 px-6 py-4 rounded-3xl relative flex items-center shadow-sm">
                    <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="font-bold">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Header Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl border border-gray-100">
                <div class="p-8 md:p-12">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                        <div class="flex items-center">
                            <div
                                class="h-16 w-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-3xl flex items-center justify-center text-white text-2xl font-black shadow-xl shadow-indigo-100 mr-6">
                                {{ substr($campaign->name, 0, 1) }}
                            </div>
                            <div>
                                <h3 class="text-3xl font-black text-gray-900 tracking-tight">{{ $campaign->name }}</h3>
                                <div class="flex items-center mt-1 space-x-2">
                                    <p class="text-gray-400 font-bold text-sm tracking-widest uppercase">ID:
                                        {{ substr($campaign->id, 0, 8) }}
                                    </p>
                                    <span class="text-gray-200">|</span>
                                    <p class="text-indigo-400 font-bold text-sm tracking-widest uppercase">
                                        {{ $campaign->adAccount->platform ?? 'INTERNAL' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-6 md:mt-0 flex items-center space-x-3">
                            @if($campaign->adAccount)
                                <x-button color="outline" wire:click="syncMetrics" title="Sync from Meta">
                                    <svg wire:loading.class="animate-spin" class="h-4 w-4 mr-2" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Force Sync
                                </x-button>

                                <div class="h-8 border-r border-gray-200 mx-2"></div>

                                @if($campaign->status !== 'INACTIVE')
                                    <x-button color="outline" wire:click="pauseCampaign"
                                        class="text-amber-600 border-amber-200 hover:bg-amber-50">Pause</x-button>
                                @else
                                    <x-button color="primary" wire:click="pauseCampaign">Resume</x-button>
                                @endif

                                <x-button color="outline" wire:click="archiveCampaign"
                                    class="text-gray-500">Archive</x-button>
                                <x-button color="outline" wire:click="deleteCampaign"
                                    class="text-rose-600 border-rose-200 hover:bg-rose-50"
                                    wire:confirm="Are you sure you want to delete this campaign permanently from both DC OS and Meta?">Delete</x-button>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mt-12 pt-12 border-t border-gray-50">
                        <div>
                            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Client
                                Partner</div>
                            <div class="text-lg font-bold text-gray-900">{{ $campaign->client->name }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Ad
                                Infrastructure</div>
                            <div class="text-lg font-bold text-gray-900">
                                @if($campaign->adAccount)
                                    <div class="flex items-center">
                                        <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span>
                                        {{ $campaign->adAccount->account_name }}
                                    </div>
                                @else
                                    <span class="text-gray-400 italic font-medium">Not Linked</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Total
                                Budget</div>
                            <div class="text-lg font-bold text-gray-900">{{ $campaign->getFormattedBudgetAttribute() }}
                            </div>
                        </div>
                        <div>
                            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Status &
                                Timeline</div>
                            <div class="flex items-center mt-1">
                                <span
                                    class="bg-indigo-50 text-indigo-700 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest mr-3">
                                    {{ $campaign->status }}
                                </span>
                                <span class="text-sm font-bold text-gray-900">
                                    {{ $campaign->start_date ? $campaign->start_date->format('M d') : 'TBD' }}
                                    @if($campaign->end_date) - {{ $campaign->end_date->format('M d, Y') }} @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabbed Navigation                    <nav class="-mb-px flex space-x-12" aria-label="Tabs">
                        <button wire:click="setTab('creative')"
                            class="{{ $activeTab === 'creative' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-400 hover:text-gray-600' }} border-b-2 py-6 px-1 text-[10px] font-black uppercase tracking-[0.3em] transition">Creative
                            Briefs</button>
                        <button wire:click="setTab('adsets')"
                            class="{{ $activeTab === 'adsets' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-400 hover:text-gray-600' }} border-b-2 py-6 px-1 text-[10px] font-black uppercase tracking-[0.3em] transition">Targeting
                            Ad Sets</button>
                        <button wire:click="setTab('performance')"
                            class="{{ $activeTab === 'performance' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-400 hover:text-gray-600' }} border-b-2 py-6 px-1 text-[10px] font-black uppercase tracking-[0.3em] transition">Performance
                            Data</button>
                        <button wire:click="setTab('audience')"
                            class="{{ $activeTab === 'audience' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-400 hover:text-gray-600' }} border-b-2 py-6 px-1 text-[10px] font-black uppercase tracking-[0.3em] transition">Audience
                            Demographics</button>
                        <button wire:click="setTab('leads')"
                            class="{{ $activeTab === 'leads' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-400 hover:text-gray-600' }} border-b-2 py-6 px-1 text-[10px] font-black uppercase tracking-[0.3em] transition">Captured
                            Leads</button>
                    </nav>

            @if($activeTab === 'creative')
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Creative Pipeline -->
                    <div class="lg:col-span-2 space-y-8">
                        <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm transition hover:shadow-md">
                            <div class="flex justify-between items-center mb-8">
                                <h4 class="text-2xl font-black text-gray-900 tracking-tight">Creative Production</h4>
                                <button
                                    class="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-indigo-100 transition">Add
                                    Asset</button>
                            </div>

                            <div class="space-y-4">
                                @forelse($campaign->creativeRequests as $request)
                                    <div
                                        class="flex items-center justify-between p-5 bg-gray-50 rounded-2xl border border-gray-100 hover:border-indigo-100 hover:bg-white transition-all group">
                                        <div class="flex items-center">
                                            <div
                                                class="h-12 w-12 bg-white rounded-xl shadow-sm flex items-center justify-center text-gray-400 mr-4 group-hover:scale-110 transition">
                                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900 tracking-tight">{{ $request->title }}
                                                </div>
                                                <div
                                                    class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-0.5">
                                                    Type: {{ $request->type }}</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-4">
                                            <span
                                                class="bg-white px-4 py-1.5 rounded-full text-[10px] font-black text-gray-500 shadow-sm border border-gray-100 uppercase tracking-widest">
                                                {{ $request->status }}
                                            </span>
                                            <button class="text-gray-300 hover:text-indigo-600 transition">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 5l7 7-7 7" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="py-16 text-center bg-gray-50 rounded-3xl border border-dashed border-gray-200">
                                        <div
                                            class="inline-flex h-12 w-12 bg-gray-100 rounded-full items-center justify-center mb-4 text-gray-300">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <p class="text-sm text-gray-400 font-bold uppercase tracking-widest">Awaiting
                                            creative
                                            briefs</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Active Tasks -->
                    <div class="space-y-8">
                        <div
                            class="bg-indigo-600 p-8 rounded-[2rem] text-white shadow-xl shadow-indigo-100 relative overflow-hidden">
                            <div class="absolute -right-4 -top-4 h-24 w-24 bg-white opacity-5 rounded-full"></div>
                            <h4 class="text-xl font-black mb-8 tracking-tight relative z-10">Execution Roadmap</h4>
                            <div class="space-y-8 relative z-10">
                                @forelse($campaign->tasks->where('status', '!=', 'completed') as $task)
                                    <div class="flex items-start group">
                                        <div
                                            class="h-6 w-6 rounded-lg bg-indigo-500/50 border border-indigo-400/50 mt-0.5 mr-4 flex-shrink-0 group-hover:bg-white transition flex items-center justify-center">
                                            <div class="h-2 w-2 rounded-sm bg-white group-hover:bg-indigo-600 transition">
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-sm font-black tracking-tight group-hover:translate-x-1 transition">
                                                {{ $task->title }}
                                            </div>
                                            <div class="text-[10px] text-indigo-200 font-black mt-1 uppercase tracking-widest">
                                                Due {{ $task->due_date ? $task->due_date->format('M d') : 'TODAY' }}
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="py-4 text-center border border-indigo-500/30 rounded-2xl bg-indigo-500/10">
                                        <p class="text-xs text-indigo-200 font-black uppercase tracking-widest">Roadmap is
                                            clear
                                        </p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($activeTab === 'adsets')
                <div class="space-y-8">
                    <div class="grid grid-cols-1 gap-6">
                        @forelse($campaign->adSets as $adSet)
                            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden transition hover:border-indigo-200"
                                x-data="{ expanded: false }">
                                <div class="p-6 flex items-center justify-between cursor-pointer" @click="expanded = !expanded">
                                    <div class="flex items-center space-x-6">
                                        <div
                                            class="h-10 w-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 font-black text-xs">
                                            SET
                                        </div>
                                        <div>
                                            <h5 class="text-lg font-black text-gray-900 tracking-tight">{{ $adSet->name }}
                                            </h5>
                                            <div
                                                class="flex items-center mt-1 space-x-3 text-[10px] font-black uppercase tracking-widest text-gray-400">
                                                <span>ID: {{ $adSet->external_adset_id }}</span>
                                                <span class="text-gray-200">•</span>
                                                <span class="text-indigo-500">{{ $adSet->status }}</span>
                                                <span class="text-gray-200">•</span>
                                                <span>DAILY: ${{ number_format((float) $adSet->daily_budget, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <span class="text-xs font-bold text-gray-400 mr-4">{{ $adSet->ads->count() }} Ads
                                            Active</span>
                                        <svg class="h-5 w-5 text-gray-400 transition" :class="expanded ? 'rotate-180' : ''"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>

                                <div x-show="expanded" x-transition class="bg-gray-50/50 border-t border-gray-50">
                                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                        @foreach($adSet->ads as $ad)
                                            <div
                                                class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm group transition hover:border-indigo-100">
                                                <div class="flex justify-between items-start mb-4">
                                                    <div
                                                        class="h-8 w-8 bg-gray-50 rounded-lg flex items-center justify-center text-[10px] font-black group-hover:bg-indigo-50 group-hover:text-indigo-600 transition">
                                                        AD</div>
                                                    <span
                                                        class="text-[9px] font-black px-2 py-0.5 rounded bg-green-50 text-green-700 uppercase tracking-widest">{{ $ad->status }}</span>
                                                </div>
                                                <div class="font-bold text-gray-900 line-clamp-1 mb-1">{{ $ad->name }}</div>
                                                <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">
                                                    ID:
                                                    {{ $ad->external_ad_id }}
                                                </div>

                                                <div
                                                    class="pt-4 border-t border-gray-50 flex justify-between items-center text-[10px] font-black uppercase tracking-widest text-gray-400">
                                                    <span>Creative Info</span>
                                                    <button class="text-indigo-500 hover:underline">Preview</button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="py-24 text-center bg-white rounded-[2rem] border-2 border-dashed border-gray-100">
                                <p class="text-gray-400 font-black uppercase tracking-widest">No Meta Ad Sets detected for
                                    this
                                    campaign yet.</p>
                                <button wire:click="syncMetrics"
                                    class="mt-4 text-indigo-600 font-black text-xs uppercase tracking-widest hover:underline">Run
                                    Deep Sync</button>
                            </div>
                        @endforelse
                    </div>
                </div>
            @elseif($activeTab === 'performance')
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                    <!-- Metric Cards -->
                    @php
                        $insights = $campaign->adInsights->where('level', 'campaign');
                    @endphp
                    <div class="lg:col-span-4 grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
                            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 text-center">
                                Impressions</div>
                            <div class="text-3xl font-black text-gray-900 text-center">
                                {{ number_format($insights->sum('impressions')) }}
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
                            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 text-center">
                                Clicks</div>
                            <div class="text-3xl font-black text-indigo-600 text-center">
                                {{ number_format($insights->sum('clicks')) }}
                            </div>
                        </div>
                        <div class="bg-indigo-600 p-6 rounded-3xl text-white shadow-xl shadow-indigo-100">
                            <div class="text-[10px] font-black opacity-70 uppercase tracking-widest mb-2 text-center">
                                Total
                                Spend</div>
                            <div class="text-3xl font-black text-center">
                                ${{ number_format((float) $insights->sum('spend'), 2) }}</div>
                        </div>
                        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
                            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 text-center">
                                Conversions</div>
                            <div class="text-3xl font-black text-gray-900 text-center">
                                {{ number_format($insights->sum('conversions')) }}
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-4 bg-white p-8 rounded-3xl border border-gray-100 shadow-sm">
                        <h4 class="text-2xl font-black text-gray-900 tracking-tight mb-8">Metric History</h4>
                        <div class="h-64 flex items-end space-x-2">
                            @foreach($insights->sortBy('date')->take(-14) as $insight)
                                <div class="flex-1 bg-indigo-50 rounded-t-xl relative group"
                                    style="height: {{ ($insight->impressions / (max($insights->pluck('impressions')->toArray()) ?: 1)) * 100 }}%">
                                    <div
                                        class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 bg-gray-900 text-white text-[10px] font-black px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap z-50">
                                        {{ number_format($insight->impressions) }} Impr
                                    </div>
                                    <div class="absolute top-0 left-0 w-full bg-indigo-600 rounded-t-xl opacity-0 group-hover:opacity-100 transition"
                                        style="height: {{ ($insight->clicks / (max($insight->impressions ?: 1, 1))) * 100 }}%">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div
                            class="flex justify-between mt-4 text-[9px] font-black text-gray-400 uppercase tracking-widest">
                            <span>Past 14 Days Activity (Campaign Level)</span>
                        </div>
                    </div>
                </div>
            @elseif($activeTab === 'audience')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    @php
                        $demographics = $campaign->audienceInsights;
                    @endphp

                    <!-- Gender Breakdown -->
                    <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm transition hover:shadow-md">
                        <h4
                            class="text-xl font-black text-gray-900 tracking-tight mb-6 uppercase tracking-widest text-[10px]">
                            Gender Distribution</h4>
                        <div class="space-y-4">
                            @php
                                $genderData = $demographics->where('breakdown_type', 'gender')->groupBy('dimension_1')->map->sum('impressions');
                                $totalGender = $genderData->sum();
                            @endphp
                            @forelse($genderData as $gender => $count)
                                <div>
                                    <div class="flex justify-between text-xs font-bold mb-1 uppercase tracking-widest">
                                        <span>{{ $gender }}</span>
                                        <span>{{ number_format(($count / max($totalGender, 1)) * 100, 1) }}%</span>
                                    </div>
                                    <div class="h-2 bg-gray-50 rounded-full overflow-hidden">
                                        <div class="h-full bg-indigo-500 rounded-full"
                                            style="width: {{ ($count / max($totalGender, 1)) * 100 }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-text-muted italic">No gender data available yet.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Age Breakdown -->
                    <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm transition hover:shadow-md">
                        <h4
                            class="text-xl font-black text-gray-900 tracking-tight mb-6 uppercase tracking-widest text-[10px]">
                            Age Range Breakdown</h4>
                        <div class="space-y-4">
                            @php
                                $ageData = $demographics->where('breakdown_type', 'age')->groupBy('dimension_1')->map->sum('impressions');
                                $totalAge = $ageData->sum();
                            @endphp
                            @forelse($ageData as $age => $count)
                                <div>
                                    <div
                                        class="flex justify-between text-xs font-bold mb-1 uppercase tracking-widest text-text-muted">
                                        <span>{{ $age }}</span>
                                        <span>{{ number_format($count) }}</span>
                                    </div>
                                    <div class="h-1.5 bg-gray-50 rounded-full overflow-hidden">
                                        <div class="h-full bg-purple-500"
                                            style="width: {{ ($count / max($totalAge, 1)) * 100 }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-text-muted italic">No age data available yet.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Placement Performance -->
                    <div
                        class="md:col-span-2 bg-white p-8 rounded-3xl border border-gray-100 shadow-sm transition hover:shadow-md">
                        <h4
                            class="text-xl font-black text-gray-900 tracking-tight mb-8 uppercase tracking-widest text-[10px]">
                            Placement Efficiency</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            @php
                                $placementData = $demographics->where('breakdown_type', 'placement')->groupBy('dimension_1');
                            @endphp
                            @forelse($placementData as $placement => $pInsights)
                                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2">
                                        {{ $placement }}</p>
                                    <div class="flex items-baseline gap-2">
                                        <span
                                            class="text-xl font-black text-gray-900">{{ number_format($pInsights->sum('impressions')) }}</span>
                                        <span class="text-[10px] text-gray-400 font-bold">IMP</span>
                                    </div>
                                    <div class="mt-2 text-[10px] font-bold text-indigo-600">
                                        ${{ number_format($pInsights->sum('spend'), 2) }} SPENT
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-3 text-center py-8">
                                    <p class="text-xs text-text-muted italic">No placement data available for this campaign.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @elseif($activeTab === 'leads')
                <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-10 border-b border-gray-50 flex justify-between items-center">
                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em]">Campaign Lead Roster</h4>
                        <span class="bg-indigo-50 text-indigo-600 px-4 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">
                            {{ $campaign->facebookLeads->count() }} Total
                        </span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-50">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th class="px-8 py-6 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Lead Identity</th>
                                    <th class="px-8 py-6 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Contact Details</th>
                                    <th class="px-8 py-6 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Form Source</th>
                                    <th class="px-8 py-6 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Captured</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($campaign->facebookLeads as $lead)
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="px-8 py-6 whitespace-nowrap">
                                            <div class="flex items-center gap-4">
                                                <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 font-black">
                                                    {{ substr($lead->full_name, 0, 1) }}
                                                </div>
                                                <div class="font-black text-gray-900 tracking-tight">{{ $lead->full_name }}</div>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6 whitespace-nowrap">
                                            <div class="text-sm font-bold text-gray-700">{{ $lead->email }}</div>
                                            <div class="text-[10px] text-gray-400 font-black uppercase tracking-widest">{{ $lead->phone }}</div>
                                        </td>
                                        <td class="px-8 py-6 whitespace-nowrap">
                                            <span class="inline-flex px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-gray-100 text-gray-600">
                                                {{ $lead->form_name ?: 'Meta Lead Ads' }}
                                            </span>
                                        </td>
                                        <td class="px-8 py-6 text-right whitespace-nowrap font-black text-gray-400 text-[10px] uppercase tracking-widest">
                                            {{ $lead->created_at->format('M d, H:i') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-8 py-20 text-center">
                                            <p class="text-[10px] text-gray-400 italic font-black uppercase tracking-widest">No leads captured for this campaign yet</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
</div>
</div>