<x-app-container>
    <x-page-header title="Creatives">
        <div class="flex items-center space-x-3">
            <x-button color="primary" wire:click="$set('showCreateModal', true)">
                + New Request
            </x-button>
        </div>
    </x-page-header>

    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="$set('showCreateModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-middle bg-white rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-8 pt-8 pb-6">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-xl leading-6 font-bold text-gray-900 mb-6" id="modal-title">New Creative Request</h3>
                                <form wire:submit.prevent="createRequest" class="space-y-5">
                                    <x-form-field label="Title" name="title">
                                        <x-input type="text" wire:model="title" placeholder="e.g. Summer Sale Facebook Banner" />
                                    </x-form-field>
                                    
                                    <x-form-field label="Description" name="description">
                                        <x-textarea wire:model="description" rows="3" placeholder="Provide details about the asset needed..." />
                                    </x-form-field>

                                    <div class="grid grid-cols-2 gap-4">
                                        <x-form-field label="Type" name="type">
                                            <x-select wire:model="type">
                                                <option value="Graphic">Graphic</option>
                                                <option value="Video">Video</option>
                                                <option value="Copy">Copy/Text</option>
                                                <option value="Animation">Animation</option>
                                            </x-select>
                                        </x-form-field>

                                        <x-form-field label="Priority" name="priority">
                                            <x-select wire:model="priority">
                                                <option value="Low">Low</option>
                                                <option value="Medium">Medium</option>
                                                <option value="High">High</option>
                                                <option value="Urgent">Urgent</option>
                                            </x-select>
                                        </x-form-field>
                                    </div>

                                    <x-form-field label="Deadline" name="deadline">
                                        <x-input type="date" wire:model="deadline" />
                                    </x-form-field>

                                    <div class="mt-8 flex flex-row-reverse gap-3">
                                        <x-button type="submit" color="primary" class="w-full sm:w-auto">Create Request</x-button>
                                        <x-button type="button" color="outline" class="w-full sm:w-auto" wire:click="$set('showCreateModal', false)">Cancel</x-button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="flex gap-6 overflow-x-auto pb-8 scrollbar-thin scrollbar-thumb-gray-200" x-data="creativeBoard()">
        @foreach($statusGroups as $key => $group)
            <div class="flex-shrink-0 w-80">
                <div class="bg-gray-50 border border-gray-100 rounded-[2rem] h-full flex flex-col shadow-inner">
                    <div class="px-6 py-5 flex items-center justify-between">
                        <h3 class="text-xs font-semibold text-text-muted uppercase tracking-wider">{{ $group['title'] }}</h3>
                        <span class="px-2.5 py-0.5 text-xs font-semibold {{ $group['text'] }} bg-white rounded-full shadow-sm">
                            {{ count($requests[$key] ?? []) }}
                        </span>
                    </div>

                    <div 
                        class="p-3 space-y-4 flex-1 min-h-[600px] transition-colors duration-200"
                        @drop.prevent="handleDrop($event, '{{ $key }}')"
                        @dragover.prevent
                        @dragenter.prevent="$event.target.closest('.flex-shrink-0').classList.add('bg-primary-soft/50')"
                        @dragleave.prevent="$event.target.closest('.flex-shrink-0').classList.remove('bg-primary-soft/50')"
                    >
                        @forelse($requests[$key] ?? [] as $request)
                            <div 
                                class="bg-white border border-gray-100 rounded-card p-5 shadow-sm hover:shadow-md transition cursor-grab active:cursor-grabbing group relative"
                                draggable="true"
                                @dragstart="handleDragStart($event, '{{ $request['id'] }}')"
                                @dragend="handleDragEnd($event)"
                            >
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex flex-col gap-1">
                                        <x-badge variant="info" size="xs">
                                            {{ $request['type'] }}
                                        </x-badge>
                                        @php
                                            $priority = strtolower($request['priority'] ?? '');
                                            $priorityVariant = match ($priority) {
                                                'urgent' => 'danger',
                                                'high' => 'warning',
                                                'medium' => 'info',
                                                'low' => 'neutral',
                                                default => 'neutral',
                                            };
                                        @endphp
                                        <x-badge :variant="$priorityVariant" size="xs">
                                            {{ $request['priority'] ?? 'Normal' }}
                                        </x-badge>
                                    </div>
                                    <button 
                                        wire:click="deleteRequest('{{ $request['id'] }}')" 
                                        wire:confirm="Are you sure you want to delete this creative request?"
                                        class="text-gray-200 hover:text-danger p-1 rounded-lg transition-colors"
                                        title="Delete Request"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>

                                <h5 class="font-semibold text-text-primary mb-2">
                                    {{ $request['title'] }}
                                </h5>

                                <p class="text-xs text-gray-500 font-medium line-clamp-2 leading-relaxed mb-4">
                                    {{ $request['description'] }}
                                </p>

                                <div class="pt-4 border-t border-gray-50 flex items-center justify-between">
                                    <div class="flex items-center">
                                        @if($request['assignee'])
                                            <div class="h-6 w-6 rounded-element bg-primary-soft flex items-center justify-center text-xs font-semibold text-primary mr-2">
                                                {{ substr($request['assignee']['full_name'], 0, 1) }}
                                            </div>
                                            <span class="text-xs font-semibold text-text-muted">{{ explode(' ', $request['assignee']['full_name'])[0] }}</span>
                                        @else
                                            <div class="h-6 w-6 rounded-element bg-gray-100 flex items-center justify-center text-xs font-semibold text-gray-500 mr-2 border border-gray-200">
                                                ?
                                            </div>
                                            <span class="text-xs font-semibold text-text-muted">Unassigned</span>
                                        @endif
                                    </div>
                                    <div class="text-xs font-semibold text-text-muted">
                                        {{ $request['deadline'] ? \Carbon\Carbon::parse($request['deadline'])->format('M d') : 'NO DATE' }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <x-empty-state class="py-10" title="No requests" description="Create a request to start this workflow." />
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        function creativeBoard() {
            return {
                draggedId: null,
                handleDragStart(event, id) {
                    this.draggedId = id;
                    event.dataTransfer.effectAllowed = 'move';
                    event.target.classList.add('opacity-50');
                },
                handleDragEnd(event) {
                    event.target.classList.remove('opacity-50');
                    document.querySelectorAll('.bg-primary-soft/50').forEach(el => el.classList.remove('bg-primary-soft/50'));
                },
                handleDrop(event, newStatus) {
                    document.querySelectorAll('.bg-primary-soft/50').forEach(el => el.classList.remove('bg-primary-soft/50'));
                    if (this.draggedId) {
                        this.$wire.updateStatus(this.draggedId, newStatus);
                        this.draggedId = null;
                    }
                }
            }
        }
    </script>

    <div wire:loading class="fixed bottom-6 right-6 z-50">
        <div class="bg-gray-900 rounded-2xl p-4 flex items-center space-x-3 shadow-2xl">
            <svg class="animate-spin h-5 w-5 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-white font-semibold text-[10px] uppercase tracking-[0.2em]">Updating...</span>
        </div>
    </div>
</x-app-container>
