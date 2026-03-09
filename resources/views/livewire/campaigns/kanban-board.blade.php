<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input 
                    wire:model.live.debounce.300ms="searchQuery"
                    type="text" 
                    placeholder="Search campaigns..." 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
            </div>

            <select 
                wire:model.live="statusFilter" 
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
                <option value="all">All Statuses</option>
                @foreach($columns as $column)
                    <option value="{{ $column['key'] }}">{{ $column['title'] }}</option>
                @endforeach
            </select>

            <select 
                wire:model.live="clientFilter" 
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
                <option value="">All Clients</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                @endforeach
            </select>

            @if($statusFilter !== 'all' || $clientFilter || $searchQuery)
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
                                {{ count($campaigns[$column['key']] ?? []) }}
                            </span>
                        </div>
                    </div>
                    
                    <div 
                        class="p-3 space-y-3 min-h-[200px]"
                        x-ref="column_{{ $column['key'] }}"
                        @drop.prevent="handleDrop($event, '{{ $column['key'] }}')"
                        @dragover.prevent
                        @dragenter.prevent="$event.target.classList.add('bg-blue-50')"
                        @dragleave.prevent="$event.target.classList.remove('bg-blue-50')"
                    >
                        @forelse($campaigns[$column['key']] ?? [] as $campaign)
                            <div 
                                class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition cursor-move"
                                draggable="true"
                                @dragstart="handleDragStart($event, '{{ $campaign['id'] }}')"
                                @dragend="handleDragEnd($event)"
                            >
                                <div class="flex items-start justify-between mb-2">
                                    <h4 class="font-medium text-gray-900 flex-1 pr-2">
                                        {{ $campaign['name'] }}
                                    </h4>
                                    <button 
                                        class="text-gray-400 hover:text-gray-600"
                                        onclick="window.location.href='/campaigns/{{ $campaign['id'] }}'"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                </div>

                                @if($campaign['client'])
                                    <div class="flex items-center text-sm text-gray-600 mb-2">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        {{ $campaign['client']['name'] }}
                                    </div>
                                @endif

                                @if($campaign['ad_account'])
                                    <div class="flex items-center text-xs text-gray-500 mb-2">
                                        <span class="px-2 py-1 bg-gray-100 rounded">
                                            {{ ucwords(str_replace('_', ' ', $campaign['ad_account']['platform'])) }}
                                        </span>
                                    </div>
                                @endif

                                <div class="flex items-center justify-between text-xs text-gray-500 mt-3 pt-3 border-t border-gray-100">
                                    @if($campaign['daily_budget'])
                                        <span class="font-medium text-green-600">
                                            ${{ number_format($campaign['daily_budget'], 2) }}/day
                                        </span>
                                    @endif

                                    @if($campaign['start_date'])
                                        <span>
                                            {{ \Carbon\Carbon::parse($campaign['start_date'])->format('M d, Y') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <p class="text-sm">No campaigns</p>
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
                draggedCampaignId: null,

                handleDragStart(event, campaignId) {
                    this.draggedCampaignId = campaignId;
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
                    
                    if (this.draggedCampaignId) {
                        @this.updateCampaignStatus(this.draggedCampaignId, newStatus);
                        this.draggedCampaignId = null;
                    }
                }
            }
        }
    </script>

    <div 
        wire:loading 
        class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50"
    >
        <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
            <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-700 font-medium">Updating...</span>
        </div>
    </div>
</div>
