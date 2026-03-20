<x-app-container>
    <x-page-header title="Creative Assets Protocol">
        <div class="flex items-center space-x-3">
            <x-button color="primary" class="rounded-xl shadow-md">
                + New Asset Request
            </x-button>
        </div>
    </x-page-header>

    <div class="flex gap-6 overflow-x-auto pb-8 scrollbar-thin scrollbar-thumb-gray-200" x-data="creativeBoard()">
        @foreach($statusGroups as $key => $group)
            <div class="flex-shrink-0 w-80">
                <div class="bg-gray-50 border border-gray-100 rounded-[2rem] h-full flex flex-col shadow-inner">
                    <div class="px-6 py-5 flex items-center justify-between">
                        <h3 class="font-black text-gray-700 uppercase tracking-widest text-[10px]">{{ $group['title'] }}</h3>
                        <span class="px-2.5 py-0.5 text-xs font-black {{ $group['text'] }} bg-white rounded-full shadow-sm">
                            {{ count($requests[$key] ?? []) }}
                        </span>
                    </div>

                    <div 
                        class="p-3 space-y-4 flex-1 min-h-[600px] transition-colors duration-200"
                        @drop.prevent="handleDrop($event, '{{ $key }}')"
                        @dragover.prevent
                        @dragenter.prevent="$event.target.closest('.flex-shrink-0').classList.add('bg-indigo-50/50')"
                        @dragleave.prevent="$event.target.closest('.flex-shrink-0').classList.remove('bg-indigo-50/50')"
                    >
                        @forelse($requests[$key] ?? [] as $request)
                            <div 
                                class="bg-white border border-gray-100 rounded-[1.5rem] p-5 shadow-sm hover:shadow-xl hover:scale-[1.02] hover:border-indigo-100 transition-all cursor-grab active:cursor-grabbing group relative"
                                draggable="true"
                                @dragstart="handleDragStart($event, '{{ $request['id'] }}')"
                                @dragend="handleDragEnd($event)"
                            >
                                <div class="flex justify-between items-start mb-4">
                                    <span class="text-[9px] uppercase font-black px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100/50 tracking-widest">
                                        {{ $request['type'] }}
                                    </span>
                                    <x-status-badge :status="$request['priority']" type="lead" class="!px-2 !py-0.5 !text-[8px]" />
                                </div>

                                <h5 class="font-black text-gray-900 tracking-tight group-hover:text-indigo-600 transition-colors mb-2">
                                    {{ $request['title'] }}
                                </h5>

                                <p class="text-xs text-gray-500 font-medium line-clamp-2 leading-relaxed mb-4">
                                    {{ $request['description'] }}
                                </p>

                                <div class="pt-4 border-t border-gray-50 flex items-center justify-between">
                                    <div class="flex items-center">
                                        @if($request['assignee'])
                                            <div class="h-6 w-6 rounded-lg bg-indigo-600 flex items-center justify-center text-[10px] font-black text-white shadow-sm mr-2">
                                                {{ substr($request['assignee']['full_name'], 0, 1) }}
                                            </div>
                                            <span class="text-[10px] font-bold text-gray-400 capitalize">{{ explode(' ', $request['assignee']['full_name'])[0] }}</span>
                                        @else
                                            <div class="h-6 w-6 rounded-lg bg-gray-100 flex items-center justify-center text-[10px] font-black text-gray-400 mr-2 border border-gray-200">
                                                ?
                                            </div>
                                            <span class="text-[10px] font-bold text-gray-300">Unassigned</span>
                                        @endif
                                    </div>
                                    <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest">
                                        {{ $request['deadline'] ? \Carbon\Carbon::parse($request['deadline'])->format('M d') : 'NO DATE' }}
                                    </div>
                                </div>
                                
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <div class="w-1 h-8 rounded-full bg-indigo-600/20"></div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-16 px-6">
                                <p class="text-[10px] font-black text-gray-300 uppercase tracking-[0.2em] leading-relaxed">Pipeline Segment Empty</p>
                            </div>
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
                    document.querySelectorAll('.bg-indigo-50/50').forEach(el => el.classList.remove('bg-indigo-50/50'));
                },
                handleDrop(event, newStatus) {
                    document.querySelectorAll('.bg-indigo-50/50').forEach(el => el.classList.remove('bg-indigo-50/50'));
                    if (this.draggedId) {
                        @this.updateStatus(this.draggedId, newStatus);
                        this.draggedId = null;
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
            <span class="text-white font-black text-[10px] uppercase tracking-[0.2em]">Updating...</span>
        </div>
    </div>
</x-app-container>