<x-app-container>
    <x-page-header title="Satisfaction Intelligence & Flux">
        <x-button color="primary" class="rounded-2xl shadow-lg" wire:click="$toggle('showCreateModal')">
            + Log Feedback
        </x-button>
    </x-page-header>

    @if (session()->has('success'))
        <div class="mb-8 p-6 bg-indigo-50 border border-indigo-100 text-indigo-700 rounded-[2rem] font-black uppercase tracking-widest text-[10px]">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-10">
        <!-- Summary Cards -->
        <x-card class="p-8 border-none shadow-xl rounded-[2.5rem] bg-white group">
             <div class="flex items-center gap-4 mb-6">
                <div class="h-12 w-12 bg-green-50 rounded-2xl flex items-center justify-center text-green-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                     <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Sentiment Score</p>
                     <p class="text-xl font-black text-gray-900 tracking-tight tracking-widest">4.8 / 5.0</p>
                </div>
             </div>
             <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-relaxed">Cross-entity satisfaction matrix analyzed from last 30 logs.</p>
        </x-card>
        
        <x-card class="p-8 border-none shadow-xl rounded-[2.5rem] bg-indigo-600 text-white relative overflow-hidden group">
             <div class="absolute -right-10 -top-10 h-32 w-32 bg-white opacity-5 rounded-full"></div>
             <div class="flex items-center gap-4 mb-6 relative z-10">
                <div class="h-12 w-12 bg-white/10 rounded-2xl flex items-center justify-center text-white">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                     <p class="text-[9px] font-black text-indigo-200 uppercase tracking-widest">Pending Nodes</p>
                     <p class="text-xl font-black text-white tracking-tight tracking-widest">07 Flux Logs</p>
                </div>
             </div>
             <p class="text-[10px] font-bold text-indigo-100 uppercase tracking-widest leading-relaxed">Awaiting agency acknowledgment and strategic alignment.</p>
        </x-card>
    </div>

    <div class="space-y-6">
        @forelse($feedbackItems as $item)
            <x-card class="p-8 border-none shadow-xl hover:shadow-2xl transition duration-300 rounded-[2.5rem] bg-white group overflow-hidden relative">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
                    <div class="flex-1">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="h-4 w-4 {{ $i <= $item->rating ? 'text-amber-400' : 'text-gray-100' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                            </div>
                            <span class="px-3 py-1 bg-gray-50 text-[8px] font-black text-gray-400 rounded-full uppercase tracking-[0.2em]">{{ $item->entity_type }}</span>
                            <span class="text-[9px] font-black text-gray-300 uppercase tracking-widest tracking-widest">{{ $item->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-xl font-black text-gray-900 tracking-tight italic">"{{ $item->comment }}"</p>
                        <div class="mt-4 flex items-center gap-3">
                             <div class="h-8 w-8 rounded-full bg-indigo-50 flex items-center justify-center text-[10px] font-black text-indigo-400 uppercase border border-indigo-100">{{ substr($item->user->full_name ?? 'AU', 0, 2) }}</div>
                             <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ $item->user->full_name ?? 'Anonymous Intelligence' }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                         <button wire:click="archive('{{ $item->id }}')" class="p-4 bg-gray-50 rounded-2xl text-gray-400 hover:text-indigo-600 transition group-hover:bg-indigo-50">
                             <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                         </button>
                    </div>
                </div>
            </x-card>
        @empty
             <div class="py-40 text-center bg-gray-50 rounded-[4rem] border-4 border-dashed border-gray-100">
                <h3 class="text-xl font-black text-gray-900 tracking-tight uppercase tracking-widest italic opacity-30">Saturation of satisfaction logs not reached</h3>
            </div>
        @endforelse
    </div>

    <!-- Create Feedback Modal -->
    <x-modal name="feedback-submission-modal" wire:model="showCreateModal">
        <div class="p-10">
            <h2 class="text-3xl font-black text-gray-900 tracking-tighter mb-10 uppercase italic tracking-widest">Transmit Satisfaction Pulse</h2>
            <form wire:submit="createFeedback" class="space-y-8">
                <div>
                    <x-input-label>Rate Your Perception</x-input-label>
                    <div class="flex gap-4 mt-4">
                        @foreach([1, 2, 3, 4, 5] as $val)
                            <button type="button" wire:click="$set('rating', {{ $val }})" class="h-16 w-16 rounded-[1.5rem] border-2 flex items-center justify-center transition focus:outline-none {{ $rating >= $val ? 'bg-amber-400 border-amber-400 text-white shadow-lg' : 'bg-white border-gray-100 text-gray-200' }}">
                                <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <x-input-label>Entity Mapping</x-input-label>
                    <x-select wire:model="entity_type" class="w-full mt-2 rounded-2xl border-gray-100 p-4">
                        <option value="general">General Agency Relationship</option>
                        <option value="project">Specific Active Project</option>
                        <option value="creative_request">Active Creative Asset Flow</option>
                        <option value="platform">Software Ecosystem Feedback</option>
                    </x-select>
                </div>

                <div>
                    <x-input-label>Intellectual Commentary</x-input-label>
                    <x-textarea wire:model="comment" class="w-full mt-2 rounded-[2rem] border-gray-100 p-6 shadow-inner" rows="4" placeholder="Craft your experience narrative..."></x-textarea>
                    <x-input-error :messages="$errors->get('comment')" class="mt-2" />
                </div>

                <div class="flex justify-end space-x-4 mt-12">
                     <x-button color="outline" type="button" wire:click="$toggle('showCreateModal')" class="rounded-2xl px-10">Abort</x-button>
                     <x-button color="primary" type="submit" class="rounded-2xl px-14 shadow-xl shadow-indigo-100">Transmit Log</x-button>
                </div>
            </form>
        </div>
    </x-modal>
</x-app-container>
