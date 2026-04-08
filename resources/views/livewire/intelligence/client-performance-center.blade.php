<div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="flex flex-col items-start justify-between gap-4 mb-10 md:flex-row md:items-center">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('intelligence.briefing') }}" class="text-xs font-bold tracking-widest text-indigo-600 uppercase hover:underline dark:text-indigo-400">← Agency Command</a>
            </div>
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-slate-100">{{ $client->name }}</h1>
            <p class="mt-1 text-slate-500 dark:text-slate-400">Performance Intelligence Report for {{ $date }}</p>
        </div>

        @if($client->latestHealthScore)
            @php
                $score = $client->latestHealthScore->overall_score;
                $scoreDarkClass = $score >= 70
                    ? 'dark:bg-green-900/30 dark:text-green-200 dark:border-green-800'
                    : ($score >= 40
                        ? 'dark:bg-yellow-900/30 dark:text-yellow-200 dark:border-yellow-800'
                        : 'dark:bg-red-900/30 dark:text-red-200 dark:border-red-800');
            @endphp
            <div class="flex items-center p-4 bg-white border border-slate-200 rounded-2xl shadow-sm dark:bg-slate-900 dark:border-slate-800">
                <div class="mr-4">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 text-right dark:text-slate-500">Health Score</div>
                    <div class="text-3xl font-black text-slate-900 text-right dark:text-slate-100">{{ $client->latestHealthScore->overall_score }}<span class="text-sm font-bold text-slate-400 dark:text-slate-500">/100</span></div>
                </div>
                <div class="w-12 h-12 flex items-center justify-center rounded-xl border {{ $client->latestHealthScore->getScoreBadgeClass() }} {{ $scoreDarkClass }}">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                </div>
            </div>
        @endif
    </div>

    <!-- Metrics Grid -->
    <div class="grid grid-cols-1 gap-6 mb-10 lg:grid-cols-3">
        @forelse($snapshots as $snapshot)
            <div class="relative overflow-hidden bg-white border border-slate-200 rounded-3xl shadow-sm hover:shadow-md transition-all dark:bg-slate-900 dark:border-slate-800">
                @if($snapshot->hasAnomalies())
                    <div class="absolute top-0 right-0 p-4">
                        <span class="flex items-center justify-center w-8 h-8 text-white bg-red-500 rounded-full animate-pulse shadow-lg ring-4 ring-red-50 dark:ring-red-900/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </span>
                    </div>
                @endif

                <div class="p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-2 bg-indigo-50 rounded-lg dark:bg-indigo-900/20">
                            <x-intelligence.channel-icon :channel="$snapshot->channel_type" />
                        </div>
                        <h3 class="text-sm font-black tracking-widest text-slate-400 uppercase dark:text-slate-500">{{ str_replace('_', ' ', $snapshot->channel_type) }}</h3>
                    </div>

                    <div class="grid grid-cols-2 gap-y-6">
                        <div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 dark:text-slate-500">Spend</div>
                            <div class="text-xl font-bold text-slate-900 dark:text-slate-100">${{ number_format($snapshot->spend, 2) }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 dark:text-slate-500">CTR</div>
                            <div class="flex items-center text-xl font-bold text-slate-900 dark:text-slate-100">
                                {{ number_format($snapshot->ctr * 100, 2) }}%
                                @php $ctrChange = $snapshot->getCtrChangePercent() @endphp
                                @if($ctrChange !== null)
                                    <span class="ml-2 text-xs {{ $ctrChange >= 0 ? 'text-emerald-500' : 'text-red-500' }}">
                                        {{ $ctrChange >= 0 ? '↑' : '↓' }} {{ abs(round($ctrChange)) }}%
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 dark:text-slate-500">ROAS</div>
                            <div class="flex items-center text-xl font-bold text-slate-900 dark:text-slate-100">
                                {{ number_format($snapshot->roas, 2) }}x
                                @php $roasChange = $snapshot->getRoasChangePercent() @endphp
                                @if($roasChange !== null)
                                    <span class="ml-2 text-xs {{ $roasChange >= 0 ? 'text-emerald-500' : 'text-red-500' }}">
                                        {{ $roasChange >= 0 ? '↑' : '↓' }} {{ abs(round($roasChange)) }}%
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 dark:text-slate-500">CPC</div>
                            <div class="flex items-center text-xl font-bold text-slate-900 dark:text-slate-100">
                                ${{ number_format($snapshot->cpc, 2) }}
                                @php $cpcChange = $snapshot->getCpcChangePercent() @endphp
                                @if($cpcChange !== null)
                                    <span class="ml-2 text-xs {{ $cpcChange <= 0 ? 'text-emerald-500' : 'text-red-500' }}">
                                        {{ $cpcChange <= 0 ? '↓' : '↑' }} {{ abs(round($cpcChange)) }}%
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @if($snapshot->hasAnomalies())
                    <div class="px-8 py-4 bg-red-50 border-t border-red-100 italic dark:bg-red-900/20 dark:border-red-800">
                        <p class="text-[11px] font-bold text-red-700 tracking-wide uppercase dark:text-red-200">Detected Anomaly</p>
                    </div>
                @endif
            </div>
        @empty
            <div class="flex flex-col items-center justify-center col-span-1 p-12 bg-white border border-slate-200 lg:col-span-3 rounded-3xl sm:p-24 dark:bg-slate-900 dark:border-slate-800">
                <div class="p-4 mb-4 bg-slate-50 rounded-full text-slate-300 dark:bg-slate-800 dark:text-slate-600">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900 dark:text-slate-100">No Historical Data Yet</h3>
                <p class="mt-2 text-slate-500 dark:text-slate-400">Wait for the next sync cycle to see performance snapshots.</p>
            </div>
        @endforelse
    </div>

    <!-- AI Investigation Section -->
    @if($snapshots->some(fn($s) => $s->hasAnomalies()))
        <div class="p-10 mb-10 overflow-hidden bg-slate-900 rounded-3xl shadow-2xl relative dark:bg-slate-950">
            <div class="absolute top-0 right-0 p-8 opacity-10">
                <svg class="w-32 h-32 text-indigo-400" fill="currentColor" viewBox="0 0 24 24"><path d="M9,22A1,1 0 0,1 8,21V18H4A2,2 0 0,1 2,16V4C2,2.89 2.9,2 4,2H20A2,2 0 0,1 22,4V16A2,2 0 0,1 20,18H13.9L10.2,21.71C10,21.9 9.75,22 9.5,22V22H9Z"/></svg>
            </div>
            
            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-4">
                    <span class="px-3 py-1 text-[10px] font-extrabold tracking-widest text-indigo-400 uppercase bg-indigo-500/10 rounded-md ring-1 ring-indigo-400/20">AI Investigation</span>
                </div>
                <h2 class="text-2xl font-black text-white mb-6">Deep Analysis of Performance Shifts</h2>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    @foreach($recentInsights as $insight)
                        <div class="p-6 bg-white/5 border border-white/10 rounded-2xl backdrop-blur-sm">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="w-2 h-2 bg-indigo-500 rounded-full"></span>
                                <h4 class="text-sm font-bold text-slate-300">{{ $insight->title }}</h4>
                            </div>
                            <p class="text-sm leading-relaxed text-slate-400 mb-4">{{ $insight->issue_description }}</p>
                            <div class="p-3 text-xs font-semibold leading-relaxed text-indigo-200 bg-indigo-500/10 rounded-lg border-l-2 border-indigo-500">
                                <span class="opacity-50 text-[10px] block mb-1 uppercase tracking-widest">Recommendation</span>
                                {{ $insight->recommended_action }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
