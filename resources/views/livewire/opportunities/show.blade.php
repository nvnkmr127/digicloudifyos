<x-app-container>
    <x-page-header title="Opportunity Details">
        <a href="{{ route('pipelines.index') }}" class="text-sm font-medium text-text-muted hover:text-text-primary">Back to Pipeline</a>
    </x-page-header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 space-y-6">
            <x-card class="bg-white border-0 shadow-xl rounded-card-premium overflow-hidden">
                <div class="p-8">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h2 class="text-3xl font-black text-gray-900 tracking-tight">{{ $opportunity->name }}</h2>
                            <p class="text-sm font-bold text-gray-400 mt-2 flex items-center gap-2">
                                <span class="uppercase tracking-widest">{{ $opportunity->pipeline?->name ?? 'Default Pipeline' }}</span>
                                <span>&bull;</span>
                                <span class="text-primary">{{ $opportunity->stage?->name ?? 'Initial Stage' }}</span>
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-black uppercase tracking-widest {{ $opportunity->status === 'won' ? 'bg-green-100 text-green-700' : ($opportunity->status === 'lost' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') }}">
                                {{ $opportunity->status }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="flex items-end gap-2 my-8 border-b border-gray-50 pb-8">
                        <span class="text-5xl font-black text-gray-900 tracking-tighter">${{ number_format((float)$opportunity->monetary_value, 2) }}</span>
                        <span class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-1">Value</span>
                    </div>

                    <div class="mt-4 prose prose-sm text-gray-600">
                        <p>This opportunity is currently active in the pipeline. Continue following up with the contact to advance it to the next stage.</p>
                    </div>
                </div>
            </x-card>
        </div>

        <div class="space-y-6">
            <x-card class="bg-white border-0 shadow-lg rounded-2xl p-6">
                <h3 class="text-xs uppercase tracking-widest font-black text-gray-900 mb-4 border-b border-gray-100 pb-2">Associated Contact</h3>
                @if($opportunity->contact)
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-xl bg-primary text-white flex items-center justify-center font-black text-lg">
                            {{ substr($opportunity->contact->first_name, 0, 1) }}{{ substr($opportunity->contact->last_name, 0, 1) }}
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900">{{ $opportunity->contact->first_name }} {{ $opportunity->contact->last_name }}</h4>
                            <p class="text-xs text-text-muted mt-1">{{ $opportunity->contact->email ?? 'No email available' }}</p>
                            @if($opportunity->contact->company_name)
                                <p class="text-xs font-medium text-gray-500 mt-1">{{ $opportunity->contact->company_name }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="mt-6 pt-4 border-t border-gray-50">
                        <x-button color="primary" class="w-full" href="{{ route('contacts.show', $opportunity->contact->id) }}">
                            View Contact Profile
                        </x-button>
                    </div>
                @else
                    <div class="text-center py-6">
                        <p class="text-sm font-medium text-gray-400">No contact linked</p>
                    </div>
                @endif
            </x-card>

            <x-card class="bg-gradient-to-br from-indigo-900 to-indigo-800 border-0 shadow-lg p-6">
                <h3 class="text-xs uppercase tracking-widest font-black text-white/50 mb-4">Quick Actions</h3>
                <div class="space-y-2">
                    <button wire:click="markAsWon" wire:loading.attr="disabled" class="w-full bg-white/10 hover:bg-white/20 transition px-4 py-3 rounded-xl text-left flex items-center justify-between text-white font-bold text-sm disabled:opacity-50">
                        Mark as Won
                        <svg class="h-4 w-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </button>
                    <button wire:click="markAsLost" wire:loading.attr="disabled" class="w-full bg-white/10 hover:bg-white/20 transition px-4 py-3 rounded-xl text-left flex items-center justify-between text-white font-bold text-sm disabled:opacity-50">
                        Mark as Lost
                        <svg class="h-4 w-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </button>
                    <button wire:click="createProposal" wire:loading.attr="disabled" class="w-full bg-white/10 hover:bg-white/20 transition px-4 py-3 rounded-xl text-left flex items-center justify-between text-white font-bold text-sm disabled:opacity-50">
                        Create Proposal
                        <svg class="h-4 w-4 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </button>
                </div>
            </x-card>
        </div>
    </div>
</x-app-container>
