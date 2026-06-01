<x-app-container>
    <x-page-header title="Satisfaction Intelligence & Flux">
        <x-button variant="primary" wire:click="$toggle('showCreateModal')">Log Feedback</x-button>
    </x-page-header>

    @if (session()->has('success'))
        <x-alert type="success" class="mb-6">
            {{ session('success') }}
        </x-alert>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <x-stat-card
            label="Average rating"
            value="{{ number_format((float) $feedbackItems->getCollection()->avg('rating'), 1) }} / 5.0"
            trend="{{ $feedbackItems->total() }} logs"
        />
        <x-stat-card
            label="Latest"
            value="{{ optional($feedbackItems->getCollection()->first())?->created_at?->diffForHumans() ?? '—' }}"
            trend="Most recent submission"
        />
    </div>

    <div class="space-y-6">
        @forelse($feedbackItems as $item)
            <x-card class="p-6">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
                    <div class="flex-1">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="h-4 w-4 {{ $i <= $item->rating ? 'text-amber-400' : 'text-gray-100' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                            </div>
                            <x-badge variant="neutral" size="xs">{{ $item->entity_type }}</x-badge>
                            <span class="text-xs text-text-muted">{{ $item->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-sm text-text-primary">“{{ $item->comment }}”</p>
                        <div class="mt-4 flex items-center gap-3">
                             <div class="h-8 w-8 rounded-full bg-primary-soft flex items-center justify-center text-xs font-semibold text-primary border border-gray-100">{{ substr($item->user->full_name ?? 'AU', 0, 2) }}</div>
                             <span class="text-xs font-semibold text-text-muted">{{ $item->user->full_name ?? 'Anonymous' }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                         <button wire:click="archive('{{ $item->id }}')" aria-label="Archive feedback" class="p-3 bg-gray-50 rounded-button text-text-muted hover:text-primary transition">
                             <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                         </button>
                    </div>
                </div>
            </x-card>
        @empty
            <x-card>
                <x-empty-state title="No feedback yet" description="Log feedback to track satisfaction over time." />
            </x-card>
        @endforelse
    </div>

    <!-- Create Feedback Modal -->
    <x-modal name="feedback-submission-modal" wire:model="showCreateModal">
        <div class="p-8">
            <h2 class="text-lg font-semibold text-text-primary mb-6">Log Feedback</h2>
            <form wire:submit="createFeedback" class="space-y-8">
                <div>
                    <x-input-label>Rate Your Perception</x-input-label>
                    <div class="flex gap-4 mt-4">
                        @foreach([1, 2, 3, 4, 5] as $val)
                            <button type="button" wire:click="$set('rating', {{ $val }})" class="h-12 w-12 rounded-button border flex items-center justify-center transition focus:outline-none {{ $rating >= $val ? 'bg-warning border-warning text-white' : 'bg-white border-gray-200 text-gray-300' }}">
                                <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <x-input-label>Entity Mapping</x-input-label>
                    <x-select wire:model="entity_type" class="w-full mt-2">
                        <option value="general">General Agency Relationship</option>
                        <option value="project">Specific Active Project</option>
                        <option value="creative_request">Active Creative Asset Flow</option>
                        <option value="platform">Software Ecosystem Feedback</option>
                    </x-select>
                </div>

                <div>
                    <x-input-label>Intellectual Commentary</x-input-label>
                    <x-textarea wire:model="comment" class="w-full mt-2" rows="4" placeholder="Share context and what would improve…"></x-textarea>
                    <x-input-error :messages="$errors->get('comment')" class="mt-2" />
                </div>

                <div class="flex justify-end space-x-4 mt-12">
                     <x-button variant="outline" type="button" wire:click="$toggle('showCreateModal')">Cancel</x-button>
                     <x-button variant="primary" type="submit">Save</x-button>
                </div>
            </form>
        </div>
    </x-modal>
</x-app-container>
