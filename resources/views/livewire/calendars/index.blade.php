<x-app-container>
    <x-page-header title="Unified Event Calendar">
        <div class="flex items-center space-x-2">
            <button wire:click="previousMonth" class="p-2 hover:bg-gray-100 rounded-xl transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <h3 class="text-lg font-black text-gray-900 tracking-tight transition-all duration-300">{{ $currentMonthLabel }}</h3>
            <button wire:click="nextMonth" class="p-2 hover:bg-gray-100 rounded-xl transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </x-page-header>

    <div class="bg-white rounded-[3rem] shadow-2xl overflow-hidden border-none">
        <div class="grid grid-cols-7 bg-gray-50/50 border-b border-gray-100">
            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                <div class="py-4 text-center text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 border-r border-gray-100 last:border-0">
                    {{ $day }}
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-7 auto-rows-fr">
            @foreach($calendar as $date => $dayData)
                <div class="min-h-[140px] p-4 border-r border-b border-gray-100 last:border-r-0 hover:bg-gray-50/50 transition-all duration-300 {{ !$dayData['isCurrentMonth'] ? 'bg-gray-50/30' : '' }}">
                    <div class="flex justify-between items-start mb-3">
                        <span class="text-sm font-black {{ $dayData['isCurrentMonth'] ? 'text-gray-900' : 'text-gray-300' }}">
                            {{ $dayData['day'] }}
                        </span>
                        @if($date === now()->format('Y-m-d'))
                            <span class="h-2 w-2 bg-indigo-600 rounded-full animate-pulse shadow-sm shadow-indigo-100"></span>
                        @endif
                    </div>

                    <div class="space-y-2">
                        @foreach($dayData['items'] as $item)
                            <div class="px-3 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest leading-tight shadow-sm border border-transparent hover:border-{{ $item['color'] }}-200 transition bg-{{ $item['color'] }}-50 text-{{ $item['color'] }}-600 truncate" title="{{ $item['title'] }}">
                                {{ $item['title'] }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-container>