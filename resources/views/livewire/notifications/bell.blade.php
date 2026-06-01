<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" class="relative p-2 text-gray-500 hover:text-primary transition duration-150 focus:outline-none" aria-label="Notifications">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        @if($unreadCount > 0)
            <span class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-danger rounded-full border-2 border-white">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div x-show="open" @click.away="open = false" 
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-2xl border border-gray-100 z-50 overflow-hidden" 
        x-cloak>
        <div class="p-4 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
            <h3 class="text-sm font-bold text-gray-800">Notifications</h3>
            <span class="text-[10px] text-text-muted uppercase tracking-wider font-semibold">{{ $unreadCount }} Unread</span>
        </div>
        <div class="max-h-96 overflow-y-auto custom-scrollbar">
            @forelse($notifications as $notification)
                <div class="p-4 border-b border-gray-50 hover:bg-gray-50 transition duration-150 cursor-pointer {{ $notification->is_read ? 'opacity-60' : '' }}"
                    wire:click="markAsRead('{{ $notification->id }}')">
                    <div class="flex gap-3">
                        <div class="h-8 w-8 rounded-xl flex-shrink-0 flex items-center justify-center {{ $notification->is_read ? 'bg-gray-100 text-gray-400' : 'bg-primary-soft text-primary' }}">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-xs font-bold text-gray-900">{{ $notification->title }}</h4>
                            <p class="text-xs text-text-muted mt-1 leading-relaxed">{{ $notification->message }}</p>
                            <span class="text-[10px] text-text-muted mt-2 block">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center">
                    <svg class="mx-auto h-8 w-8 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0l-4 4m-8-4l4 4m0 0l4-4m-4 4l-4-4"></path>
                    </svg>
                    <p class="mt-2 text-xs text-text-muted">No new notifications</p>
                </div>
            @endforelse
        </div>
        <div class="p-3 bg-gray-50 text-center border-t border-gray-100">
            <a href="{{ route('notifications.index') }}" class="text-[10px] font-bold text-primary hover:text-primary-hover uppercase tracking-widest">View All Notifications</a>
        </div>
    </div>
</div>
