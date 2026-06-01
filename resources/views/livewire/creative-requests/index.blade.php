<x-app-container>
    <x-page-header title="Creative Requests">
        <x-button variant="primary" wire:click="$toggle('showCreateModal')">New Request</x-button>
    </x-page-header>

    @if (session()->has('success'))
        <x-alert type="success" class="mb-6">
            {{ session('success') }}
        </x-alert>
    @endif

    <x-toolbar class="mb-6" variant="subtle">
        <x-slot name="left">
            <x-input wire:model.live.debounce.300ms="search" type="search" aria-label="Search creative requests" class="w-full sm:w-96" placeholder="Search requests…" />
        </x-slot>
        <x-slot name="right">
            <div class="inline-flex rounded-button border border-gray-200 bg-white p-1">
                <button type="button" wire:click="$set('statusFilter', 'ALL')"
                    class="{{ $statusFilter === 'ALL' ? 'bg-primary-soft text-primary' : 'text-text-muted hover:text-text-primary' }} px-3 py-2 rounded-button text-sm font-semibold transition">
                    All
                </button>
                <button type="button" wire:click="$set('statusFilter', 'requested')"
                    class="{{ $statusFilter === 'requested' ? 'bg-primary-soft text-primary' : 'text-text-muted hover:text-text-primary' }} px-3 py-2 rounded-button text-sm font-semibold transition">
                    Requested
                </button>
                <button type="button" wire:click="$set('statusFilter', 'in_production')"
                    class="{{ $statusFilter === 'in_production' ? 'bg-primary-soft text-primary' : 'text-text-muted hover:text-text-primary' }} px-3 py-2 rounded-button text-sm font-semibold transition">
                    In Production
                </button>
            </div>
        </x-slot>
    </x-toolbar>

    <div class="space-y-6">
        @forelse($requests as $request)
            <x-card class="p-6 overflow-hidden relative">
                @if(strtolower((string) $request->priority) === 'urgent')
                    <div class="absolute top-0 left-0 w-2 h-full bg-rose-500"></div>
                @elseif(strtolower((string) $request->priority) === 'high')
                    <div class="absolute top-0 left-0 w-2 h-full bg-amber-500"></div>
                @endif

                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            @php
                                $reqStatus = strtolower($request->status ?? '');
                                $reqStatusVariant = match ($reqStatus) {
                                    'requested' => 'neutral',
                                    'in_production' => 'info',
                                    'review' => 'warning',
                                    'approved', 'published' => 'success',
                                    'rejected' => 'danger',
                                    default => 'neutral',
                                };

                                $reqPriority = strtolower($request->priority ?? '');
                                $reqPriorityVariant = match ($reqPriority) {
                                    'urgent' => 'danger',
                                    'high' => 'warning',
                                    'medium' => 'info',
                                    'low' => 'neutral',
                                    default => 'neutral',
                                };
                            @endphp
                            <x-badge :variant="$reqStatusVariant" size="xs">{{ $request->status }}</x-badge>
                            <x-badge :variant="$reqPriorityVariant" size="xs">{{ $request->priority }}</x-badge>
                            <span class="text-xs text-text-muted">ID: {{ substr($request->id, 0, 8) }}</span>
                        </div>
                        <h3 class="text-sm font-semibold text-text-primary">{{ $request->title }}</h3>
                        <p class="text-sm text-text-muted mt-2 line-clamp-2">{{ $request->description }}</p>
                    </div>

                    <div class="flex items-center gap-6 lg:border-l lg:border-gray-100 lg:pl-8">
                        <div class="text-sm text-text-muted">
                            <div class="font-semibold text-text-primary">{{ $request->deadline ? $request->deadline->format('M d, Y') : 'No deadline' }}</div>
                            <div>Deadline</div>
                        </div>
                        <button 
                            type="button" 
                            wire:click="deleteCreativeRequest('{{ $request->id }}')" 
                            wire:confirm="Are you sure you want to delete this request?"
                            class="text-gray-300 hover:text-danger p-2 rounded-lg transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </x-card>
        @empty
            <x-card>
                <x-empty-state title="No requests yet" description="Create a request to start the creative workflow." />
            </x-card>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $requests->links() }}
    </div>

    <!-- Create Request Modal -->
    <x-modal name="creative-submission-modal" wire:model="showCreateModal">
        <div class="p-8">
            <h2 class="text-lg font-semibold text-text-primary mb-6">New Request</h2>
            <form wire:submit="createCreativeRequest" class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <x-input-label>Client</x-input-label>
                        <x-select wire:model="client_id" class="w-full mt-2">
                            <option value="">Select client</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </x-select>
                        <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label>Campaign</x-input-label>
                        <x-select wire:model="campaign_id" class="w-full mt-2">
                            <option value="">Select campaign</option>
                            @foreach($campaigns as $campaign)
                                <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                            @endforeach
                        </x-select>
                        <x-input-error :messages="$errors->get('campaign_id')" class="mt-2" />
                    </div>

                    <div class="md:col-span-2">
                        <x-input-label>Type</x-input-label>
                        <x-select wire:model="type" class="w-full mt-2">
                            <option value="image">Image</option>
                            <option value="carousel">Carousel</option>
                            <option value="video">Video</option>
                            <option value="banner">Banner</option>
                        </x-select>
                        <x-input-error :messages="$errors->get('type')" class="mt-2" />
                    </div>

                    <div class="md:col-span-2">
                        <x-input-label>Objective Title</x-input-label>
                        <x-input wire:model="title" class="w-full mt-2" placeholder="e.g. Meta Q3 Visual Refresh" />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div class="md:col-span-2">
                        <x-input-label>Creative Brief Intelligence</x-input-label>
                        <x-textarea wire:model="description" class="w-full mt-2" rows="5" placeholder="Define the visual strategy and constraints…"></x-textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label>Impact Level</x-input-label>
                        <x-select wire:model="priority" class="w-full mt-2">
                            <option value="low">Low - Maintenance</option>
                            <option value="medium">Medium - Growth</option>
                            <option value="high">High - Strategic</option>
                            <option value="urgent">Urgent - Mission Critical</option>
                        </x-select>
                        <x-input-error :messages="$errors->get('priority')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label>Deadline Node</x-input-label>
                        <x-input type="date" wire:model="deadline" class="w-full mt-2" />
                        <x-input-error :messages="$errors->get('deadline')" class="mt-2" />
                    </div>
                </div>

                <div class="flex justify-end space-x-4 mt-12">
                     <x-button variant="outline" type="button" wire:click="$toggle('showCreateModal')">Cancel</x-button>
                     <x-button variant="primary" type="submit">Create</x-button>
                </div>
            </form>
        </div>
    </x-modal>
</x-app-container>
