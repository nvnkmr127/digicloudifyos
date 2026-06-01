<x-app-container>
    <div class="mb-4">
        <a href="{{ route('projects.index') }}" wire:navigate class="text-sm text-text-muted hover:text-primary transition-colors flex items-center gap-2">
            <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Projects
        </a>
    </div>

    <x-page-header title="{{ $project->name }}">
        <x-button variant="outline" href="{{ route('projects.edit', $project->id) }}" wire:navigate>
            Edit Project
        </x-button>
    </x-page-header>

    <div class="space-y-8">
        <!-- Project Master Card -->
        <x-card class="mb-6">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8">
                <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-4 mb-6">
                        <x-badge variant="primary" size="xs">
                            {{ $project->project_code ?? 'Project' }}
                        </x-badge>
                        <x-status-badge :status="$project->status" type="client" />
                        @php
                            $priority = strtolower($project->priority ?? '');
                            $priorityVariant = match ($priority) {
                                'urgent' => 'danger',
                                'high' => 'warning',
                                'medium' => 'info',
                                'low' => 'neutral',
                                default => 'neutral',
                            };
                        @endphp
                        <x-badge :variant="$priorityVariant" size="xs">
                            {{ $project->priority ?? 'Normal' }}
                        </x-badge>
                    </div>
                    <p class="text-sm text-text-muted">
                        Client: <span class="text-text-primary font-semibold">{{ $project->client->name ?? 'Internal' }}</span>
                    </p>
                </div>

                <div class="flex items-center gap-3"></div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-10 mt-12 pt-12 border-t border-gray-50">
                <x-detail-label label="Assigned Strategist">
                    <div class="flex items-center mt-2">
                        <div class="h-10 w-10 rounded-element bg-primary-soft flex items-center justify-center text-sm font-semibold text-primary mr-4">
                            {{ $project->projectManager ? substr($project->projectManager->full_name, 0, 1) : '?' }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-text-primary leading-none">{{ $project->projectManager->full_name ?? 'Unassigned' }}</p>
                            <p class="text-branding text-gray-400 mt-1">Lead PM</p>
                        </div>
                    </div>
                </x-detail-label>

                <x-detail-label label="Capital Allocation">
                    <p class="text-lg font-semibold text-text-primary mt-1">${{ number_format($project->budget, 0) }}</p>
                    <p class="text-sm text-text-muted mt-1">{{ strtoupper($project->billing_type) }} model</p>
                </x-detail-label>

                <x-detail-label label="Timeline Horizon">
                    <p class="text-sm font-semibold text-text-primary mt-1">{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('M d') : 'Now' }} — {{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('M d, Y') : 'Open' }}</p>
                    <p class="text-sm text-text-muted mt-1">Lifecycle</p>
                </x-detail-label>

                <x-detail-label label="Projected Burn">
                    <div class="mt-2">
                        @php
                            $progress = $project->budget > 0 ? min(100, ($project->actual_cost / $project->budget) * 100) : 0;
                            $pColor = $progress > 90 ? 'bg-rose-500' : ($progress > 70 ? 'bg-amber-500' : 'bg-primary');
                        @endphp
                        <p class="text-sm font-semibold text-text-primary mb-2">${{ number_format($project->actual_cost, 0) }} <span class="text-xs text-text-muted">spent</span></p>
                        <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                            <div x-data="{ p: @js($progress) }" class="h-full {{ $pColor }} rounded-full transition-all duration-1000" :style="`width: ${p}%`"></div>
                        </div>
                    </div>
                </x-detail-label>
            </div>
        </x-card>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <!-- Strategic Scope -->
            <div class="lg:col-span-2 space-y-10">
                <x-card>
                    <h4 class="text-lg font-semibold text-text-primary mb-6 flex items-center">
                        <svg class="w-6 h-6 mr-3 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Project Directives
                    </h4>
                    <div class="prose prose-indigo max-w-none text-gray-600 font-medium leading-relaxed">
                        {!! nl2br(e($project->description)) ?: '<span class="italic text-gray-400">No strategic directives provided.</span>' !!}
                    </div>
                </x-card>

                <!-- Tasks / Milestones -->
                <x-card>
                    <div class="flex justify-between items-center mb-8">
                        <h4 class="text-lg font-semibold text-text-primary flex items-center">
                            <svg class="w-6 h-6 mr-3 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-card border border-gray-100 hover:bg-white transition">
                                <div class="flex items-center gap-6">
                                    <div class="h-10 w-10 rounded-element bg-white border border-gray-100 flex items-center justify-center text-primary transition shadow-sm">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-text-primary">{{ $task->title }}</p>
                                        <p class="text-sm text-text-muted mt-1">Due: {{ $task->deadline ? $task->deadline->format('M d') : 'Open' }}</p>
                                    </div>
                                </div>
                                @php
                                    $taskStatus = strtolower($task->status ?? '');
                                    $taskVariant = match ($taskStatus) {
                                        'completed' => 'success',
                                        'in_progress', 'in progress' => 'info',
                                        'blocked' => 'danger',
                                        default => 'neutral',
                                    };
                                @endphp
                                <x-badge :variant="$taskVariant" size="xs">{{ $task->status }}</x-badge>
                            </div>
                        @empty
                            <x-empty-state title="No tasks yet" description="Create a task to start tracking delivery work." />
                        @endforelse
                    </div>
                </x-card>
            </div>

            <!-- Operational Insights -->
            <div class="space-y-10">
                <x-card>
                    <x-section title="Overview" description="Project stats and timestamps" />
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div class="p-4 bg-gray-50 rounded-card border border-gray-100">
                            <div class="text-sm text-text-muted">Tasks</div>
                            <div class="text-lg font-semibold text-text-primary mt-1">{{ $project->tasks->count() }}</div>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-card border border-gray-100">
                            <div class="text-sm text-text-muted">Campaigns</div>
                            <div class="text-lg font-semibold text-text-primary mt-1">{{ $project->campaigns->count() }}</div>
                        </div>
                    </div>
                    <div class="mt-6 text-sm text-text-muted">
                        Created {{ $project->created_at->format('M d, Y · H:i') }} · Updated {{ $project->updated_at->diffForHumans() }}
                    </div>
                </x-card>

                <x-card>
                    <x-section title="Billing" description="Billing model and budget context" />
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
                            <x-badge variant="primary" size="xs">{{ strtoupper($project->billing_type) }}</x-badge>
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
                            <span class="text-xs font-semibold text-gray-900 leading-none">
                                ${{ number_format($project->budget + $project->actual_cost, 0) }}
                            </span>
                        </div>
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</x-app-container>
