<x-app-container>
    <x-page-header title="Mass Broadcast Intelligence">
        <x-button color="primary" class="rounded-2xl shadow-lg" wire:click="$toggle('showCreateModal')">
            + New Campaign
        </x-button>
    </x-page-header>

    @if (session()->has('success'))
        <div class="mb-8 p-6 bg-green-50 border border-green-100 text-green-700 rounded-[2rem] font-black uppercase tracking-widest text-[10px]">
            {{ session('success') }}
        </div>
    @endif

    <div class="space-y-8">
        <!-- Search and Filter (Optional placeholder) -->
        <div class="flex items-center space-x-4">
            <x-text-input wire:model.live="search" placeholder="Search broadcasts..." class="w-full max-w-sm rounded-[1.5rem]" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($broadcasts as $broadcast)
                <x-card class="p-8 border-none shadow-2xl rounded-[3rem] hover:scale-[1.02] transition duration-300">
                    <div class="flex justify-between items-start mb-6">
                        <div class="h-14 w-14 bg-indigo-50 rounded-[1.5rem] flex items-center justify-center text-indigo-600">
                            @if($broadcast->channel === 'WHATSAPP')
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            @elseif($broadcast->channel === 'EMAIL')
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            @else
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                            @endif
                        </div>
                        <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-gray-50 text-gray-500 shadow-inner">
                            {{ $broadcast->status }}
                        </span>
                    </div>

                    <h3 class="text-xl font-black text-gray-900 tracking-tight mb-2">{{ $broadcast->name }}</h3>
                    <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-6 italic">Target: {{ $broadcast->target_segment }}</p>

                    <div class="pt-6 border-t border-gray-50 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                             <span class="h-2 w-2 rounded-full bg-green-500"></span>
                             <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ $broadcast->recipients_count ?? 0 }} Reached</span>
                        </div>
                        <button wire:click="delete('{{ $broadcast->id }}')" wire:confirm="Destroy this campaign intelligence?" class="text-red-300 hover:text-red-500 transition">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </x-card>
            @empty
                <div class="lg:col-span-3 py-32 text-center bg-gray-50/50 rounded-[4rem] border-4 border-dashed border-gray-100">
                    <div class="text-gray-200 mb-6 flex justify-center">
                         <svg class="h-24 w-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                    </div>
                    <h3 class="text-2xl font-black text-gray-900 tracking-tight">No mass broadcasts found</h3>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-2">Initialize your first multi-channel campaign to start scaling</p>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $broadcasts->links() }}
        </div>
    </div>

    <!-- Create Modal -->
    <x-modal name="broadcast-creation-modal" wire:model="showCreateModal">
        <div class="p-10">
            <h2 class="text-3xl font-black text-gray-900 tracking-tighter mb-10 italic uppercase tracking-widest">Deploy Campaign Logic</h2>
            
            <form wire:submit="createBroadcast" class="space-y-8">
                <div class="grid grid-cols-2 gap-8">
                    <div class="col-span-2">
                        <x-input-label for="name">Campaign Identifier</x-input-label>
                        <x-text-input wire:model="name" id="name" class="w-full mt-2 rounded-2xl" placeholder="e.g. Q2 Prospecting Sequence" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="channel">Transmission Channel</x-input-label>
                        <x-select wire:model="channel" id="channel" class="w-full mt-2 rounded-2xl">
                            <option value="EMAIL">Direct Email</option>
                            <option value="WHATSAPP">WhatsApp Cloud API</option>
                            <option value="SMS">Premium SMS</option>
                        </x-select>
                    </div>

                    <div>
                        <x-input-label for="target_segment">Recipient Segment</x-input-label>
                        <x-select wire:model="target_segment" id="target_segment" class="w-full mt-2 rounded-2xl">
                            <option value="ALL_CONTACTS">Global Contact Pool</option>
                            <option value="QUALIFIED_LEADS">Qualified Pipeline Only</option>
                            <option value="PAST_CLIENTS">Retention Retention Pool</option>
                        </x-select>
                    </div>

                    <div class="col-span-2">
                        <x-input-label for="content_body">Intelligence Payload (Body)</x-input-label>
                        <x-textarea wire:model="content_body" id="content_body" class="w-full mt-2 rounded-2xl" rows="4" placeholder="Craft your message here..."></x-textarea>
                        <x-input-error :messages="$errors->get('content_body')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="automation_rule_id">Follow-up Automation</x-input-label>
                        <x-select wire:model="automation_rule_id" id="automation_rule_id" class="w-full mt-2 rounded-2xl">
                            <option value="">None (Static Broadcast)</option>
                            @foreach($automationRules as $rule)
                                <option value="{{ $rule->id }}">{{ $rule->name }}</option>
                            @endforeach
                        </x-select>
                    </div>

                    <div>
                        <x-input-label for="scheduled_at">Deployment Schedule</x-input-label>
                        <x-text-input type="datetime-local" wire:model="scheduled_at" id="scheduled_at" class="w-full mt-2 rounded-2xl" />
                    </div>
                </div>

                <div class="mt-10 flex justify-end space-x-4">
                    <x-button color="outline" type="button" wire:click="$toggle('showCreateModal')" class="rounded-2xl px-10">Cancel</x-button>
                    <x-button color="primary" type="submit" class="rounded-2xl px-12 shadow-xl shadow-indigo-100">Initialize Broadcast</x-button>
                </div>
            </form>
        </div>
    </x-modal>
</x-app-container>
