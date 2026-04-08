<div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
    <!-- Filter Header -->
    <div class="flex flex-col gap-6 mb-8 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-slate-100">AI Intelligence Feed</h1>
            <p class="mt-1 text-slate-500 dark:text-slate-400">Prioritized recommendations and root cause analysis across all channels.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <select wire:model.live="clientFilter" class="px-4 py-2 text-sm font-semibold transition bg-white border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100 dark:focus:ring-indigo-900/40 dark:focus:border-indigo-500">
                <option value="">All Clients</option>
                @foreach($clients as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
            
            <select wire:model.live="priorityFilter" class="px-4 py-2 text-sm font-semibold transition bg-white border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100 dark:focus:ring-indigo-900/40 dark:focus:border-indigo-500">
                <option value="">Any Priority</option>
                <option value="critical">Critical</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="opportunity">Opportunity</option>
            </select>

            <button wire:click="$toggle('showCompleted')" class="px-4 py-2 text-sm font-bold transition rounded-xl border {{ $showCompleted ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50 dark:bg-slate-900 dark:text-slate-200 dark:border-slate-700 dark:hover:bg-slate-800' }}">
                Show Resolved
            </button>
        </div>
    </div>

    <!-- Insights List -->
    <div class="grid grid-cols-1 gap-6">
        @forelse($insights as $insight)
            <div class="overflow-hidden transition-all bg-white border shadow-sm rounded-3xl border-slate-200 hover:shadow-md hover:border-indigo-200 dark:bg-slate-900 dark:border-slate-800 dark:hover:border-indigo-700 {{ $insight->is_completed ? 'opacity-60' : '' }}">
                <div class="p-8">
                    <div class="flex items-start justify-between gap-4 mb-6">
                        <div class="flex flex-wrap items-center gap-2">
                            <x-intelligence.priority-badge :priority="$insight->priority" />
                            <span class="text-xs font-bold tracking-widest text-slate-400 uppercase dark:text-slate-500">{{ $insight->client->name }}</span>
                            @if($insight->channel_type)
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px] font-bold uppercase dark:bg-slate-800 dark:text-slate-200">{{ str_replace('_', ' ', $insight->channel_type) }}</span>
                            @endif
                        </div>
                        <div class="text-xs font-bold text-slate-400 dark:text-slate-500">
                            {{ $insight->insight_date->format('M j, Y') }}
                        </div>
                    </div>

                    <div class="flex flex-col gap-8 lg:flex-row">
                        <div class="flex-1">
                            <h3 class="text-xl font-black text-slate-900 mb-3 dark:text-slate-100">{{ $insight->title }}</h3>
                            <p class="text-sm leading-relaxed text-slate-600 mb-6 dark:text-slate-300">{{ $insight->issue_description }}</p>

                            @if($insight->root_cause)
                                <div class="mb-6">
                                    <h4 class="mb-2 text-[10px] font-black tracking-widest text-slate-400 uppercase dark:text-slate-500">Likely Root Cause</h4>
                                    <p class="p-4 text-sm font-medium italic bg-slate-50 rounded-2xl text-slate-600 dark:bg-slate-950/40 dark:text-slate-300">"{{ $insight->root_cause }}"</p>
                                </div>
                            @endif
                        </div>

                        <div class="w-full lg:w-96 shrink-0">
                            <div class="p-6 h-full bg-gradient-to-br from-indigo-50 to-violet-50 rounded-2xl border border-indigo-100 relative group dark:from-indigo-950/40 dark:to-violet-950/40 dark:border-indigo-900/50">
                                <h4 class="mb-4 text-xs font-black tracking-widest text-indigo-500 uppercase dark:text-indigo-300">Recommended Action</h4>
                                <p class="text-sm font-bold leading-relaxed text-indigo-900 mb-6 dark:text-indigo-100">{{ $insight->recommended_action }}</p>
                                
                                <div class="flex flex-wrap items-center gap-4 mt-auto">
                                    <div>
                                        <span class="block text-[10px] font-black text-indigo-300 uppercase tracking-widest dark:text-indigo-400">Impact</span>
                                        <span class="text-xs font-bold text-indigo-700 capitalize dark:text-indigo-200">{{ $insight->expected_impact }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-[10px] font-black text-indigo-300 uppercase tracking-widest dark:text-indigo-400">Effort</span>
                                        <span class="text-xs font-bold text-indigo-700 capitalize dark:text-indigo-200">{{ $insight->effort_level }}</span>
                                    </div>
                                    
                                    @if(!$insight->is_completed)
                                        <div class="ml-auto flex items-center gap-2">
                                            <button wire:click="dismiss('{{ $insight->id }}')" class="p-2 text-slate-400 hover:text-red-600 transition dark:text-slate-500 dark:hover:text-red-400" title="Dismiss">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                            <button wire:click="complete('{{ $insight->id }}')" class="p-2 text-slate-400 hover:text-emerald-600 transition dark:text-slate-500 dark:hover:text-emerald-400" title="Mark Resolved">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            </button>
                                        </div>
                                    @else
                                        <div class="ml-auto">
                                            <span class="px-3 py-1 bg-indigo-600 text-white text-[10px] font-black rounded-lg uppercase tracking-widest">Done</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center p-24 bg-white border border-dashed border-slate-200 rounded-3xl text-center dark:bg-slate-900 dark:border-slate-800">
                <div class="p-4 mb-4 bg-indigo-50 rounded-full text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-300">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900 dark:text-slate-100">No Insights Yet</h3>
                <p class="mt-2 text-slate-500 dark:text-slate-400">When your accounts show unusual performance shifts, AI insights will appear here.</p>
            </div>
        @endforelse

        <div class="mt-8">
            {{ $insights->links() }}
        </div>
    </div>
</div>
