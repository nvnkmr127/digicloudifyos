<x-app-container>
    <x-page-header title="Social Planner">
        <x-button variant="outline" wire:click="$toggle('showConnectModal')">
            Connect Channel
        </x-button>
        <x-button variant="primary" wire:click="$toggle('showCreateModal')">
            New Post
        </x-button>
    </x-page-header>

    @if (session()->has('message'))
        <x-alert type="success" class="mb-6">
            {{ session('message') }}
        </x-alert>
    @endif

    <x-toolbar class="mb-6" variant="subtle">
        <x-slot name="left">
            <div class="flex flex-wrap gap-3">
            @forelse($channels as $channel)
                <div class="group flex items-center bg-white border border-gray-100 px-3 py-2 rounded-card shadow-sm transition">
                    <div class="h-6 w-6 rounded-element bg-primary-soft flex items-center justify-center text-primary mr-3">
                         <span class="text-xs font-semibold">{{ strtoupper(substr($channel->platform, 0, 1)) }}</span>
                    </div>
                    <span class="text-sm font-semibold text-text-primary">{{ $channel->account_name }}</span>
                </div>
            @empty
                <div class="text-sm text-text-muted">No channels connected</div>
            @endforelse
            </div>
        </x-slot>
        <x-slot name="right">
            <div class="inline-flex rounded-button border border-gray-200 bg-white p-1">
                <button type="button" wire:click="setViewMode('calendar')"
                    class="{{ $viewMode === 'calendar' ? 'bg-primary-soft text-primary' : 'text-text-muted hover:text-text-primary' }} px-3 py-2 rounded-button text-sm font-semibold transition">
                    Calendar
                </button>
                <button type="button" wire:click="setViewMode('list')"
                    class="{{ $viewMode === 'list' ? 'bg-primary-soft text-primary' : 'text-text-muted hover:text-text-primary' }} px-3 py-2 rounded-button text-sm font-semibold transition">
                    List
                </button>
            </div>
        </x-slot>
    </x-toolbar>

    @if($viewMode === 'calendar')
        <x-card class="p-0 overflow-hidden">
            <div class="border-b border-gray-100 bg-gray-50 px-6 py-4 flex justify-between items-center">
                <h3 class="text-base font-semibold text-text-primary">{{ $currentMonthLabel }}</h3>
                <div class="flex items-center gap-2">
                    <x-button variant="outline" size="sm" wire:click="previousMonth" aria-label="Previous month">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </x-button>
                    <x-button variant="outline" size="sm" wire:click="nextMonth" aria-label="Next month">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </x-button>
                </div>
            </div>
            
            <div class="grid grid-cols-7 text-center py-3 bg-gray-50 text-xs font-semibold text-text-muted uppercase tracking-wider">
                <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
            </div>

            <div class="grid grid-cols-7 bg-gray-100 gap-px border-t border-gray-50">
                @foreach($calendar as $dateString => $dayData)
                    <div class="bg-white min-h-[160px] p-4 hover:bg-gray-50/50 transition group overflow-hidden">
                        <div class="flex justify-between items-start mb-2">
                             <span class="{{ $dayData['isCurrentMonth'] ? 'text-text-primary font-semibold' : 'text-gray-300' }} text-xs">{{ $dayData['day'] }}</span>
                             @if(now()->format('Y-m-d') === $dateString)
                                <span class="h-1.5 w-1.5 rounded-full bg-primary"></span>
                             @endif
                        </div>

                        <div class="space-y-2">
                            @foreach($dayData['posts'] as $post)
                                <div class="bg-primary-soft border-l-4 border-primary p-2 rounded-element transition cursor-pointer" title="{{ $post->content }}">
                                    <div class="text-xs font-semibold text-primary mb-1">{{ $post->scheduled_at->format('g:i A') }}</div>
                                    <div class="text-xs text-text-primary leading-tight line-clamp-2">{{ $post->content }}</div>
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
                 <x-card class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-6">
                            <div class="h-10 w-10 rounded-element bg-primary-soft flex items-center justify-center text-primary font-semibold">
                                 {{ strtoupper(substr($post->channel->platform ?? '?', 0, 2)) }}
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-text-primary">{{ str($post->content)->limit(100) }}</h4>
                                <div class="flex items-center mt-1 space-x-3 text-xs text-text-muted">
                                    <span>{{ $post->scheduled_at->format('M d, Y @ g:i A') }}</span>
                                    <span>•</span>
                                    <span>{{ $post->channel->account_name }}</span>
                                </div>
                            </div>
                        </div>
                        <x-badge :variant="$post->status === 'published' ? 'success' : 'neutral'" size="xs">{{ $post->status }}</x-badge>
                    </div>
                 </x-card>
            @empty
                <x-card>
                    <x-empty-state title="No posts scheduled" description="Create a post to start planning your content calendar." />
                </x-card>
            @endforelse
        </div>
    @endif

    <!-- Create Post Modal -->
    <x-modal name="post-creation-modal" wire:model="showCreateModal">
        <div class="p-8">
            <h2 class="text-lg font-semibold text-text-primary mb-6">Create Post</h2>
            <form wire:submit="createPost" class="space-y-8">
                <div>
                    <x-input-label>Target Transmission Nodes</x-input-label>
                    <div class="flex flex-wrap gap-3 mt-4">
                        @foreach($channels as $channel)
                            <button type="button" wire:click="toggleChannelSelection('{{ $channel->id }}')"
                                class="px-4 py-2 border rounded-button text-sm font-semibold transition 
                                    {{ in_array($channel->id, $selectedChannels) ? 'bg-primary border-primary text-white' : 'bg-white border-gray-200 text-text-muted hover:text-text-primary' }}">
                                {{ $channel->account_name }}
                            </button>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('selectedChannels')" class="mt-2" />
                </div>

                <div>
                    <x-input-label>Intelligence Content</x-input-label>
                    <x-textarea wire:model="content" rows="6" placeholder="Construct your omnichannel message..."
                        class="w-full mt-2"></x-textarea>
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
                     <x-button type="button" variant="outline" wire:click="$toggle('showCreateModal')">Cancel</x-button>
                     <x-button type="submit" variant="primary">Schedule</x-button>
                </div>
            </form>
        </div>
    </x-modal>

    <!-- Connect Channel Modal -->
    <x-modal name="channel-connect-modal" wire:model="showConnectModal">
        <div class="p-8">
            <h2 class="text-lg font-semibold text-text-primary mb-6">Connect Channel</h2>
            <div class="grid grid-cols-2 gap-8">
                @foreach(['facebook', 'instagram', 'linkedin', 'twitter'] as $platform)
                    <button wire:click="connectChannel('{{ $platform }}')"
                        class="p-6 border border-gray-100 bg-white rounded-card flex flex-col items-center hover:bg-gray-50 transition">
                        <div class="h-12 w-12 bg-primary-soft rounded-element flex items-center justify-center text-primary mb-4 font-semibold text-lg">
                             {{ strtoupper(substr($platform, 0, 1)) }}
                        </div>
                        <span class="text-sm font-semibold text-text-primary">{{ ucfirst($platform) }}</span>
                    </button>
                @endforeach
            </div>
            <div class="flex justify-center mt-12">
                 <x-button type="button" variant="outline" wire:click="$toggle('showConnectModal')">Cancel</x-button>
            </div>
        </div>
    </x-modal>
</x-app-container>
