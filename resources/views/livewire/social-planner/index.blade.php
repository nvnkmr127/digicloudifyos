<x-app-container>
    <x-page-header title="Omnichannel Content Planner">
        <x-button color="outline" class="mr-3 rounded-2xl border-gray-200 px-8" wire:click="$toggle('showConnectModal')">
            Connect Engine
        </x-button>
        <x-button color="primary" class="rounded-2xl shadow-lg px-8 shadow-indigo-100" wire:click="$toggle('showCreateModal')">
            + New Post Logic
        </x-button>
    </x-page-header>

    @if (session()->has('message'))
        <div class="mb-8 p-6 bg-indigo-50 border border-indigo-100 text-indigo-700 rounded-[2.5rem] font-black uppercase tracking-widest text-[10px]">
            {{ session('message') }}
        </div>
    @endif

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-6">
        <div class="flex flex-wrap gap-3">
            @forelse($channels as $channel)
                <div class="group flex items-center bg-white border border-gray-100 px-4 py-2 rounded-2xl shadow-sm hover:border-indigo-200 transition">
                    <div class="h-6 w-6 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-500 mr-3">
                         <span class="text-[8px] font-black">{{ strtoupper(substr($channel->platform, 0, 1)) }}</span>
                    </div>
                    <span class="text-xs font-black text-gray-700 tracking-tight">{{ $channel->account_name }}</span>
                </div>
            @empty
                <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest italic">No Transmission Nodes Connected</div>
            @endforelse
        </div>
        
        <div class="flex bg-gray-100 p-1.5 rounded-2xl shadow-inner">
            <button wire:click="setViewMode('calendar')"
                class="{{ $viewMode === 'calendar' ? 'bg-white shadow-md text-gray-900 border border-gray-100' : 'text-gray-400 hover:text-gray-600' }} px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition">Calendar</button>
            <button wire:click="setViewMode('list')"
                class="{{ $viewMode === 'list' ? 'bg-white shadow-md text-gray-900 border border-gray-100' : 'text-gray-400 hover:text-gray-600' }} px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition ml-1">Archive</button>
        </div>
    </div>

    @if($viewMode === 'calendar')
        <x-card class="p-0 overflow-hidden border-none shadow-2xl rounded-[3rem] bg-white">
            <div class="border-b border-gray-50 bg-gray-50/50 p-8 flex justify-between items-center">
                <h3 class="font-black text-2xl text-gray-900 tracking-tighter uppercase">{{ $currentMonthLabel }}</h3>
                <div class="flex space-x-3">
                    <button wire:click="previousMonth" class="h-10 w-10 bg-white shadow-sm rounded-xl flex items-center justify-center text-gray-400 hover:text-indigo-600 transition border border-gray-100">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button wire:click="nextMonth" class="h-10 w-10 bg-white shadow-sm rounded-xl flex items-center justify-center text-gray-400 hover:text-indigo-600 transition border border-gray-100">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
            
            <div class="grid grid-cols-7 text-center py-4 bg-gray-50/30 text-[9px] font-black text-gray-400 uppercase tracking-[0.2em]">
                <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
            </div>

            <div class="grid grid-cols-7 bg-gray-100 gap-px border-t border-gray-50">
                @foreach($calendar as $dateString => $dayData)
                    <div class="bg-white min-h-[160px] p-4 hover:bg-gray-50/50 transition group overflow-hidden">
                        <div class="flex justify-between items-start mb-2">
                             <span class="{{ $dayData['isCurrentMonth'] ? 'text-gray-900 font-black' : 'text-gray-200' }} text-xs">{{ $dayData['day'] }}</span>
                             @if(now()->format('Y-m-d') === $dateString)
                                <span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                             @endif
                        </div>

                        <div class="space-y-2">
                            @foreach($dayData['posts'] as $post)
                                <div class="bg-indigo-50/80 border-l-4 border-indigo-400 p-2 rounded-r-lg shadow-sm group-hover:shadow-md transition cursor-pointer" title="{{ $post->content }}">
                                    <div class="text-[8px] font-black text-indigo-500 uppercase tracking-widest mb-1">{{ $post->scheduled_at->format('g:i A') }}</div>
                                    <div class="text-[10px] font-bold text-gray-700 leading-tight line-clamp-2">{{ $post->content }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>
    @else
        <div class="grid grid-cols-1 gap-6">
            @forelse($listPosts as $post)
                 <x-card class="p-8 border-none shadow-xl rounded-[2.5rem] bg-white hover:scale-[1.01] transition duration-300">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-6">
                            <div class="h-14 w-14 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 font-black">
                                 {{ strtoupper(substr($post->channel->platform ?? '?', 0, 2)) }}
                            </div>
                            <div>
                                <h4 class="text-lg font-black text-gray-900 tracking-tight">{{ str($post->content)->limit(100) }}</h4>
                                <div class="flex items-center mt-1 space-x-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                    <span>{{ $post->scheduled_at->format('M d, Y @ g:i A') }}</span>
                                    <span>•</span>
                                    <span class="text-indigo-400">{{ $post->channel->account_name }}</span>
                                </div>
                            </div>
                        </div>
                        <span class="px-5 py-2 rounded-full text-[10px] font-black uppercase tracking-widest {{ $post->status === 'published' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $post->status }}
                        </span>
                    </div>
                 </x-card>
            @empty
                <div class="py-40 text-center bg-gray-50/50 rounded-[4rem] border-4 border-dashed border-gray-100">
                     <h3 class="text-2xl font-black text-gray-900 tracking-tight uppercase tracking-widest">No Post Sequences</h3>
                </div>
            @endforelse
        </div>
    @endif

    <!-- Create Post Modal -->
    <x-modal name="post-creation-modal" wire:model="showCreateModal">
        <div class="p-10">
            <h2 class="text-3xl font-black text-gray-900 tracking-tighter mb-10 uppercase tracking-widest italic">Initialize Post Logic</h2>
            <form wire:submit="createPost" class="space-y-8">
                <div>
                    <x-input-label>Target Transmission Nodes</x-input-label>
                    <div class="flex flex-wrap gap-3 mt-4">
                        @foreach($channels as $channel)
                            <button type="button" wire:click="toggleChannelSelection('{{ $channel->id }}')"
                                class="px-6 py-3 border rounded-2xl text-[10px] font-black uppercase tracking-widest transition 
                                    {{ in_array($channel->id, $selectedChannels) ? 'bg-indigo-600 border-indigo-600 text-white shadow-lg' : 'bg-white border-gray-100 text-gray-400 hover:border-indigo-200' }}">
                                {{ $channel->account_name }}
                            </button>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('selectedChannels')" class="mt-2" />
                </div>

                <div>
                    <x-input-label>Intelligence Content</x-input-label>
                    <x-textarea wire:model="content" rows="6" placeholder="Construct your omnichannel message..."
                        class="w-full mt-2 rounded-[2rem] p-6 border-gray-100 shadow-inner"></x-textarea>
                    <x-input-error :messages="$errors->get('content')" class="mt-2" />
                </div>

                <div class="grid grid-cols-2 gap-8">
                    <div>
                        <x-input-label>Deployment Date</x-input-label>
                        <x-text-input type="date" wire:model="scheduledDate" class="w-full mt-2 rounded-xl" />
                    </div>
                    <div>
                        <x-input-label>Deployment Time</x-input-label>
                        <x-text-input type="time" wire:model="scheduledTime" class="w-full mt-2 rounded-xl" />
                    </div>
                </div>

                <div class="flex justify-end space-x-4 mt-12">
                     <x-button type="button" color="outline" wire:click="$toggle('showCreateModal')" class="rounded-2xl px-10">Abort</x-button>
                     <x-button type="submit" color="primary" class="rounded-2xl px-12 shadow-xl shadow-indigo-100">Schedule Logic</x-button>
                </div>
            </form>
        </div>
    </x-modal>

    <!-- Connect Channel Modal -->
    <x-modal name="channel-connect-modal" wire:model="showConnectModal">
        <div class="p-10">
            <h2 class="text-3xl font-black text-gray-900 tracking-tighter mb-10 uppercase tracking-widest italic">Link Social Engine</h2>
            <div class="grid grid-cols-2 gap-8">
                @foreach(['facebook', 'instagram', 'linkedin', 'twitter'] as $platform)
                    <button wire:click="connectChannel('{{ $platform }}')"
                        class="p-8 border-none bg-gray-50 rounded-[2.5rem] flex flex-col items-center hover:bg-indigo-50 hover:scale-105 transition duration-300">
                        <div class="h-16 w-16 bg-white rounded-2xl shadow-sm flex items-center justify-center text-indigo-600 mb-6 font-black text-xl">
                             {{ strtoupper(substr($platform, 0, 1)) }}
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-500">{{ ucfirst($platform) }}</span>
                    </button>
                @endforeach
            </div>
            <div class="flex justify-center mt-12">
                 <x-button type="button" color="outline" wire:click="$toggle('showConnectModal')" class="rounded-2xl px-12">Cancel Handshake</x-button>
            </div>
        </div>
    </x-modal>
</x-app-container>