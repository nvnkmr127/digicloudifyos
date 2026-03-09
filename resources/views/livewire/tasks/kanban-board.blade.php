<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input 
                    wire:model.live.debounce.300ms="searchQuery"
                    type="text" 
                    placeholder="Search tasks..." 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
            </div>

            <select 
                wire:model.live="priorityFilter" 
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
                <option value="all">All Priorities</option>
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
            </select>

            <select 
                wire:model.live="assigneeFilter" 
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
                <option value="">All Assignees</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                @endforeach
            </select>

            @if($priorityFilter !== 'all' || $assigneeFilter || $searchQuery)
                <button 
                    wire:click="clearFilters"
                    class="px-4 py-2 text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg hover:bg-gray-50 transition"
                >
                    Clear Filters
                </button>
            @endif
        </div>
    </div>

    <div class="flex gap-4 overflow-x-auto pb-4" x-data="kanbanBoard()">
        @foreach($columns as $column)
            <div class="flex-shrink-0 w-80">
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-4 py-3 border-b border-gray-200 {{ $column['color'] }} rounded-t-lg">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-gray-700">{{ $column['title'] }}</h3>
                            <span class="px-2 py-1 text-xs font-medium bg-white rounded-full">
                                {{ count($tasks[$column['key']] ?? []) }}
                            </span>
                        </div>
                    </div>
                    
                    <div 
                        class="p-3 space-y-3 min-h-[200px]"
                        @drop.prevent="handleDrop($event, '{{ $column['key'] }}')"
                        @dragover.prevent
                        @dragenter.prevent="$event.target.classList.add('bg-blue-50')"
                        @dragleave.prevent="$event.target.classList.remove('bg-blue-50')"
                    >
                        @forelse($tasks[$column['key']] ?? [] as $task)
                            <div 
                                class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition cursor-move"
                                draggable="true"
                                @dragstart="handleDragStart($event, '{{ $task['id'] }}')"
                                @dragend="handleDragEnd($event)"
                            >
                                <div class="flex items-start justify-between mb-2">
                                    <h4 class="font-medium text-gray-900 flex-1 pr-2">
                                        {{ $task['title'] }}
                                    </h4>
                                    @if($task['priority'])
                                        <span class="px-2 py-1 text-xs font-semibold rounded
                                            @if($task['priority'] === 'urgent') bg-red-100 text-red-800
                                            @elseif($task['priority'] === 'high') bg-orange-100 text-orange-800
                                            @elseif($task['priority'] === 'medium') bg-yellow-100 text-yellow-800
                                            @else bg-green-100 text-green-800
                                            @endif
                                        ">
                                            {{ ucfirst($task['priority']) }}
                                        </span>
                                    @endif
                                </div>

                                @if($task['assignee'])
                                    <div class="flex items-center text-sm text-gray-600 mb-2">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        {{ $task['assignee']['full_name'] }}
                                    </div>
                                @endif

                                @if($task['deadline'])
                                    <div class="flex items-center text-xs text-gray-500 mt-2">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        {{ \Carbon\Carbon::parse($task['deadline'])->format('M d, Y') }}
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-400">
                                <p class="text-sm">No tasks</p>
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
                    document.querySelectorAll('.bg-blue-50').forEach(el => {
                        el.classList.remove('bg-blue-50');
                    });
                },

                handleDrop(event, newStatus) {
                    event.target.classList.remove('bg-blue-50');
                    
                    if (this.draggedTaskId) {
                        @this.updateTaskStatus(this.draggedTaskId, newStatus);
                        this.draggedTaskId = null;
                    }
                }
            }
        }
    </script>

    <div wire:loading class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
            <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-700 font-medium">Updating...</span>
        </div>
    </div>
</div>
