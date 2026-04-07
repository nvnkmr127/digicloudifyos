<x-app-container>
    <x-page-header title="Notification Center">
        <x-button color="outline">Mark All as Read</x-button>
    </x-page-header>

    <div class="max-w-4xl max-auto">
        <x-card class="bg-white border-0 shadow-lg rounded-2xl overflow-hidden p-0">
            <div class="divide-y divide-gray-50">
                @forelse($notifications as $notification)
                    <div class="p-6 hover:bg-gray-50 flex transition {{ $notification->read_at ? 'opacity-60' : '' }}">
                        <div class="mr-4">
                            <div class="h-10 w-10 rounded-xl flex items-center justify-center {{ $notification->read_at ? 'bg-gray-100 text-gray-400' : 'bg-primary-soft text-primary' }}">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <h3 class="text-sm font-bold text-gray-900">{{ $notification->data['title'] ?? 'System Notification' }}</h3>
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ $notification->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1 leading-relaxed">{{ $notification->data['message'] ?? 'No message body provided.' }}</p>
                            @if(isset($notification->data['action_url']))
                                <div class="mt-4">
                                    <a href="{{ $notification->data['action_url'] }}" class="text-xs font-bold text-primary hover:text-indigo-700 uppercase tracking-widest transition">
                                        {{ $notification->data['action_text'] ?? 'View Details' }} &rarr;
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-16 text-center">
                        <div class="h-24 w-24 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-6 text-gray-300 transform -rotate-12">
                            <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0l-4 4m-8-4l4 4m0 0l4-4m-4 4l-4-4"></path></svg>
                        </div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight">You're all caught up!</h3>
                        <p class="text-sm text-gray-500 mt-2">There are no new notifications assigned to your account right now.</p>
                    </div>
                @endforelse
            </div>
            @if($notifications->hasPages())
                <div class="p-6 border-t border-gray-50 bg-gray-50/30">
                    {{ $notifications->links() }}
                </div>
            @endif
        </x-card>
    </div>
</x-app-container>
