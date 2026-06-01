<x-app-container>
    <x-page-header title="Task Board">
        <div class="flex items-center space-x-3">
             <x-button color="primary" href="{{ route('tasks.create') }}" wire:navigate>
                + New Task
            </x-button>
        </div>
    </x-page-header>

    <div class="space-y-6">
        <x-card class="border-none shadow-sm p-4">
            <div class="flex flex-wrap gap-4 items-center">
                <div class="flex-1 min-w-[250px]">
                    <x-input 
                        wire:model.live.debounce.300ms="searchQuery"
                        type="text" 
                        placeholder="Search tasks by title or assignee..." 
                        aria-label="Search tasks"
                        class="rounded-xl"
                    />
                </div>

                <div class="min-w-[150px]">
                    <x-select wire:model.live="priorityFilter" class="rounded-xl">
                        <option value="all">All Priorities</option>
                        <option value="low">Low Priority</option>
                        <option value="medium">Medium Priority</option>
                        <option value="high">High Priority</option>
                        <option value="urgent">Urgent</option>
                    </x-select>
                </div>

                <div class="min-w-[150px]">
                    <x-select wire:model.live="assigneeFilter" class="rounded-xl">
                        <option value="">All Assignees</option>
                        @foreach($availableAssignees as $user)
                            <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                        @endforeach
                    </x-select>
                </div>

                @if($priorityFilter !== 'all' || $assigneeFilter || $searchQuery)
                    <x-button 
                        color="outline" 
                        wire:click="clearFilters"
                        class="rounded-xl"
                    >
                        Clear Filters
                    </x-button>
                @endif
            </div>
        </x-card>

        <div class="flex gap-6 overflow-x-auto pb-6 scrollbar-thin scrollbar-thumb-gray-200" x-data="kanbanBoard()">
            @foreach($columns as $column)
                <div class="flex-shrink-0 w-80">
                    <div class="bg-gray-50 border border-gray-100 rounded-3xl h-full flex flex-col shadow-inner">
                        <div class="px-5 py-4 flex items-center justify-between" id="column-header-{{ $column['key'] }}">
                            <h3 class="text-xs font-semibold text-text-muted uppercase tracking-wider">{{ $column['title'] }}</h3>
                            <span class="px-2.5 py-0.5 text-xs font-semibold {{ $column['color'] }} bg-white rounded-full shadow-sm" aria-label="{{ count($tasks[$column['key']] ?? []) }} tasks in {{ $column['title'] }}">
                                {{ count($tasks[$column['key']] ?? []) }}
                            </span>
                        </div>
                        
                        <div 
                            class="p-3 space-y-3 flex-1 min-h-[500px] transition-colors duration-200"
                            role="listbox"
                            aria-labelledby="column-header-{{ $column['key'] }}"
                            @drop.prevent="handleDrop($event, '{{ $column['key'] }}')"
                            @dragover.prevent
                            @dragenter.prevent="$event.target.closest('.flex-shrink-0').classList.add('bg-primary-soft/50')"
                            @dragleave.prevent="$event.target.closest('.flex-shrink-0').classList.remove('bg-primary-soft/50')"
                        >
                            @forelse($tasks[$column['key']] ?? [] as $task)
                                <div 
                                    class="bg-white border border-gray-100 rounded-card p-5 shadow-sm hover:shadow-md transition cursor-grab active:cursor-grabbing group relative focus:ring-2 focus:ring-primary focus:outline-none"
                                    draggable="true"
                                    role="option"
                                    tabindex="0"
                                    aria-label="Task: {{ $task['title'] }}. Click to view details or drag to change status."
                                    @dragstart="handleDragStart($event, '{{ $task['id'] }}')"
                                    @dragend="handleDragEnd($event)"
                                >
                                    <div class="flex items-start justify-between mb-3 gap-2">
                                        <h4 class="font-semibold text-text-primary leading-tight">
                                            {{ $task['title'] }}
                                        </h4>
                                        <div class="flex items-center gap-2">
                                            @if($task['priority'])
                                                @php
                                                    $priorityColors = [
                                                        'urgent' => 'bg-red-100 text-red-700 border-red-200',
                                                        'high' => 'bg-orange-100 text-orange-700 border-orange-200',
                                                        'medium' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                                        'low' => 'bg-green-100 text-green-700 border-green-200',
                                                    ];
                                                    $pColor = $priorityColors[strtolower($task['priority'])] ?? 'bg-gray-100 text-gray-700 border-gray-200';
                                                @endphp
                                                <span class="px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider rounded-md border {{ $pColor }}">
                                                    {{ $task['priority'] }}
                                                </span>
                                            @endif
                                            <div class="flex items-center gap-2">
                                                <button 
                                                    wire:click="deleteTask('{{ $task['id'] }}')" 
                                                    wire:confirm="Are you sure you want to delete this task?"
                                                    class="text-gray-300 hover:text-danger transition-colors"
                                                    title="Delete Task"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                                <a href="{{ route('tasks.show', $task['id']) }}" wire:navigate class="text-gray-300 hover:text-primary transition-colors" title="View Details">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-3">
                                        @if($task['assignee'])
                                            <div class="flex items-center text-xs font-semibold text-text-muted">
                                                <div class="w-6 h-6 rounded-full bg-primary-soft text-primary flex items-center justify-center mr-2 font-semibold text-xs">
                                                    {{ substr($task['assignee']['full_name'], 0, 1) }}
                                                </div>
                                                {{ $task['assignee']['full_name'] }}
                                            </div>
                                        @endif

                                        @if($task['deadline'])
                                            <div class="flex items-center text-xs font-semibold {{ \Carbon\Carbon::parse($task['deadline'])->isPast() ? 'text-danger' : 'text-text-muted' }}">
                                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                {{ \Carbon\Carbon::parse($task['deadline'])->format('M d') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <x-empty-state class="py-10" title="No tasks" description="Drag tasks into this stage or create a new task.">
                                    <x-slot name="actions">
                                        <x-button variant="outline" size="sm" href="{{ route('tasks.create') }}" wire:navigate>
                                            New Task
                                        </x-button>
                                    </x-slot>
                                </x-empty-state>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <script>
            function kanbanBoard() {
                return {
                    draggedTaskId: null,
                    handleDragStart(event, taskId) {
                        this.draggedTaskId = taskId;
                        event.dataTransfer.effectAllowed = 'move';
                        event.target.classList.add('opacity-50');
                    },
                    handleDragEnd(event) {
                        event.target.classList.remove('opacity-50');
                        document.querySelectorAll('.bg-primary-soft/50').forEach(el => el.classList.remove('bg-primary-soft/50'));
                    },
                    handleDrop(event, newStatus) {
                        document.querySelectorAll('.bg-primary-soft/50').forEach(el => el.classList.remove('bg-primary-soft/50'));
                        if (this.draggedTaskId) {
                            this.$wire.updateTaskStatus(this.draggedTaskId, newStatus)
                                .then(() => {
                                    this.draggedTaskId = null;
                                });
                        }
                    }
                }
            }
        </script>

        <div wire:loading class="fixed bottom-6 right-6 z-50">
            <div class="bg-gray-900/90 backdrop-blur-sm rounded-2xl p-4 flex items-center space-x-3 shadow-2xl">
                <svg class="animate-spin h-5 w-5 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-white font-semibold text-xs tracking-wider">Processing...</span>
            </div>
        </div>
    </div>
</x-app-container>
