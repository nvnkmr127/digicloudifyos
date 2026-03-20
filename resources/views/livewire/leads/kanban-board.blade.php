<x-app-container>
    <x-page-header title="Leads Pipeline">
        <div class="flex items-center space-x-3">
             <x-button color="primary" href="{{ route('leads.create') }}" wire:navigate class="rounded-xl shadow-md">
                + New Lead
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
                        placeholder="Search leads by name, email or phone..." 
                        class="rounded-xl"
                    />
                </div>

                <div class="min-w-[180px]">
                    <x-select wire:model.live="sourceFilter" class="rounded-xl">
                        <option value="all">All Sources</option>
                        <option value="Website">Website</option>
                        <option value="Referral">Referral</option>
                        <option value="Social Media">Social Media</option>
                        <option value="Email Campaign">Email Campaign</option>
                    </x-select>
                </div>

                @if($sourceFilter !== 'all' || $searchQuery)
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
                                {{ count($leads[$column['key']] ?? []) }}
                            </span>
                        </div>
                        
                        <div 
                            class="p-3 space-y-3 flex-1 min-h-[500px] transition-colors duration-200"
                            @drop.prevent="handleDrop($event, '{{ $column['key'] }}')"
                            @dragover.prevent
                            @dragenter.prevent="$event.target.closest('.flex-shrink-0').classList.add('bg-indigo-50/50')"
                            @dragleave.prevent="$event.target.closest('.flex-shrink-0').classList.remove('bg-indigo-50/50')"
                        >
                            @forelse($leads[$column['key']] ?? [] as $lead)
                                <a 
                                    href="{{ route('leads.show', $lead['id']) }}"
                                    wire:navigate
                                    class="block bg-white border border-gray-100 rounded-[1.5rem] p-5 shadow-sm hover:shadow-xl hover:scale-[1.02] hover:border-indigo-100 transition-all cursor-grab active:cursor-grabbing group relative overflow-hidden"
                                    draggable="true"
                                    @dragstart="handleDragStart($event, '{{ $lead['id'] }}')"
                                    @dragend="handleDragEnd($event)"
                                >
                                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-indigo-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                    
                                    <h4 class="font-black text-gray-900 mb-3 group-hover:text-indigo-600 transition-colors">
                                        {{ $lead['name'] }}
                                    </h4>

                                    <div class="space-y-2">
                                        @if($lead['email'])
                                            <div class="flex items-center text-[11px] font-bold text-gray-500 uppercase tracking-tight">
                                                <svg class="w-3.5 h-3.5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                </svg>
                                                {{ $lead['email'] }}
                                            </div>
                                        @endif

                                        @if($lead['phone'])
                                            <div class="flex items-center text-[11px] font-bold text-gray-400 uppercase tracking-tight">
                                                <svg class="w-3.5 h-3.5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                </svg>
                                                {{ $lead['phone'] }}
                                            </div>
                                        @endif
                                    </div>

                                    @if($lead['source'])
                                        <div class="mt-4 pt-4 border-t border-gray-50 flex items-center justify-between">
                                            <span class="text-[9px] font-black text-indigo-400 uppercase tracking-widest">{{ $lead['source'] }}</span>
                                            <svg class="w-4 h-4 text-gray-200 group-hover:text-indigo-200 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </div>
                                    @endif
                                </a>
                            @empty
                                <div class="text-center py-12 px-6">
                                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4a2 2 0 012-2m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                    </div>
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest leading-relaxed">No leads in this stage</p>
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
                        event.target.classList.add('scale-95');
                    },

                    handleDragEnd(event) {
                        event.target.classList.remove('opacity-50');
                        event.target.classList.remove('scale-95');
                        document.querySelectorAll('.bg-indigo-50/50').forEach(el => {
                            el.classList.remove('bg-indigo-50/50');
                        });
                    },

                    handleDrop(event, newStatus) {
                        document.querySelectorAll('.bg-indigo-50/50').forEach(el => {
                            el.classList.remove('bg-indigo-50/50');
                        });
                        
                        if (this.draggedLeadId) {
                            @this.updateLeadStatus(this.draggedLeadId, newStatus);
                            this.draggedLeadId = null;
                        }
                    }
                }
            }
        </script>

        <div wire:loading class="fixed bottom-6 right-6 z-50">
            <div class="bg-indigo-600 rounded-2xl p-4 flex items-center space-x-3 shadow-2xl shadow-indigo-200">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-white font-black text-xs uppercase tracking-widest">Syncing Cloud...</span>
            </div>
        </div>
    </div>
</x-app-container>

