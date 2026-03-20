<x-app-container>
    <div class="mb-4">
        <a href="{{ route('tasks.index') }}" wire:navigate class="text-xs font-black text-gray-400 hover:text-indigo-600 uppercase tracking-widest transition-colors flex items-center">
            <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Pipeline
        </a>
    </div>

    <x-page-header title="Task Directives" />

    <div class="space-y-8">
        <!-- Task Header Card -->
        <x-card class="p-8 md:p-12 rounded-card-premium border-none shadow-xl shadow-indigo-50/20">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-3 mb-6">
                        @if($task->campaign)
                            <span class="bg-indigo-50 text-indigo-700 text-branding px-4 py-1.5 rounded-full border border-indigo-100 shadow-sm">
                                Protocol: {{ $task->campaign->name }}
                            </span>
                        @else
                            <span class="bg-gray-50 text-gray-500 text-branding px-4 py-1.5 rounded-full border border-gray-100 italic">
                                Global Execution
                            </span>
                        @endif
                        
                        <x-status-badge :status="$task->priority" type="lead" class="!px-4 !py-1.5" />
                    </div>
                    <h3 class="text-4xl font-black text-gray-900 tracking-tight leading-none">{{ $task->title }}</h3>
                </div>
                
                <div class="flex items-center gap-4">
                    <x-status-badge :status="$task->status" type="client" class="!px-6 !py-2.5 !text-xs" />
                    
                    @if($task->status !== 'completed')
                        <x-button color="primary" class="rounded-2xl px-8 shadow-lg shadow-indigo-100 hover:scale-105">
                            Mark Complete
                        </x-button>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mt-12 pt-12 border-t border-gray-50">
                <x-detail-label label="Assigned Operative">
                    <div class="flex items-center mt-1">
                        <div class="h-8 w-8 rounded-xl bg-indigo-600 text-[10px] font-black text-white flex items-center justify-center mr-3 shadow-md">
                            {{ $task->assignee ? substr($task->assignee->full_name, 0, 1) : '?' }}
                        </div>
                        <span class="text-gray-900 font-bold">{{ $task->assignee->full_name ?? 'Unassigned' }}</span>
                    </div>
                </x-detail-label>

                <x-detail-label label="Execution Deadline">
                    <span class="text-gray-900 font-bold {{ $task->deadline && \Carbon\Carbon::parse($task->deadline)->isPast() ? 'text-rose-600' : '' }}">
                        {{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('M d, Y') : 'No Limit' }}
                    </span>
                </x-detail-label>

                <x-detail-label label="Specialization">
                    <span class="text-gray-900 font-bold uppercase tracking-tight">{{ str_replace('_', ' ', $task->task_type) }}</span>
                </x-detail-label>

                <x-detail-label label="Client Entity">
                    <span class="text-gray-900 font-bold">{{ $task->client->name ?? 'N/A' }}</span>
                </x-detail-label>
            </div>
        </x-card>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <x-card class="p-10 rounded-card-premium border-none shadow-lg shadow-gray-100/50">
                    <h4 class="text-xl font-black text-gray-900 tracking-tight mb-8 flex items-center">
                        <svg class="w-5 h-5 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Technical Brief
                    </h4>
                    <div class="prose prose-indigo max-w-none text-gray-600 leading-relaxed font-medium">
                        {!! nl2br(e($task->description)) ?: '<span class="italic text-gray-400">No directives provided.</span>' !!}
                    </div>
                </x-card>

                <x-card class="p-10 rounded-card-premium border-none shadow-lg shadow-gray-100/50">
                    <div class="flex justify-between items-center mb-8">
                        <h4 class="text-xl font-black text-gray-900 tracking-tight flex items-center">
                            <svg class="w-5 h-5 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Task Hierarchy
                        </h4>
                        <x-button color="outline" class="rounded-xl !py-1.5 !px-4 text-branding">
                            + Add Sub-task
                        </x-button>
                    </div>
                    <div class="py-12 text-center rounded-3xl bg-gray-50 border border-dashed border-gray-200">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Roadmap Is Currently Linear</p>
                    </div>
                </x-card>
            </div>

            <div class="space-y-8">
                <x-card class="p-8 rounded-[2rem] border-none shadow-lg shadow-indigo-50/30 bg-gradient-to-br from-indigo-600 to-indigo-700 text-white">
                    <h4 class="text-lg font-black tracking-tight mb-8">Metadata</h4>
                    <div class="space-y-6">
                        <div>
                            <label class="text-[10px] font-black text-indigo-200 uppercase tracking-widest block mb-2">Initiated By</label>
                            <div class="flex items-center">
                                <div class="h-8 w-8 rounded-lg bg-white/10 flex items-center justify-center text-[10px] font-black mr-3">
                                    {{ $task->creator ? substr($task->creator->full_name, 0, 1) : 'S' }}
                                </div>
                                <span class="font-bold">{{ $task->creator->full_name ?? 'Protocol System' }}</span>
                            </div>
                        </div>
                        
                        <div>
                            <label class="text-[10px] font-black text-indigo-200 uppercase tracking-widest block mb-2">Creation Timestamp</label>
                            <span class="font-bold text-sm">{{ $task->created_at->format('M d, Y - H:i') }}</span>
                        </div>

                        <div>
                            <label class="text-[10px] font-black text-indigo-200 uppercase tracking-widest block mb-2">Last Synchronized</label>
                            <span class="font-bold text-sm">{{ $task->updated_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </x-card>

                <x-card class="p-8 rounded-[2rem] border-none shadow-lg shadow-gray-100/50">
                    <h4 class="text-lg font-black text-gray-900 tracking-tight mb-6">Activity Protocol</h4>
                    <div class="space-y-4">
                        <div class="flex gap-4">
                            <div class="w-0.5 bg-gray-100 relative">
                                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-2 h-2 rounded-full bg-indigo-600"></div>
                            </div>
                            <div class="pb-6">
                                <p class="text-xs font-bold text-gray-900">Task Protocol Initiated</p>
                                <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mt-1">{{ $task->created_at->format('h:i A') }}</p>
                            </div>
                        </div>
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</x-app-container>