<div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="flex flex-col items-start justify-between gap-4 mb-10 md:flex-row md:items-center">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-slate-100">Intelligence Overview</h1>
            <p class="mt-1 text-slate-500 dark:text-slate-400">Agency-wide performance monitoring and AI-driven optimizations.</p>
        </div>
        <div class="flex gap-4">
            <a href="{{ route('intelligence.briefing') }}" class="px-5 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-xl shadow-lg shadow-indigo-200/60 hover:bg-indigo-700 transition focus:outline-none focus:ring-4 focus:ring-indigo-200 dark:shadow-indigo-900/30 dark:focus:ring-indigo-900/40">View Morning Briefing</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <!-- Main Column: Client Health Grid -->
        <div class="lg:col-span-2 space-y-8">
            <div class="p-8 bg-white border border-slate-200 rounded-3xl shadow-sm dark:bg-slate-900 dark:border-slate-800">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-xl font-black text-slate-900 dark:text-slate-100">Client Portfolio Health</h3>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest dark:text-slate-500">Active Clients: {{ $clients->count() }}</div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach($clients as $c)
                        @php
                            $score = $c->latestHealthScore?->overall_score;
                            $scoreDarkClass = $score === null
                                ? 'dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700'
                                : ($score >= 70
                                    ? 'dark:bg-green-900/30 dark:text-green-200 dark:border-green-800'
                                    : ($score >= 40
                                        ? 'dark:bg-yellow-900/30 dark:text-yellow-200 dark:border-yellow-800'
                                        : 'dark:bg-red-900/30 dark:text-red-200 dark:border-red-800'));
                        @endphp
                        <a href="{{ route('intelligence.client.workspace', $c->id) }}" class="flex items-center p-4 transition border border-slate-100 rounded-2xl bg-slate-50 hover:bg-white hover:shadow-md hover:border-indigo-100 group dark:border-slate-800 dark:bg-slate-950/40 dark:hover:bg-slate-900 dark:hover:border-indigo-800">
                            <div class="flex items-center justify-center w-12 h-12 mr-4 font-black rounded-xl border {{ $c->latestHealthScore?->getScoreBadgeClass() ?? 'bg-slate-200 text-slate-400 border-slate-200' }} {{ $scoreDarkClass }}">
                                {{ $c->latestHealthScore?->overall_score ?? '?' }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold truncate text-slate-900 group-hover:text-indigo-600 dark:text-slate-100 dark:group-hover:text-indigo-400">{{ $c->name }}</h4>
                                <div class="flex items-center mt-1">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest dark:text-slate-500">{{ $c->industry ?? 'Other' }}</span>
                                    @if($c->latestHealthScore?->trend === 'improving')
                                        <span class="ml-2 text-emerald-600 text-[10px] font-bold dark:text-emerald-400">↑Improving</span>
                                    @elseif($c->latestHealthScore?->trend === 'declining')
                                        <span class="ml-2 text-red-600 text-[10px] font-bold dark:text-red-400">↓Declining</span>
                                    @endif
                                </div>
                            </div>
                            <div class="ml-2 opacity-0 group-hover:opacity-100 transition text-indigo-500 dark:text-indigo-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Recent AI Insights -->
            <div class="p-8 bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl relative overflow-hidden dark:bg-slate-950 dark:border-slate-800">
                <div class="absolute top-0 right-0 p-8 opacity-5">
                    <svg class="w-32 h-32 text-indigo-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20M11,17H13V13H11V17M11,11H13V7H11V11Z"/></svg>
                </div>
                
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-xl font-black text-white">Top Optimization Opportunities</h3>
                        <a href="{{ route('intelligence.insights') }}" class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest hover:underline">View All Insights →</a>
                    </div>

                    <div class="space-y-4">
                        @forelse($recentInsights as $insight)
                            <div class="p-6 transition-all border bg-white/5 border-white/10 rounded-2xl hover:bg-white/10 backdrop-blur-sm">
                                <div class="flex items-start justify-between gap-4 mb-3">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">{{ $insight->client->name }}</span>
                                            <span class="w-1 h-1 rounded-full bg-slate-500"></span>
                                            <span class="px-1.5 py-0.5 rounded text-[8px] font-black uppercase {{ $insight->priority === 'critical' ? 'bg-red-500/20 text-red-400' : 'bg-indigo-500/20 text-indigo-400' }}">{{ $insight->priority }}</span>
                                        </div>
                                        <h4 class="text-base font-bold text-slate-100">{{ $insight->title }}</h4>
                                    </div>
                                    <div class="text-right">
                                        <span class="block text-[8px] font-black text-slate-500 uppercase tracking-widest mb-1">Impact</span>
                                        <span class="text-xs font-bold text-indigo-400 capitalize">{{ $insight->expected_impact }}</span>
                                    </div>
                                </div>
                                <p class="text-sm leading-relaxed text-slate-400 mb-4">{{ $insight->issue_description }}</p>
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-indigo-500/10 rounded-lg text-xs font-semibold text-indigo-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    {{ $insight->recommended_action }}
                                </div>
                            </div>
                        @empty
                            <p class="text-sm italic text-slate-500">No active AI insights available for today.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar: Alerts & Activity -->
        <div class="space-y-8">
            <div class="p-8 bg-white border border-slate-200 rounded-3xl shadow-sm dark:bg-slate-900 dark:border-slate-800">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-lg font-black text-slate-900 dark:text-slate-100">Live Alerts</h3>
                    <a href="{{ route('intelligence.alerts') }}" class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest hover:underline dark:text-indigo-400">View All</a>
                </div>

                <div class="space-y-4">
                    @forelse($topAlerts as $alert)
                        <div class="flex items-start gap-4 p-4 border rounded-2xl border-slate-100 bg-slate-50 dark:border-slate-800 dark:bg-slate-950/40">
                            <div class="shrink-0 w-2 h-2 mt-1.5 rounded-full {{ $alert->severity === 'critical' ? 'bg-red-500 animate-pulse' : ($alert->severity === 'high' ? 'bg-orange-500' : 'bg-indigo-500') }}"></div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest dark:text-slate-500">{{ $alert->client->name }}</p>
                                <p class="mt-0.5 text-xs font-bold text-slate-800 dark:text-slate-200">{{ $alert->metric_name }} shifted by {{ abs(round($alert->deviation_percentage)) }}%</p>
                                <p class="mt-1 text-[10px] font-medium text-slate-500 italic dark:text-slate-400">{{ str_replace('_', ' ', $alert->anomaly_type) }} detected {{ $alert->detected_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="p-12 text-center text-slate-300 dark:text-slate-600">
                            <svg class="w-12 h-12 mx-auto mb-2 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <p class="text-xs font-bold uppercase tracking-widest">No Active Alerts</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="p-8 bg-white border border-slate-200 rounded-3xl shadow-sm dark:bg-slate-900 dark:border-slate-800">
                <h3 class="mb-6 text-xs font-black tracking-widest text-slate-400 uppercase dark:text-slate-500">Intelligence Pipeline</h3>
                <div class="space-y-6">
                    <div class="flex items-center gap-4">
                        <div class="w-8 h-8 flex items-center justify-center bg-emerald-100 text-emerald-700 rounded-lg dark:bg-emerald-900/30 dark:text-emerald-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200">Aggregation Engine</p>
                            <p class="text-[10px] font-medium text-slate-500 dark:text-slate-400">
                                @if($lastSync)
                                    Last sync: {{ $lastSync->diffForHumans() }}
                                @else
                                    No data synced yet
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-8 h-8 flex items-center justify-center bg-emerald-100 text-emerald-700 rounded-lg dark:bg-emerald-900/30 dark:text-emerald-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200">Anomaly Audit</p>
                            <p class="text-[10px] font-medium text-slate-500 dark:text-slate-400">{{ $activeAlertsCount }} Active Signals · Online</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-8 h-8 flex items-center justify-center bg-emerald-100 text-emerald-700 rounded-lg dark:bg-emerald-900/30 dark:text-emerald-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200">AI Intelligence</p>
                            <p class="text-[10px] font-medium text-slate-500 dark:text-slate-400">{{ $aiModel }} · Cloud-Active</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
