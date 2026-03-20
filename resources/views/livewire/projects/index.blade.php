<x-app-container>
    <x-page-header title="Project Matrix">
        <x-button color="primary" href="{{ route('projects.create') }}" wire:navigate class="rounded-xl shadow-lg shadow-indigo-100">
            + Initiate Project
        </x-button>
    </x-page-header>

    <!-- Search & Filter Bar -->
    <div class="mb-10 flex flex-col md:flex-row gap-4 items-center justify-between">
        <div class="relative w-full max-w-md group">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-gray-400 group-focus-within:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <x-input wire:model.live="search" type="text" placeholder="Search project matrix..." class="pl-11 rounded-2xl border-gray-100 focus:ring-indigo-500/20" />
        </div>
        
        <div class="flex items-center gap-3">
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Projects: {{ $projects->total() }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
        @forelse($projects as $project)
            <x-card class="group relative overflow-hidden p-8 rounded-card-premium border-none shadow-xl shadow-gray-100/30 hover:shadow-2xl hover:shadow-indigo-100/40 transition-all duration-500">
                <div class="flex justify-between items-start gap-4 mb-8">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="text-[9px] font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100/50">
                                {{ $project->project_code ?? 'PRJ-' . substr($project->id, 0, 4) }}
                            </span>
                            <x-status-badge :status="$project->status" type="client" class="!px-3 !py-1 !text-[9px]" />
                        </div>
                        <h3 class="text-2xl font-black text-gray-900 tracking-tight group-hover:text-indigo-600 transition-colors">
                            {{ $project->name }}
                        </h3>
                        <p class="text-xs font-bold text-gray-400 mt-1">Entity: <span class="text-gray-600">{{ $project->client->name ?? 'Internal' }}</span></p>
                    </div>
                    
                    <div class="flex -space-x-3">
                        @if($project->projectManager)
                            <div class="h-10 w-10 rounded-xl bg-indigo-600 border-2 border-white flex items-center justify-center text-[10px] font-black text-white shadow-lg" title="PM: {{ $project->projectManager->full_name }}">
                                {{ substr($project->projectManager->full_name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-6 py-6 border-t border-b border-gray-50">
                    <x-detail-label label="Capital Allocation">
                        <span class="text-sm font-black text-gray-900">${{ number_format($project->budget, 0) }}</span>
                    </x-detail-label>
                    <x-detail-label label="Burn Rate">
                        <span class="text-sm font-black {{ $project->actual_cost > $project->budget ? 'text-rose-600' : 'text-gray-900' }}">
                            ${{ number_format($project->actual_cost, 0) }}
                        </span>
                    </x-detail-label>
                    <x-detail-label label="Timeline End">
                        <span class="text-sm font-black text-gray-900">
                            {{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('M d, Y') : 'Open' }}
                        </span>
                    </x-detail-label>
                </div>

                <div class="mt-8 flex items-center justify-between">
                    <div class="w-1/2 bg-gray-100 h-1.5 rounded-full overflow-hidden">
                        @php
                            $progress = $project->budget > 0 ? min(100, ($project->actual_cost / $project->budget) * 100) : 0;
                        @endphp
                        <div class="h-full bg-indigo-600 rounded-full transition-all duration-1000" style="width: {{ $progress }}%"></div>
                    </div>
                    
                    <div class="flex gap-3">
                        <x-button color="outline" href="{{ route('projects.show', $project->id) }}" wire:navigate class="rounded-xl !py-2 !px-5 text-branding border-gray-100 hover:bg-gray-50">
                            Inspect
                        </x-button>
                        
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="p-2 text-gray-400 hover:text-indigo-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                </svg>
                            </button>
                            
                            <div x-show="open" @click.away="open = false" class="absolute right-0 bottom-full mb-2 w-48 bg-white rounded-2xl shadow-xl border border-gray-50 p-2 z-10">
                                <a href="{{ route('projects.edit', $project->id) }}" wire:navigate class="flex items-center px-4 py-2 text-xs font-bold text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl transition-colors">
                                    Update Strategy
                                </a>
                                <button type="button" @click="$dispatch('open-modal', 'confirm-project-deletion-{{ $project->id }}'); open = false" class="w-full text-left flex items-center px-4 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 rounded-xl transition-colors">
                                    Archive Protocol
                                </button>
                            </div>
                        </div>

                        <x-modal name="confirm-project-deletion-{{ $project->id }}">
                            <div class="p-10 text-center">
                                <div class="w-20 h-20 bg-rose-50 rounded-3xl flex items-center justify-center mx-auto mb-6">
                                    <svg class="w-10 h-10 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </div>
                                <h2 class="text-2xl font-black text-gray-900 tracking-tight">Archive Project?</h2>
                                <p class="mt-4 text-gray-500 font-medium leading-relaxed">This will suspend all activities related to <span class="text-gray-900 font-bold">{{ $project->name }}</span>. This action can be reversed by administrators.</p>
                                <div class="mt-10 flex flex-col sm:flex-row justify-center gap-4">
                                    <x-button color="outline" x-on:click="$dispatch('close')" class="rounded-2xl px-8 order-2 sm:order-1">Abort</x-button>
                                    <x-button color="danger" wire:click="delete('{{ $project->id }}')" x-on:click="$dispatch('close')" class="rounded-2xl px-8 shadow-lg shadow-rose-100 order-1 sm:order-2">Proceed with Archive</x-button>
                                </div>
                            </div>
                        </x-modal>
                    </div>
                </div>
            </x-card>
        @empty
            <div class="lg:col-span-2 py-24 text-center rounded-[3rem] border-2 border-dashed border-gray-100 bg-gray-50/50">
                <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm border border-gray-100">
                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h4 class="text-xl font-black text-gray-900 tracking-tight">Zero Projects Detected</h4>
                <p class="mt-2 text-gray-400 font-medium">Your project matrix is currently offline.</p>
                <div class="mt-8">
                    <x-button color="primary" href="{{ route('projects.create') }}" wire:navigate class="rounded-2xl px-10">Start First Project</x-button>
                </div>
            </div>
        @endforelse
    </div>

    @if($projects->hasPages())
        <div class="mt-10">
            {{ $projects->links() }}
        </div>
    @endif
</x-app-container>