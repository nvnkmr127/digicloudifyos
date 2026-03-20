<x-app-container>
    <x-page-header title="Campaign Pipeline">
        <div class="flex items-center space-x-3">
             <x-button color="primary" href="{{ route('campaigns.create') }}" wire:navigate class="rounded-xl shadow-md">
                + New Campaign
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
                        placeholder="Search campaigns by name..." 
                        class="rounded-xl"
                    />
                </div>

                <div class="min-w-[150px]">
                    <x-select wire:model.live="statusFilter" class="rounded-xl">
                        <option value="all">All Statuses</option>
                        @foreach($columns as $column)
                            <option value="{{ $column['key'] }}">{{ $column['title'] }}</option>
                        @endforeach
                    </x-select>
                </div>

                <div class="min-w-[150px]">
                    <x-select wire:model.live="clientFilter" class="rounded-xl">
                        <option value="">All Clients</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </x-select>
                </div>

                @if($statusFilter !== 'all' || $clientFilter || $searchQuery)
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
                                {{ count($campaigns[$column['key']] ?? []) }}
                            </span>
                        </div>
                        
                        <div 
                            class="p-3 space-y-3 flex-1 min-h-[500px] transition-colors duration-200"
                            @drop.prevent="handleDrop($event, '{{ $column['key'] }}')"
                            @dragover.prevent
                            @dragenter.prevent="$event.target.closest('.flex-shrink-0').classList.add('bg-indigo-50/50')"
                            @dragleave.prevent="$event.target.closest('.flex-shrink-0').classList.remove('bg-indigo-50/50')"
                        >
                            @forelse($campaigns[$column['key']] ?? [] as $campaign)
                                <div 
                                    class="bg-white border border-gray-100 rounded-[1.5rem] p-5 shadow-sm hover:shadow-xl hover:scale-[1.02] hover:border-indigo-100 transition-all cursor-grab active:cursor-grabbing group relative"
                                    draggable="true"
                                    @dragstart="handleDragStart($event, '{{ $campaign['id'] }}')"
                                    @dragend="handleDragEnd($event)"
                                >
                                    <div class="flex items-start justify-between mb-3 gap-2">
                                        <h4 class="font-black text-gray-900 leading-tight group-hover:text-indigo-600 transition-colors">
                                            {{ $campaign['name'] }}
                                        </h4>
                                        <a href="{{ route('campaigns.show', $campaign['id']) }}" wire:navigate class="text-gray-300 hover:text-indigo-600 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    </div>

                                    <div class="space-y-3">
                                        @if($campaign['client'])
                                            <div class="flex items-center text-[11px] font-bold text-gray-500 uppercase tracking-tight">
                                                <div class="w-6 h-6 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center mr-2 font-black text-[10px]">
                                                    {{ substr($campaign['client']['name'], 0, 1) }}
                                                </div>
                                                {{ $campaign['client']['name'] }}
                                            </div>
                                        @endif

                                        @if($campaign['ad_account'])
                                            <div class="flex items-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                                <span class="px-2 py-0.5 rounded-md border border-gray-100 bg-gray-50 mr-2">
                                                    {{ str_replace('_', ' ', $campaign['ad_account']['platform']) }}
                                                </span>
                                            </div>
                                        @endif

                                        <div class="flex items-center justify-between pt-3 border-t border-gray-50 font-black text-[10px] uppercase tracking-widest">
                                            @if($campaign['daily_budget'])
                                                <span class="text-green-600">
                                                    ${{ number_format($campaign['daily_budget'], 2) }}/D
                                                </span>
                                            @endif

                                            @if($campaign['start_date'])
                                                <span class="text-gray-400">
                                                    {{ \Carbon\Carbon::parse($campaign['start_date'])->format('M d') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="absolute right-4 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <div class="w-1 h-8 rounded-full bg-indigo-600/20"></div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-12 px-6">
                                    <p class="text-[10px] font-black text-gray-300 uppercase tracking-widest leading-relaxed">No active campaigns</p>
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
                        document.querySelectorAll('.bg-indigo-50/50').forEach(el => el.classList.remove('bg-indigo-50/50'));
                    },
                    handleDrop(event, newStatus) {
                        document.querySelectorAll('.bg-indigo-50/50').forEach(el => el.classList.remove('bg-indigo-50/50'));
                        if (this.draggedCampaignId) {
                            @this.updateCampaignStatus(this.draggedCampaignId, newStatus);
                            this.draggedCampaignId = null;
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
                <span class="text-white font-black text-[10px] uppercase tracking-[0.2em]">Syncing...</span>
            </div>
        </div>
    </div>
</x-app-container>

