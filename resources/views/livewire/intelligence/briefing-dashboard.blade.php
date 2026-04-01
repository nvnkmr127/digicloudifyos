<div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
    @if(!$briefing)
        <div class="p-12 text-center bg-white border-2 border-dashed rounded-xl border-slate-200">
            <div class="inline-flex items-center justify-center w-16 h-16 mb-4 bg-indigo-100 rounded-full text-indigo-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-900">No Intelligence Briefings Found</h3>
            <p class="mt-2 text-slate-500">Reports are usually generated every morning at 6:00 AM.</p>
        </div>
    @else
        <div class="flex flex-col gap-8 md:flex-row">
            <!-- Sidebar: Summary & Stats -->
            <div class="w-full md:w-80 shrink-0">
                <div class="sticky top-8 flex flex-col gap-6">
                    <div class="overflow-hidden bg-white border shadow-sm rounded-2xl border-slate-200">
                        <div class="px-6 py-6 text-white bg-gradient-to-br from-indigo-600 to-violet-700">
                            <h2 class="text-xl font-bold">Daily Briefing</h2>
                            <p class="mt-1 text-sm opacity-80">{{ $briefing->briefing_date->format('l, F j, Y') }}</p>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-slate-500">Clients Monitored</span>
                                    <span class="px-2 py-1 text-xs font-bold rounded-lg bg-slate-100 text-slate-700">{{ $briefing->total_clients_analyzed }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-slate-500">Critical Alerts</span>
                                    <span class="px-2 py-1 text-xs font-bold text-red-700 bg-red-100 rounded-lg">{{ $briefing->critical_alerts_count }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-slate-500">Opportunities</span>
                                    <span class="px-2 py-1 text-xs font-bold text-emerald-700 bg-emerald-100 rounded-lg">{{ $briefing->opportunities_count }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-white border shadow-sm rounded-2xl border-slate-200">
                        <h3 class="mb-4 text-xs font-bold tracking-widest text-slate-400 uppercase">Filters</h3>
                        <nav class="space-y-2">
                            <button wire:click="setTab('urgent')" 
                                class="flex items-center justify-between w-full px-4 py-3 text-sm font-semibold transition rounded-xl {{ $activeTab === 'urgent' ? 'bg-red-50 text-red-700 ring-1 ring-red-200' : 'text-slate-600 hover:bg-slate-50' }}">
                                <div class="flex items-center">
                                    <span class="mr-3">🔥</span>
                                    Urgent Risks
                                </div>
                                @if($briefing->critical_alerts_count > 0)
                                    <span class="w-5 h-5 text-[10px] flex items-center justify-center font-bold bg-red-600 text-white rounded-full">{{ $briefing->critical_alerts_count }}</span>
                                @endif
                            </button>
                            <button wire:click="setTab('important')" 
                                class="flex items-center justify-between w-full px-4 py-3 text-sm font-semibold transition rounded-xl {{ $activeTab === 'important' ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-200' : 'text-slate-600 hover:bg-slate-50' }}">
                                <div class="flex items-center">
                                    <span class="mr-3">⚡</span>
                                    Optimization
                                </div>
                            </button>
                            <button wire:click="setTab('opportunity')" 
                                class="flex items-center justify-between w-full px-4 py-3 text-sm font-semibold transition rounded-xl {{ $activeTab === 'opportunity' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'text-slate-600 hover:bg-slate-50' }}">
                                <div class="flex items-center">
                                    <span class="mr-3">🚀</span>
                                    Growth
                                </div>
                                @if($briefing->opportunities_count > 0)
                                    <span class="w-5 h-5 text-[10px] flex items-center justify-center font-bold bg-emerald-600 text-white rounded-full">{{ $briefing->opportunities_count }}</span>
                                @endif
                            </button>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Main Content: Action Items -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900">
                        @if($activeTab === 'urgent') Critical Interventions @elseif($activeTab === 'important') Performance Optimization @else Scalability & Growth @endif
                    </h2>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                        <span class="text-xs font-bold tracking-widest text-slate-400 uppercase">Live Intelligence</span>
                    </div>
                </div>

                @if(session()->has('message'))
                    <div class="p-4 mb-6 text-sm text-emerald-800 bg-emerald-100 rounded-xl border border-emerald-200">
                        {{ session('message') }}
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-6">
                    @forelse($items as $item)
                        <div class="relative overflow-hidden transition-all bg-white border border-slate-200 rounded-2xl group hover:shadow-lg hover:border-indigo-200 {{ $item->is_completed ? 'opacity-60 grayscale' : '' }}">
                            @if($item->is_completed)
                                <div class="absolute inset-0 z-10 flex items-center justify-center bg-white/40 backdrop-blur-[1px]">
                                    <div class="px-4 py-2 text-sm font-bold text-white shadow-xl bg-slate-800 rounded-lg">COMPLETED</div>
                                </div>
                            @endif
                            
                            <div class="p-8">
                                <div class="flex items-start justify-between gap-4 mb-4">
                                    <div>
                                        <div class="inline-flex items-center px-2 py-1 mb-3 text-[10px] font-extrabold tracking-widest text-indigo-700 uppercase bg-indigo-50 border border-indigo-100 rounded-md">
                                            {{ $item->client->name }}
                                        </div>
                                        <h3 class="text-xl font-bold text-slate-900 group-hover:text-indigo-600">{{ $item->title }}</h3>
                                    </div>
                                    <div class="shrink-0">
                                        <span class="px-3 py-1 text-xs font-bold leading-none border rounded-full {{ $item->getPriorityBadgeClass() }}">
                                            {{ strtoupper($item->priority_level) }}
                                        </span>
                                    </div>
                                </div>

                                <p class="mb-6 leading-relaxed text-slate-600">{{ $item->description }}</p>

                                <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                                    <div class="flex-1 space-y-3">
                                        <div class="p-4 bg-slate-50 rounded-xl border-l-4 border-indigo-500">
                                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Recommended Action</div>
                                            <p class="text-sm font-semibold text-slate-800">{{ $item->action }}</p>
                                        </div>
                                        
                                        <div class="flex items-center gap-6 mt-4">
                                            <div class="flex items-center text-xs">
                                                <span class="mr-2 text-slate-400">Impact:</span>
                                                <span class="font-bold text-slate-700 capitalize">{{ $item->expected_impact }}</span>
                                            </div>
                                            <div class="flex items-center text-xs">
                                                <span class="mr-2 text-slate-400">Effort:</span>
                                                <span class="font-bold text-slate-700 capitalize">{{ $item->effort }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    @if(!$item->is_completed)
                                        <button wire:click="completeItem('{{ $item->id }}')" 
                                            class="inline-flex items-center justify-center px-6 py-3 text-sm font-bold text-white transition-all bg-indigo-600 shadow-sm rounded-xl hover:bg-indigo-700 hover:shadow-indigo-200 focus:ring-4 focus:ring-indigo-100">
                                            Execute Task
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-12 text-center bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl">
                            <span class="text-3xl grayscale mb-4 block">🧘</span>
                            <h3 class="text-lg font-bold text-slate-900">All Clear</h3>
                            <p class="mt-1 text-slate-500">No pending items in this category today.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>
