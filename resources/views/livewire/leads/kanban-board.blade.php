<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input 
                    wire:model.live.debounce.300ms="searchQuery"
                    type="text" 
                    placeholder="Search leads..." 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
            </div>

            <select 
                wire:model.live="sourceFilter" 
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
                <option value="all">All Sources</option>
                <option value="Website">Website</option>
                <option value="Referral">Referral</option>
                <option value="Social Media">Social Media</option>
                <option value="Email Campaign">Email Campaign</option>
            </select>

            @if($sourceFilter !== 'all' || $searchQuery)
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
                                {{ count($leads[$column['key']] ?? []) }}
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
                        @forelse($leads[$column['key']] ?? [] as $lead)
                            <div 
                                class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition cursor-move"
                                draggable="true"
                                @dragstart="handleDragStart($event, '{{ $lead['id'] }}')"
                                @dragend="handleDragEnd($event)"
                            >
                                <h4 class="font-medium text-gray-900 mb-2">
                                    {{ $lead['name'] }}
                                </h4>

                                @if($lead['email'])
                                    <div class="flex items-center text-sm text-gray-600 mb-1">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        {{ $lead['email'] }}
                                    </div>
                                @endif

                                @if($lead['phone'])
                                    <div class="flex items-center text-sm text-gray-600 mb-1">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        {{ $lead['phone'] }}
                                    </div>
                                @endif

                                @if($lead['source'])
                                    <div class="mt-2 pt-2 border-t border-gray-100">
                                        <span class="text-xs text-gray-500">Source: {{ $lead['source'] }}</span>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-400">
                                <p class="text-sm">No leads</p>
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
                draggedLeadId: null,

                handleDragStart(event, leadId) {
                    this.draggedLeadId = leadId;
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
                    
                    if (this.draggedLeadId) {
                        @this.updateLeadStatus(this.draggedLeadId, newStatus);
                        this.draggedLeadId = null;
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
