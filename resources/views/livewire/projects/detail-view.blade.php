<x-app-container>
    <div class="mb-4">
        <a href="{{ route('projects.index') }}" wire:navigate class="text-xs font-black text-gray-400 hover:text-indigo-600 uppercase tracking-widest transition-colors flex items-center">
            <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Matrix
        </a>
    </div>

    <x-page-header title="Project Intelligence" />

    <div class="space-y-8">
        <!-- Project Master Card -->
        <x-card variant="premium" class="shadow-indigo-100/20 shadow-xl">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8">
                <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-4 mb-6">
                        <span class="text-branding text-primary bg-primary-soft px-4 py-1.5 rounded-full border border-primary/10">
                            Protocol: {{ $project->project_code ?? 'STRAT-001' }}
                        </span>
                        <x-status-badge :status="$project->status" type="client" class="!px-4 !py-1.5 text-branding" />
                        <x-status-badge :status="$project->priority" type="lead" class="!px-4 !py-1.5 text-branding" />
                    </div>
                    <h2 class="text-5xl font-black text-gray-900 tracking-tight leading-tight mb-2">{{ $project->name }}</h2>
                    <p class="text-lg font-bold text-gray-400">Strategized for <span class="text-gray-900">{{ $project->client->name ?? 'Internal Initiative' }}</span></p>
                </div>

                <div class="flex items-center gap-4">
                    <x-button color="outline" href="{{ route('projects.edit', $project->id) }}" wire:navigate class="rounded-2xl px-8">
                        Edit Strategy
                    </x-button>
                    <x-button color="primary" class="rounded-2xl px-10 shadow-lg shadow-indigo-100 hover:scale-105 transition-all">
                        Launch Action
                    </x-button>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-10 mt-12 pt-12 border-t border-gray-50">
                <x-detail-label label="Assigned Strategist">
                    <div class="flex items-center mt-2">
                        <div class="h-10 w-10 rounded-2xl bg-indigo-600 flex items-center justify-center text-xs font-black text-white shadow-md mr-4">
                            {{ $project->projectManager ? substr($project->projectManager->full_name, 0, 1) : '?' }}
                        </div>
                        <div>
                            <p class="text-sm font-black text-gray-900 leading-none">{{ $project->projectManager->full_name ?? 'Unassigned' }}</p>
                            <p class="text-branding text-gray-400 mt-1">Lead PM</p>
                        </div>
                    </div>
                </x-detail-label>

                <x-detail-label label="Capital Allocation">
                    <p class="text-2xl font-black text-gray-900 mt-1">${{ number_format($project->budget, 0) }}</p>
                    <p class="text-branding text-gray-400 mt-1">{{ strtoupper($project->billing_type) }} MODEL</p>
                </x-detail-label>

                <x-detail-label label="Timeline Horizon">
                    <p class="text-lg font-black text-gray-900 mt-1">{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('M d') : 'Now' }} — {{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('M d, Y') : 'Open' }}</p>
                    <p class="text-branding text-gray-400 mt-1">Lifecycle</p>
                </x-detail-label>

                <x-detail-label label="Projected Burn">
                    <div class="mt-2">
                        @php
                            $progress = $project->budget > 0 ? min(100, ($project->actual_cost / $project->budget) * 100) : 0;
                            $pColor = $progress > 90 ? 'bg-rose-500' : ($progress > 70 ? 'bg-amber-500' : 'bg-indigo-600');
                        @endphp
                        <p class="text-lg font-black text-gray-900 mb-2">${{ number_format($project->actual_cost, 0) }} <span class="text-xs text-gray-400">Spent</span></p>
                        <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                            <div class="h-full {{ $pColor }} rounded-full transition-all duration-1000" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>
                </x-detail-label>
            </div>
        </x-card>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <!-- Strategic Scope -->
            <div class="lg:col-span-2 space-y-10">
                <x-card variant="premium" class="shadow-lg shadow-gray-100/50">
                    <h4 class="text-xl font-black text-gray-900 tracking-tight mb-8 flex items-center">
                        <svg class="w-6 h-6 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Project Directives
                    </h4>
                    <div class="prose prose-indigo max-w-none text-gray-600 font-medium leading-relaxed">
                        {!! nl2br(e($project->description)) ?: '<span class="italic text-gray-400">No strategic directives provided.</span>' !!}
                    </div>
                </x-card>

                <!-- Tasks / Milestones -->
                <x-card variant="premium" class="shadow-lg shadow-gray-100/50">
                    <div class="flex justify-between items-center mb-8">
                        <h4 class="text-xl font-black text-gray-900 tracking-tight flex items-center">
                            <svg class="w-6 h-6 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Operational Roadmap
                        </h4>
                        <x-button color="outline" class="rounded-xl !py-1.5 px-4 text-branding">
                            Issue New Task
                        </x-button>
                    </div>
                    
                    <div class="space-y-4">
                        @forelse($project->tasks as $task)
                            <div class="flex items-center justify-between p-6 bg-gray-50/50 rounded-3xl border border-gray-100/50 hover:bg-white hover:border-indigo-100 hover:shadow-xl transition-all group">
                                <div class="flex items-center gap-6">
                                    <div class="h-10 w-10 rounded-2xl bg-white border border-gray-100 flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-sm">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-gray-900 tracking-tight">{{ $task->title }}</p>
                                        <p class="text-branding text-gray-400 mt-1">Due: {{ $task->deadline ? $task->deadline->format('M d') : 'Open' }}</p>
                                    </div>
                                </div>
                                <x-status-badge :status="$task->status" type="client" class="!px-3 !py-1 text-branding" />
                            </div>
                        @empty
                            <div class="text-center py-20 bg-gray-50 border border-dashed border-gray-200 rounded-[2rem]">
                                <p class="text-[10px] font-black text-gray-300 uppercase tracking-[0.2em]">Zero Operational Tasks Recorded</p>
                            </div>
                        @endforelse
                    </div>
                </x-card>
            </div>

            <!-- Operational Insights -->
            <div class="space-y-10">
                <x-card variant="brand" class="p-8 shadow-lg shadow-primary-soft/30">
                    <h4 class="text-lg font-black tracking-tight mb-8">Metadata Analytics</h4>
                    <div class="space-y-8">
                        <div>
                            <label class="text-[10px] font-black text-indigo-200 uppercase tracking-widest block mb-3">Sync Statistics</label>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-white/10 p-4 rounded-2xl">
                                    <p class="text-2xl font-black">{{ $project->tasks->count() }}</p>
                                    <p class="text-branding text-primary-soft opacity-80 mt-1">Active Tasks</p>
                                </div>
                                <div class="bg-white/10 p-4 rounded-2xl">
                                    <p class="text-2xl font-black">{{ $project->campaigns->count() }}</p>
                                    <p class="text-branding text-primary-soft opacity-80 mt-1">Campaigns</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="text-[10px] font-black text-indigo-200 uppercase tracking-widest block mb-3">Initiation Point</label>
                            <p class="text-sm font-bold">{{ $project->created_at->format('M d, Y - H:i') }}</p>
                            <p class="text-branding text-primary-soft opacity-80 mt-1">Project Zero</p>
                        </div>
                        
                        <div class="pt-8 border-t border-white/10 flex justify-between items-center text-branding text-primary-soft opacity-80">
                            <span>Last Synchronization</span>
                            <span class="text-white">{{ $project->updated_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </x-card>

                <x-card variant="premium" class="p-8 shadow-lg shadow-gray-100/50 overflow-hidden relative">
                    <h4 class="text-lg font-black text-gray-900 tracking-tight mb-8">Resource Allocation</h4>
                    <div class="space-y-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-xl bg-gray-50 flex items-center justify-center border border-gray-100">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Strategic Billing</span>
                            </div>
                            <span class="text-xs font-black text-indigo-600">{{ strtoupper($project->billing_type) }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-xl bg-gray-50 flex items-center justify-center border border-gray-100">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Financial Growth</span>
                            </div>
                            <span class="text-xs font-black text-gray-900 leading-none">
                                ${{ number_format($project->budget + $project->actual_cost, 0) }}
                            </span>
                        </div>
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</x-app-container>
