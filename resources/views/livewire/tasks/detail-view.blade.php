<x-app-container>
    @if (session()->has('message'))
        <x-alert type="success" class="mb-4">
            {{ session('message') }}
        </x-alert>
    @endif

    @if (session()->has('error'))
        <x-alert type="error" class="mb-4">
            {{ session('error') }}
        </x-alert>
    @endif

    <div class="mb-4">
        <a href="{{ route('tasks.index') }}" wire:navigate class="text-sm text-text-muted hover:text-primary transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Tasks
        </a>
    </div>

    <x-page-header title="{{ $task->title }}">
        @php
            $taskStatus = strtolower($task->status ?? '');
            $taskStatusVariant = match ($taskStatus) {
                'completed' => 'success',
                'in_progress', 'in progress' => 'info',
                'blocked' => 'danger',
                default => 'neutral',
            };

            $priority = strtolower($task->priority ?? '');
            $priorityVariant = match ($priority) {
                'urgent' => 'danger',
                'high' => 'warning',
                'medium' => 'info',
                'low' => 'neutral',
                default => 'neutral',
            };
        @endphp

        <x-badge :variant="$priorityVariant" size="xs">{{ $task->priority ?? 'Normal' }}</x-badge>
        <x-badge :variant="$taskStatusVariant" size="xs">{{ $task->status ?? 'unknown' }}</x-badge>

        @if($task->status !== 'completed')
            <x-button variant="primary" wire:click="markComplete" wire:loading.attr="disabled">
                Mark Complete
            </x-button>
        @endif
    </x-page-header>

    <div class="space-y-8">
        <!-- Task Header Card -->
        <x-card>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <x-detail-label label="Assigned Operative">
                    <div class="flex items-center mt-1">
                        <div class="h-8 w-8 rounded-element bg-primary-soft text-sm font-semibold text-primary flex items-center justify-center mr-3">
                            {{ $task->assignee ? substr($task->assignee->full_name, 0, 1) : '?' }}
                        </div>
                        <span class="text-text-primary font-semibold">{{ $task->assignee->full_name ?? 'Unassigned' }}</span>
                    </div>
                </x-detail-label>

                <x-detail-label label="Execution Deadline">
                    <span class="text-gray-900 font-bold {{ $task->deadline && \Carbon\Carbon::parse($task->deadline)->isPast() ? 'text-rose-600' : '' }}">
                        {{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('M d, Y') : 'No Limit' }}
                    </span>
                </x-detail-label>

                <x-detail-label label="Specialization">
                    <span class="text-text-primary font-semibold">{{ str_replace('_', ' ', $task->task_type) }}</span>
                </x-detail-label>

                <x-detail-label label="Client Entity">
                    <span class="text-text-primary font-semibold">{{ $task->client->name ?? 'N/A' }}</span>
                </x-detail-label>
            </div>
        </x-card>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <x-card>
                    <h4 class="text-lg font-semibold text-text-primary mb-6 flex items-center">
                        <svg class="w-5 h-5 mr-3 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Technical Brief
                    </h4>
                    <div class="prose prose-indigo max-w-none text-gray-600 leading-relaxed font-medium">
                        {!! nl2br(e($task->description)) ?: '<span class="italic text-gray-400">No directives provided.</span>' !!}
                    </div>
                </x-card>

                <x-card>
                    <div class="flex justify-between items-center mb-8">
                        <h4 class="text-lg font-semibold text-text-primary flex items-center">
                            <svg class="w-5 h-5 mr-3 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Task Hierarchy
                        </h4>
                        <x-button color="outline" class="rounded-xl !py-1.5 !px-4 text-branding">
                            + Add Sub-task
                        </x-button>
                    </div>
                    <x-empty-state title="No sub-tasks yet" description="Sub-task support can be added when you’re ready." />
                </x-card>
            </div>

            <div class="space-y-6">
                <x-card>
                    <x-section title="Metadata" description="Created by and timestamps" />
                    <div class="mt-4 space-y-4 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-text-muted">Created by</span>
                            <span class="text-text-primary font-semibold">{{ $task->creator->full_name ?? 'System' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-text-muted">Created</span>
                            <span class="text-text-primary">{{ $task->created_at->format('M d, Y · H:i') }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-text-muted">Updated</span>
                            <span class="text-text-primary">{{ $task->updated_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </x-card>

                <x-card>
                    <x-section title="Activity" description="Recent activity" />
                    <div class="mt-4">
                        <div class="flex items-start gap-3">
                            <div class="h-2 w-2 rounded-full bg-primary mt-2"></div>
                            <div>
                                <div class="text-sm font-semibold text-text-primary">Task created</div>
                                <div class="text-xs text-text-muted">{{ $task->created_at->format('M d, Y · H:i') }}</div>
                            </div>
                        </div>
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</x-app-container>
