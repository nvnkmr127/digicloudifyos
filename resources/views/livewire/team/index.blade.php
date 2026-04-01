<x-app-container>
    <x-page-header title="Team Management">
        <x-button color="primary">Add Team Member</x-button>
    </x-page-header>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($employees as $employee)
            <x-card class="hover:shadow-2xl transition duration-300 transform hover:-translate-y-1 rounded-2xl overflow-hidden border-none shadow-xl shadow-gray-100">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-4">
                            <div class="h-16 w-16 rounded-2xl bg-gradient-to-br from-primary to-indigo-600 flex items-center justify-center text-white text-xl font-black shadow-lg shadow-primary/20">
                                {{ substr($employee->user->full_name ?? 'U', 0, 1) }}
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-gray-900 tracking-tight">{{ $employee->user->full_name ?? 'Unknown User' }}</h3>
                                <p class="text-xs font-bold text-primary uppercase tracking-widest mt-0.5">{{ $employee->position ?? 'Contributor' }}</p>
                            </div>
                        </div>
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-2xl border border-gray-100 z-10 py-2">
                                <button class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Edit Details</button>
                                <button class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">View Workload</button>
                                <div class="border-t border-gray-100 my-1"></div>
                                <button wire:click="removeMember('{{ $employee->id }}')" wire:confirm="Are you sure you want to remove this team member?" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Remove Member</button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                            <span class="text-[10px] uppercase tracking-widest text-text-muted font-bold block">Workload</span>
                            <span class="text-sm font-black text-gray-900">{{ number_format($employee->getUtilizationRate(), 1) }}%</span>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                            <span class="text-[10px] uppercase tracking-widest text-text-muted font-bold block">Rating</span>
                            <span class="text-sm font-black text-indigo-600">{{ number_format($employee->performance_rating, 1) }}/5</span>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center text-xs text-text-muted">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            {{ $employee->user->email ?? 'No email' }}
                        </div>
                        <div class="flex items-center text-xs text-text-muted">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1l1 1h5l1-1h1m-1 4h.01M9 16h.01"></path>
                            </svg>
                            {{ $employee->department ?? 'General' }}
                        </div>
                    </div>
                </div>
            </x-card>
        @empty
            <div class="lg:col-span-3 text-center py-20 bg-white rounded-3xl border-2 border-dashed border-gray-100">
                <div class="h-20 w-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-black text-gray-900">Build Your Agency Team</h3>
                <p class="text-brand-muted mt-2">Start by inviting your first creative or strategist.</p>
                <x-button color="primary" class="mt-8 px-10">Invite Member</x-button>
            </div>
        @endforelse
    </div>
</x-app-container>