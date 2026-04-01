<x-app-container>
    <x-page-header title="Contact Details">
        <div class="flex items-center gap-4">
            <a href="{{ route('contacts.index') }}" class="text-sm font-medium text-text-muted hover:text-text-primary">Back to List</a>
            <x-button color="primary">Edit Contact</x-button>
        </div>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Sidebar Info -->
        <div class="lg:col-span-1 space-y-6">
            <x-card class="text-center p-8 bg-gradient-to-b from-white to-gray-50 border-none shadow-xl">
                <div class="h-24 w-24 rounded-3xl bg-primary text-white flex items-center justify-center text-3xl font-black mx-auto mb-6 shadow-2xl shadow-primary/20 rotate-3">
                    {{ substr($contact->first_name, 0, 1) }}{{ substr($contact->last_name, 0, 1) }}
                </div>
                <h2 class="text-2xl font-black text-gray-900 tracking-tight">{{ $contact->first_name }} {{ $contact->last_name }}</h2>
                <p class="text-xs font-bold text-primary uppercase tracking-widest mt-2 px-3 py-1 bg-primary/5 rounded-full inline-block">{{ $contact->type }}</p>
                
                <div class="mt-8 space-y-4 text-left border-t border-gray-100 pt-8">
                    <div class="flex items-center text-sm text-text-muted">
                        <svg class="w-5 h-5 mr-3 text-primary/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span class="font-medium text-gray-700">{{ $contact->email ?: 'No email' }}</span>
                    </div>
                    <div class="flex items-center text-sm text-text-muted">
                        <svg class="w-5 h-5 mr-3 text-primary/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        <span class="font-medium text-gray-700">{{ $contact->phone ?: 'No phone' }}</span>
                    </div>
                    @if($contact->company_name)
                    <div class="flex items-center text-sm text-text-muted">
                        <svg class="w-5 h-5 mr-3 text-primary/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1l1 1h5l1-1h1m-1 4h.01M9 16h.01"></path>
                        </svg>
                        <span class="font-medium text-gray-700">{{ $contact->company_name }}</span>
                    </div>
                    @endif
                </div>
            </x-card>

            <x-card class="p-6 border-none shadow-xl">
                <h3 class="text-xs uppercase tracking-widest font-black text-gray-900 mb-4">Tags</h3>
                <div class="flex flex-wrap gap-2">
                    @forelse($contact->tags ?? [] as $tag)
                        <span class="px-2.5 py-1 bg-gray-100 text-gray-600 rounded-lg text-[10px] font-black uppercase tracking-wider">{{ $tag }}</span>
                    @empty
                        <span class="text-xs text-gray-400 italic">No tags assigned</span>
                    @endforelse
                </div>
            </x-card>
        </div>

        <!-- Main Content -->
        <div class="lg:col-span-3 space-y-8">
            <!-- Tabs -->
            <div x-data="{ tab: 'timeline' }">
                <div class="flex space-x-8 border-b border-gray-100 mb-8">
                    <button @click="tab = 'timeline'" :class="tab === 'timeline' ? 'border-primary text-primary' : 'border-transparent text-text-muted'" class="pb-4 border-b-2 font-black text-xs uppercase tracking-widest transition-all">Timeline & Activity</button>
                    <button @click="tab = 'opportunities'" :class="tab === 'opportunities' ? 'border-primary text-primary' : 'border-transparent text-text-muted'" class="pb-4 border-b-2 font-black text-xs uppercase tracking-widest transition-all">Opportunities ({{ count($contact->opportunities) }})</button>
                    <button @click="tab = 'conversations'" :class="tab === 'conversations' ? 'border-primary text-primary' : 'border-transparent text-text-muted'" class="pb-4 border-b-2 font-black text-xs uppercase tracking-widest transition-all">Conversations ({{ count($contact->conversations) }})</button>
                </div>

                <!-- Timeline -->
                <div x-show="tab === 'timeline'" class="space-y-6">
                    <div class="relative pl-8">
                        <div class="absolute left-0 top-2 bottom-8 w-0.5 bg-gray-100"></div>
                        
                        <div class="relative mb-10">
                            <div class="absolute -left-10 h-4 w-4 rounded-full bg-primary border-4 border-white shadow-sm ring-4 ring-primary/5"></div>
                            <div>
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ $contact->created_at->format('M d, Y') }}</span>
                                <h4 class="text-md font-bold text-gray-900 mt-1">Contact Created</h4>
                                <p class="text-sm text-text-muted mt-1 leading-relaxed">System automatically initialized this contact entry from direct input.</p>
                            </div>
                        </div>

                        @foreach($contact->opportunities as $op)
                        <div class="relative mb-10">
                            <div class="absolute -left-10 h-4 w-4 rounded-full bg-green-500 border-4 border-white shadow-sm ring-4 ring-green-500/5"></div>
                            <div>
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ $op->created_at->diffForHumans() }}</span>
                                <h4 class="text-md font-bold text-gray-900 mt-1">Opportunity Link: {{ $op->name }}</h4>
                                <p class="text-sm text-text-muted mt-1 leading-relaxed">Current Stage: <span class="font-bold text-green-600">{{ $op->stage }}</span></p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Opportunities Grid -->
                <div x-show="tab === 'opportunities'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @forelse($contact->opportunities as $op)
                        <x-card class="border-none shadow-xl hover:shadow-2xl transition duration-300 p-6">
                            <div class="flex justify-between items-start mb-4">
                                <h4 class="text-lg font-black text-gray-900 tracking-tight">{{ $op->name }}</h4>
                                <span class="px-2 py-1 bg-green-50 text-green-600 rounded-lg text-[10px] font-black uppercase tracking-widest">{{ $op->stage ?? 'New' }}</span>
                            </div>
                            <p class="text-2xl font-black text-primary">${{ number_format($op->value, 2) }}</p>
                            <div class="mt-6 flex items-center justify-between text-xs font-bold text-gray-400">
                                <span>Closing: {{ $op->close_date ? $op->close_date->format('M Y') : 'TBD' }}</span>
                                <a href="#" class="text-primary hover:underline">View Opportunity &rarr;</a>
                            </div>
                        </x-card>
                    @empty
                        <div class="col-span-2 text-center py-20 bg-gray-50 rounded-3xl border-2 border-dashed border-gray-100">
                            <p class="text-gray-400 font-bold uppercase tracking-widest text-xs">No opportunities found for this contact.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Conversations -->
                <div x-show="tab === 'conversations'" class="space-y-4">
                    @forelse($contact->conversations as $convo)
                        <x-card class="p-6 border-none shadow-lg hover:bg-gray-50 transition cursor-pointer">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-4">
                                    <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                                        {{ substr($convo->platform, 0, 1) }}
                                    </div>
                                    <div>
                                        <h4 class="text-md font-bold text-gray-900">{{ ucfirst($convo->platform) }} Chat</h4>
                                        <p class="text-xs text-text-muted mt-0.5">Last message: {{ $convo->updated_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                <span class="text-primary text-xs font-black uppercase tracking-widest">Open Chat</span>
                            </div>
                        </x-card>
                    @empty
                        <div class="text-center py-20 bg-gray-50 rounded-3xl border-2 border-dashed border-gray-100">
                            <p class="text-gray-400 font-bold uppercase tracking-widest text-xs">No active conversations yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-container>
