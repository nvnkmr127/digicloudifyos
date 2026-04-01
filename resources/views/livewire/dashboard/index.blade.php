<x-app-container>
    <x-page-header title="Agency OS Command Center">
        <div class="flex items-center gap-4">
            <span class="px-4 py-2 bg-indigo-50 text-[10px] font-black text-indigo-600 rounded-full border border-indigo-100 uppercase tracking-[0.2em] animate-pulse">
                Satellite Uplink Active
            </span>
            <x-button color="outline" class="rounded-2xl shadow-sm" href="{{ route('settings') }}">
                Config OS
            </x-button>
        </div>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 mb-10">
        <!-- Revenue Card -->
        <x-card class="p-8 border-none shadow-xl rounded-[2.5rem] bg-indigo-600 text-white relative overflow-hidden">
            <div class="absolute -right-6 -top-6 h-32 w-32 bg-white opacity-5 rounded-full"></div>
             <p class="text-[10px] font-black text-indigo-200 uppercase tracking-widest mb-2">Liquidity Matrix</p>
             <h3 class="text-3xl font-black tracking-tight">${{ number_format($revenue_matrix['total_paid'] / 100, 2) }}</h3>
             <div class="mt-4 flex items-center justify-between">
                 <span class="text-[9px] font-black text-indigo-300 uppercase">+14.2% Flux</span>
                 <span class="text-[9px] font-black italic opacity-50">{{ number_format($revenue_matrix['pending'] / 100, 2) }} Pending</span>
             </div>
        </x-card>

        <!-- Lead Flux Card -->
        <x-card class="p-8 border-none shadow-xl rounded-[2.5rem] bg-white group hover:bg-gray-50 transition">
             <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Lead Inflow</p>
             <h3 class="text-3xl font-black text-gray-900 tracking-tight">{{ $lead_flux['total'] }} <span class="text-sm text-green-500 ml-2 font-black">+{{ $lead_flux['new_today'] }}</span></h3>
             <div class="mt-4 flex items-center gap-2">
                 <div class="flex -space-x-2">
                     <div class="h-6 w-6 rounded-full bg-indigo-50 border border-white"></div>
                     <div class="h-6 w-6 rounded-full bg-pink-50 border border-white"></div>
                 </div>
                 <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">{{ $lead_flux['high_intent'] }} High Intent Nodes</span>
             </div>
        </x-card>

        <!-- Creative Node Card -->
        <x-card class="p-8 border-none shadow-xl rounded-[2.5rem] bg-white group">
             <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Creative Pulse</p>
             <h3 class="text-3xl font-black text-gray-900 tracking-tight">{{ $creative_nodes['pending'] }} Requests</h3>
             <div class="mt-4 flex items-center justify-between">
                 @if($creative_nodes['urgent'] > 0)
                    <span class="px-2 py-1 bg-rose-50 text-rose-600 text-[8px] font-black rounded-lg uppercase tracking-widest animate-bounce">{{ $creative_nodes['urgent'] }} Critical</span>
                 @else
                    <span class="text-[9px] font-black text-green-500 uppercase tracking-widest">Stable Ops</span>
                 @endif
                 <x-button color="outline" class="p-1 px-3 rounded-lg text-[9px] border-gray-100" href="{{ route('creative-requests.index') }}">View Hub</x-button>
             </div>
        </x-card>

        <!-- Automation Pulse Card -->
        <x-card class="p-8 border-none shadow-xl rounded-[2.5rem] bg-gray-900 text-white group overflow-hidden">
             <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/10 to-transparent"></div>
             <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 flex items-center gap-2">
                 <span class="h-1.5 w-1.5 bg-green-400 rounded-full animate-ping"></span>
                 Automation Core
             </p>
             <h3 class="text-3xl font-black tracking-tight">{{ $automation_pulse['total_runs'] }} Ops</h3>
             <p class="mt-4 text-[9px] font-black text-gray-400 uppercase tracking-widest italic">Autonomous efficiency optimized</p>
        </x-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <!-- Active Automations Flow -->
        <div class="lg:col-span-2">
            <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.3em] mb-6">Automation Real-Time Stream</h3>
            <div class="space-y-4">
                @foreach($automation_pulse['recent_runs'] as $run)
                    <div class="flex items-center justify-between p-6 bg-white rounded-[2rem] shadow-sm hover:shadow-md transition group border-l-4 {{ $run->status === 'success' ? 'border-green-400' : 'border-rose-400' }}">
                         <div class="flex items-center gap-6">
                            <div class="h-12 w-12 bg-gray-50 rounded-2xl flex items-center justify-center text-gray-400 group-hover:bg-indigo-50 group-hover:text-indigo-500 transition">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-gray-900 uppercase tracking-tight">{{ $run->rule->name }}</h4>
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-1">{{ $run->created_at->diffForHumans() }} • Node {{ substr($run->id, 0, 6) }}</p>
                            </div>
                         </div>
                         <div class="text-right">
                            <span class="px-3 py-1 bg-gray-50 text-[8px] font-black rounded-full uppercase tracking-widest text-gray-400">{{ $run->status }}</span>
                         </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Recent Projects & Metrics -->
        <div class="space-y-10">
            <div>
                 <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.3em] mb-6">Strategic Initiatives</h3>
                 <div class="bg-white rounded-[3rem] shadow-xl p-8 space-y-8">
                     @foreach($recent_projects as $project)
                        <div class="flex items-start gap-4">
                            <div class="h-10 w-10 flex-shrink-0 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-400 font-black text-xs">
                                {{ substr($project->name, 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-xs font-black text-gray-900 truncate uppercase mt-1">{{ $project->name }}</h4>
                                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-1">{{ $project->client->name }}</p>
                                <div class="mt-3 w-full bg-gray-50 h-1 rounded-full overflow-hidden">
                                     <div class="bg-indigo-500 h-full w-[45%]"></div>
                                </div>
                            </div>
                        </div>
                     @endforeach
                     <x-button color="outline" class="w-full rounded-2xl text-[10px] font-black uppercase tracking-widest py-4 border-gray-100" href="{{ route('projects.index') }}">Access Full Portfolio</x-button>
                 </div>
            </div>

            <div class="p-8 bg-gradient-to-br from-indigo-600 to-indigo-800 rounded-[3rem] shadow-2xl relative overflow-hidden group">
                 <div class="absolute -right-10 -bottom-10 h-40 w-40 bg-white opacity-10 rounded-full group-hover:scale-110 transition duration-700"></div>
                 <div class="relative z-10">
                     <h3 class="text-white text-lg font-black tracking-tight uppercase italic mb-6">Conversion Core</h3>
                     <div class="flex items-center justify-between mb-2">
                        <span class="text-[9px] font-black text-indigo-200 uppercase tracking-widest">Funnel Efficiency</span>
                        <span class="text-[10px] font-black text-white">78%</span>
                     </div>
                     <div class="w-full bg-indigo-500 h-2 rounded-full overflow-hidden mb-6">
                         <div class="bg-white h-full w-[78%]"></div>
                     </div>
                     <p class="text-[10px] font-bold text-indigo-100 opacity-70 uppercase tracking-widest leading-relaxed italic">Intelligence engine is optimizing {{ $conversion_funnel['total_submissions'] }} unique data points from your marketing logic stack.</p>
                 </div>
            </div>
        </div>
    </div>

    <!-- Performance Intelligence Hub -->
    <div class="mt-16 mb-12">
        <div class="flex items-center justify-between mb-8">
            <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.3em]">Performance Intelligence Hub</h3>
            <a href="{{ route('intelligence.overview') }}" class="text-[10px] font-black text-indigo-600 uppercase tracking-widest hover:underline italic">Access Neural Center →</a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Client Health Grid Preview -->
            <div class="p-10 bg-white shadow-xl rounded-[3rem] border border-slate-100 relative overflow-hidden group">
                <div class="absolute -right-10 -top-10 h-40 w-40 bg-indigo-50 opacity-0 group-hover:opacity-100 rounded-full transition duration-700"></div>
                <h4 class="text-lg font-black text-slate-900 tracking-tight mb-8">Portfolio Health</h4>
                
                <div class="grid grid-cols-2 gap-4">
                    @forelse($client_health_grid as $client)
                        <a href="{{ route('intelligence.client', $client->id) }}" class="p-3 bg-slate-50 rounded-2xl border border-transparent hover:border-indigo-100 hover:bg-white transition flex items-center gap-3">
                            <div class="h-8 w-8 flex items-center justify-center rounded-lg font-bold text-[10px] {{ $client->latestHealthScore?->getScoreBadgeClass() ?? 'bg-slate-200 text-slate-400' }}">
                                {{ $client->latestHealthScore?->overall_score ?? '?' }}
                            </div>
                            <span class="text-[10px] font-black text-slate-700 uppercase truncate">{{ $client->name }}</span>
                        </a>
                    @empty
                        <p class="text-xs text-slate-400 italic">No health data synced yet.</p>
                    @endforelse
                </div>
            </div>

            <!-- Morning Briefing Highlights -->
            <div class="p-10 bg-indigo-600 shadow-2xl rounded-[3rem] text-white relative group overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-500 to-violet-700 opacity-0 group-hover:opacity-100 transition duration-700"></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-8 text-indigo-200">
                        <h4 class="text-lg font-black text-white tracking-tight">Daily Briefing</h4>
                        <span class="text-[9px] font-black uppercase tracking-widest italic opacity-70">Awaiting Action</span>
                    </div>

                    @if($morning_briefing_preview)
                        <div class="space-y-4">
                            @foreach($morning_briefing_preview->actionItems as $item)
                                <div class="p-4 bg-white/10 rounded-2xl border border-white/5 backdrop-blur-sm">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $item->priority_level === 'urgent' ? 'bg-rose-400' : 'bg-amber-400' }}"></span>
                                        <p class="text-[9px] font-black uppercase tracking-widest text-indigo-100 truncate">{{ $item->client->name }}</p>
                                    </div>
                                    <p class="text-[11px] font-bold text-white leading-relaxed">{{ $item->title }}</p>
                                </div>
                            @endforeach
                            <x-button color="outline" class="w-full mt-4 rounded-2xl py-3 border-white/20 text-white font-black text-[9px]" href="{{ route('intelligence.briefing') }}">Execute Morning Strategy</x-button>
                        </div>
                    @else
                        <div class="py-6 text-center text-indigo-200 italic">
                            <p class="text-sm">Morning briefing will be delivered at 7:00 AM.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Anomalies -->
            <div class="p-10 bg-slate-900 shadow-2xl rounded-[3rem] text-white group relative overflow-hidden">
                <div class="absolute -left-10 -bottom-10 h-40 w-40 bg-indigo-500/5 rounded-full"></div>
                <h4 class="text-lg font-black text-white tracking-tight mb-8">Neural Exceptions</h4>
                
                <div class="space-y-4">
                    @forelse($recent_anomalies as $anomaly)
                        <div class="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/5 hover:bg-white/10 transition cursor-pointer" onclick="window.location='{{ route('intelligence.alerts') }}'">
                            <div class="flex items-center gap-4">
                                <div class="h-2 w-2 rounded-full {{ $anomaly->severity === 'critical' ? 'bg-rose-500 animate-pulse' : 'bg-amber-500' }}"></div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest">{{ $anomaly->client->name }}</p>
                                    <p class="text-[10px] font-bold text-slate-200 mt-0.5">{{ $anomaly->metric_name }} Outlier detected</p>
                                </div>
                            </div>
                            <span class="text-[9px] font-black {{ $anomaly->deviation_percentage > 0 ? 'text-rose-400' : 'text-emerald-400' }}">{{ $anomaly->deviation_percentage > 0 ? '+' : '' }}{{ round($anomaly->deviation_percentage) }}%</span>
                        </div>
                    @empty
                        <div class="py-8 text-center">
                            <span class="text-2xl grayscale opacity-30 mb-2 block">🧘</span>
                            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest tracking-[0.2em]">All Systems Nominal</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-container>