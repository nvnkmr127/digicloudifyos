<x-app-container>
    <x-page-header title="Task Board">
        <div class="flex items-center space-x-3">
             <x-button color="primary" href="{{ route('tasks.create') }}" wire:navigate class="rounded-xl shadow-md">
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
                        @foreach($users as $user)
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
                        <div class="px-5 py-4 flex items-center justify-between">
                            <h3 class="font-black text-gray-700 uppercase tracking-widest text-[10px]">{{ $column['title'] }}</h3>
                            <span class="px-2.5 py-0.5 text-xs font-black {{ $column['color'] }} bg-white rounded-full shadow-sm">
                                {{ count($tasks[$column['key']] ?? []) }}
                            </span>
                        </div>
                        
                        <div 
                            class="p-3 space-y-3 flex-1 min-h-[500px] transition-colors duration-200"
                            @drop.prevent="handleDrop($event, '{{ $column['key'] }}')"
                            @dragover.prevent
                            @dragenter.prevent="$event.target.closest('.flex-shrink-0').classList.add('bg-indigo-50/50')"
                            @dragleave.prevent="$event.target.closest('.flex-shrink-0').classList.remove('bg-indigo-50/50')"
                        >
                            @forelse($tasks[$column['key']] ?? [] as $task)
                                <div 
                                    class="bg-white border border-gray-100 rounded-[1.5rem] p-5 shadow-sm hover:shadow-xl hover:scale-[1.02] hover:border-indigo-100 transition-all cursor-grab active:cursor-grabbing group relative"
                                    draggable="true"
                                    @dragstart="handleDragStart($event, '{{ $task['id'] }}')"
                                    @dragend="handleDragEnd($event)"
                                >
                                    <div class="flex items-start justify-between mb-3 gap-2">
                                        <h4 class="font-black text-gray-900 leading-tight group-hover:text-indigo-600 transition-colors">
                                            {{ $task['title'] }}
                                        </h4>
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
                                            <span class="px-2 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-md border {{ $pColor }}">
                                                {{ $task['priority'] }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="space-y-3">
                                        @if($task['assignee'])
                                            <div class="flex items-center text-[11px] font-bold text-gray-500 uppercase tracking-tight">
                                                <div class="w-6 h-6 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center mr-2 font-black text-[10px]">
                                                    {{ substr($task['assignee']['full_name'], 0, 1) }}
                                                </div>
                                                {{ $task['assignee']['full_name'] }}
                                            </div>
                                        @endif

                                        @if($task['deadline'])
                                            <div class="flex items-center text-[10px] font-bold {{ \Carbon\Carbon::parse($task['deadline'])->isPast() ? 'text-red-500' : 'text-gray-400' }} uppercase tracking-widest">
                                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                {{ \Carbon\Carbon::parse($task['deadline'])->format('M d') }}
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="absolute right-4 bottom-4 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <div class="w-2 h-2 rounded-full bg-indigo-600 animate-pulse"></div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-12 px-6">
                                    <p class="text-[10px] font-black text-gray-300 uppercase tracking-widest leading-relaxed">Empty Stage</p>
                                </div>
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
                        document.querySelectorAll('.bg-indigo-50/50').forEach(el => el.classList.remove('bg-indigo-50/50'));
                    },
                    handleDrop(event, newStatus) {
                        document.querySelectorAll('.bg-indigo-50/50').forEach(el => el.classList.remove('bg-indigo-50/50'));
                        if (this.draggedTaskId) {
                            @this.updateTaskStatus(this.draggedTaskId, newStatus);
                            this.draggedTaskId = null;
                        }
                    }
                }
            }
        </script>

        <div wire:loading class="fixed bottom-6 right-6 z-50">
            <div class="bg-gray-900 rounded-2xl p-4 flex items-center space-x-3 shadow-2xl">
                <svg class="animate-spin h-5 w-5 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-white font-black text-[10px] uppercase tracking-[0.2em]">Processing...</span>
            </div>
        </div>
    </div>
</x-app-container>

