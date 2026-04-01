<x-app-container>
    <x-page-header title="Creative Intelligence Dashboard">
        <x-button color="primary" class="rounded-2xl shadow-lg" wire:click="$toggle('showCreateModal')">
            + New Request
        </x-button>
    </x-page-header>

    @if (session()->has('success'))
        <div class="mb-8 p-6 bg-green-50 border border-green-100 text-green-700 rounded-[2rem] font-black uppercase tracking-widest text-[10px]">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4 mb-10">
        <div class="flex-1">
             <x-text-input wire:model.live="search" class="w-full rounded-2xl border-gray-100 p-4 shadow-sm" placeholder="Global search for creative tasks..." />
        </div>
        <div class="flex bg-gray-100 p-1.5 rounded-2xl shadow-inner">
            <button wire:click="$set('statusFilter', 'ALL')"
                class="{{ $statusFilter === 'ALL' ? 'bg-white shadow-md text-gray-900' : 'text-gray-400' }} px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition">All Flux</button>
            <button wire:click="$set('statusFilter', 'Pending')"
                class="{{ $statusFilter === 'Pending' ? 'bg-white shadow-md text-gray-900 border border-gray-100' : 'text-gray-400' }} px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition ml-1">Queue</button>
            <button wire:click="$set('statusFilter', 'Processing')"
                class="{{ $statusFilter === 'Processing' ? 'bg-white shadow-md text-gray-900' : 'text-gray-400' }} px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition ml-1">Live</button>
        </div>
    </div>

    <div class="space-y-6">
        @forelse($requests as $request)
            <x-card class="p-8 border-none shadow-xl hover:shadow-2xl transition duration-300 rounded-[2.5rem] bg-white group overflow-hidden relative">
                @if($request->priority === 'Urgent')
                    <div class="absolute top-0 left-0 w-2 h-full bg-rose-500"></div>
                @elseif($request->priority === 'High')
                    <div class="absolute top-0 left-0 w-2 h-full bg-amber-500"></div>
                @endif

                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="px-3 py-1 text-[8px] font-black rounded-full uppercase tracking-[0.2em] {{ $request->status === 'Pending' ? 'bg-gray-100 text-gray-500' : 'bg-indigo-100 text-indigo-600' }}">
                                {{ $request->status }}
                            </span>
                            <span class="text-[9px] font-black text-gray-300 uppercase tracking-widest">• ID: {{ substr($request->id, 0, 8) }}</span>
                        </div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight group-hover:text-branding transition">{{ $request->title }}</h3>
                        <p class="text-[11px] font-bold text-gray-400 mt-2 line-clamp-2 uppercase italic leading-relaxed">"{{ $request->description }}"</p>
                    </div>

                    <div class="flex items-center gap-8 lg:border-l lg:border-gray-50 lg:pl-12">
                        <div class="flex flex-col">
                             <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Priority Node</span>
                             <span class="text-xs font-black uppercase tracking-widest italic {{ $request->priority === 'Urgent' ? 'text-rose-600' : 'text-gray-900' }}">{{ $request->priority }}</span>
                        </div>
                        <div class="flex flex-col">
                             <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Target Epoch</span>
                             <span class="text-xs font-black text-gray-900 uppercase tracking-widest italic">{{ $request->due_date ? $request->due_date->format('M d, Y') : 'Variable' }}</span>
                        </div>
                        <div class="flex -space-x-3">
                             <div class="h-10 w-10 rounded-full border-2 border-white bg-indigo-50 flex items-center justify-center text-[10px] font-black text-indigo-400 uppercase shadow-sm">NA</div>
                        </div>
                    </div>
                </div>
            </x-card>
        @empty
             <div class="py-40 text-center bg-gray-50 rounded-[4rem] border-4 border-dashed border-gray-100">
                <div class="inline-flex h-24 w-24 bg-white rounded-[3rem] shadow-sm items-center justify-center text-gray-100 mb-8 self-center">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="text-xl font-black text-gray-900 tracking-tight uppercase tracking-widest italic">Request Reservoir Empty</h3>
                <p class="text-[10px] font-black text-gray-400 mt-2 uppercase tracking-widest">Initialize a creative sequence to start design fulfillment</p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $requests->links() }}
    </div>

    <!-- Create Request Modal -->
    <x-modal name="creative-submission-modal" wire:model="showCreateModal">
        <div class="p-10">
            <h2 class="text-3xl font-black text-gray-900 tracking-tighter mb-10 uppercase italic tracking-widest">Construct Creative Pulse</h2>
            <form wire:submit="createCreativeRequest" class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="md:col-span-2">
                        <x-input-label>Objective Title</x-input-label>
                        <x-text-input wire:model="title" class="w-full mt-2 rounded-2xl border-gray-100 p-4" placeholder="e.g. Meta Q3 Visual Refresh" />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div class="md:col-span-2">
                        <x-input-label>Creative Brief Intelligence</x-input-label>
                        <x-textarea wire:model="description" class="w-full mt-2 rounded-[2rem] border-gray-100 p-6" rows="5" placeholder="Define the visual strategy and constraints..."></x-textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label>Impact Level</x-input-label>
                        <x-select wire:model="priority" class="w-full mt-2 rounded-2xl border-gray-100 p-4">
                            <option value="Low">Low - Maintenance</option>
                            <option value="Medium text-indigo-500">Medium - Growth</option>
                            <option value="High">High - Strategic</option>
                            <option value="Urgent">Urgent - Mission Critical</option>
                        </x-select>
                    </div>

                    <div>
                        <x-input-label>Deadline Node</x-input-label>
                        <x-text-input type="date" wire:model="due_date" class="w-full mt-2 rounded-2xl border-gray-100 p-4" />
                        <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                    </div>
                </div>

                <div class="flex justify-end space-x-4 mt-12">
                     <x-button color="outline" type="button" wire:click="$toggle('showCreateModal')" class="rounded-2xl px-10">Abort</x-button>
                     <x-button color="primary" type="submit" class="rounded-2xl px-12 shadow-xl shadow-indigo-100">Queue Request</x-button>
                </div>
            </form>
        </div>
    </x-modal>
</x-app-container>
