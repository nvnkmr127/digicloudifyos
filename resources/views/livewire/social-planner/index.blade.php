<x-app-container>
    <x-page-header title="Social Planner">
        <x-button color="outline" class="mr-2" x-on:click="$dispatch('open-modal', 'connect-channel-modal')">Connect
            Accounts</x-button>
        <x-button color="primary" x-on:click="$dispatch('open-modal', 'create-post-modal')">New Post</x-button>
    </x-page-header>

    @if (session()->has('message'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
            {{ session('message') }}
        </div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <div class="flex space-x-2">
            @forelse($channels as $channel)
                <span class="bg-blue-100 text-blue-800 rounded-full px-3 py-1 text-sm font-bold flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                    </svg>
                    {{ $channel->account_name }}
                </span>
            @empty
                <span class="text-sm text-gray-500">No channels connected yet.</span>
            @endforelse
            <button x-on:click="$dispatch('open-modal', 'connect-channel-modal')"
                class="bg-blue-50 text-blue-400 rounded-full border border-blue-200 px-3 py-1 text-sm font-bold flex items-center hover:bg-blue-100 transition">
                + Connect More
            </button>
        </div>
        <div class="flex space-x-2 text-sm bg-gray-100 p-1 rounded">
            <button wire:click="setViewMode('calendar')"
                class="{{ $viewMode === 'calendar' ? 'bg-white shadow-sm text-text-primary' : 'text-text-muted hover:text-text-primary' }} px-3 py-1.5 rounded font-medium">Calendar</button>
            <button wire:click="setViewMode('list')"
                class="{{ $viewMode === 'list' ? 'bg-white shadow-sm text-text-primary' : 'text-text-muted hover:text-text-primary' }} px-3 py-1.5 rounded font-medium">List
                View</button>
        </div>
    </div>

    @if($viewMode === 'calendar')
        <!-- Calendar View -->
        <x-card class="p-0 overflow-hidden">
            <div class="border-b border-gray-200 bg-gray-50 p-4 flex justify-between items-center">
                <h3 class="font-bold text-lg text-text-primary">{{ $currentMonthLabel }}</h3>
                <div class="flex space-x-2">
                    <button wire:click="previousMonth" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                            </path>
                        </svg></button>
                    <button wire:click="nextMonth" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg></button>
                </div>
            </div>
            <div
                class="grid grid-cols-7 border-b border-gray-200 bg-gray-50 text-center text-xs font-semibold text-gray-500 uppercase">
                <div class="py-2 border-r border-gray-200">Sun</div>
                <div class="py-2 border-r border-gray-200">Mon</div>
                <div class="py-2 border-r border-gray-200">Tue</div>
                <div class="py-2 border-r border-gray-200">Wed</div>
                <div class="py-2 border-r border-gray-200">Thu</div>
                <div class="py-2 border-r border-gray-200">Fri</div>
                <div class="py-2">Sat</div>
            </div>

            <div class="grid grid-cols-7 bg-gray-200 gap-px">
                @foreach($calendar as $dateString => $dayData)
                    <div class="bg-white min-h-[120px] p-2 hover:bg-gray-50 transition">
                        <span
                            class="{{ $dayData['isCurrentMonth'] ? 'text-text-primary font-bold' : 'text-gray-400' }} text-xs">
                            {{ $dayData['day'] }}
                        </span>

                        @foreach($dayData['posts'] as $post)
                            <div class="mt-2 bg-blue-100 border-l-2 border-blue-500 text-[10px] p-1 rounded text-blue-800 shadow-sm truncate"
                                title="{{ $post->content }}">
                                {{ $post->scheduled_at->format('g:i A') }} {{ str($post->content)->limit(15) }}
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </x-card>
    @else
        <!-- List View -->
        <x-card class="p-0 overflow-hidden">
            <div class="border-b border-gray-200 bg-gray-50 p-4 flex justify-between items-center">
                <h3 class="font-bold text-lg text-text-primary">Scheduled Posts</h3>
                <div class="flex space-x-2">
                    <button wire:click="previousMonth" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                            </path>
                        </svg></button>
                    <span class="text-sm font-medium text-text-muted mt-1">{{ $currentMonthLabel }}</span>
                    <button wire:click="nextMonth" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg></button>
                </div>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($listPosts as $post)
                    <div class="p-4 hover:bg-gray-50 flex justify-between items-center transition">
                        <div class="flex items-center space-x-4">
                            <div
                                class="h-10 w-10 flex-shrink-0 bg-gray-100 rounded flex items-center justify-center font-bold text-xs text-gray-500 uppercase">
                                {{ substr($post->channel->platform ?? '?', 0, 2) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-text-primary">{{ str($post->content)->limit(80) }}</p>
                                <p class="text-xs text-text-muted mt-1">
                                    {{ $post->scheduled_at->format('M d, Y g:i A') }} • to
                                    {{ $post->channel->account_name ?? 'Unknown' }}
                                </p>
                            </div>
                        </div>
                        <div>
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                Scheduled
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="p-10 text-center text-text-muted">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                            </path>
                        </svg>
                        <p>No posts scheduled for this month.</p>
                        <button x-on:click="$dispatch('open-modal', 'create-post-modal')"
                            class="mt-2 text-primary hover:underline text-sm font-medium">Create your first post</button>
                    </div>
                @endforelse
            </div>
        </x-card>
    @endif

    <!-- Create Post Modal -->
    <x-modal name="create-post-modal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-text-primary mb-4">Schedule New Post</h2>
            <form wire:submit="createPost" class="space-y-4">
                <div>
                    <x-input-label>Select Channels</x-input-label>
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach($channels as $channel)
                            <button type="button" wire:click="toggleChannelSelection('{{ $channel->id }}')"
                                class="px-3 py-2 border rounded text-sm flex items-center transition 
                                    {{ in_array($channel->id, $selectedChannels) ? 'bg-primary/5 border-primary text-primary font-medium' : 'hover:bg-gray-50 bg-white border-gray-300 text-gray-700' }}">
                                <span class="font-bold mr-1">{{ strtoupper(substr($channel->platform, 0, 2)) }}</span>
                                {{ $channel->account_name }}
                            </button>
                        @endforeach
                        @if($channels->isEmpty())
                            <div class="text-sm text-gray-500 py-2">No channels. Connect one first!</div>
                        @endif
                    </div>
                    <x-input-error :messages="$errors->get('selectedChannels')" class="mt-2" />
                </div>

                <div>
                    <x-input-label>Content</x-input-label>
                    <x-textarea wire:model="content" rows="4" placeholder="What do you want to share?"
                        class="w-full"></x-textarea>
                    <x-input-error :messages="$errors->get('content')" class="mt-2" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label>Date</x-input-label>
                        <x-text-input type="date" wire:model="scheduledDate" class="w-full" />
                        <x-input-error :messages="$errors->get('scheduledDate')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label>Time</x-input-label>
                        <x-text-input type="time" wire:model="scheduledTime" class="w-full" />
                        <x-input-error :messages="$errors->get('scheduledTime')" class="mt-2" />
                    </div>
                </div>

                <div class="flex justify-end space-x-2 mt-6">
                    <x-button type="button" color="outline" x-on:click="$dispatch('close')">Cancel</x-button>
                    <x-button type="submit" color="primary">Schedule Post</x-button>
                </div>
            </form>
        </div>
    </x-modal>

    <!-- Connect Channel Modal -->
    <x-modal name="connect-channel-modal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-text-primary mb-4">Connect Social Channel</h2>
            <div class="grid grid-cols-2 gap-4">
                <button wire:click="connectChannel('facebook')"
                    class="p-4 border rounded shadow-sm flex flex-col items-center hover:bg-gray-50 transition">
                    <div class="h-10 w-10 bg-blue-600 text-white rounded-full flex items-center justify-center mb-2">FB
                    </div>
                    <span class="font-medium">Facebook Page</span>
                </button>
                <button wire:click="connectChannel('instagram')"
                    class="p-4 border rounded shadow-sm flex flex-col items-center hover:bg-gray-50 transition">
                    <div class="h-10 w-10 bg-pink-600 text-white rounded-full flex items-center justify-center mb-2">IG
                    </div>
                    <span class="font-medium">Instagram</span>
                </button>
                <button wire:click="connectChannel('linkedin')"
                    class="p-4 border rounded shadow-sm flex flex-col items-center hover:bg-gray-50 transition">
                    <div class="h-10 w-10 bg-blue-800 text-white rounded-full flex items-center justify-center mb-2">IN
                    </div>
                    <span class="font-medium">LinkedIn</span>
                </button>
                <button wire:click="connectChannel('twitter')"
                    class="p-4 border rounded shadow-sm flex flex-col items-center hover:bg-gray-50 transition">
                    <div class="h-10 w-10 bg-black text-white rounded-full flex items-center justify-center mb-2">X
                    </div>
                    <span class="font-medium">X (Twitter)</span>
                </button>
            </div>
            <div class="flex justify-end mt-6">
                <x-button type="button" color="outline" x-on:click="$dispatch('close')">Cancel</x-button>
            </div>
        </div>
    </x-modal>
</x-app-container>