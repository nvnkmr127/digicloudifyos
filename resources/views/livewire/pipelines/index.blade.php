<x-app-container>
    <x-page-header title="Revenue Pipeline">
        <div class="flex items-center space-x-3">
            <x-button color="outline" class="rounded-xl text-branding">
                Configure Streams
            </x-button>
            <a href="{{ route('opportunities.create') }}">
                <x-button color="primary" class="rounded-xl shadow-lg shadow-primary-soft/30">
                    + New Opportunity
                </x-button>
            </a>
        </div>
    </x-page-header>

    <x-card variant="default" class="mb-10 flex flex-col md:flex-row justify-between items-center rounded-card shadow-xl shadow-gray-100/30 border-none p-6">
        <div class="flex items-center gap-6">
            <div class="h-12 w-12 bg-primary rounded-2xl flex items-center justify-center text-white shadow-lg shadow-primary/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <div>
                <h3 class="text-xl font-black text-gray-900 tracking-tight">
                    {{ $selectedPipeline ? $selectedPipeline->name : 'No Pipeline Stream' }}
                </h3>
                @if($selectedPipeline)
                    <p class="text-branding text-brand-muted mt-1">
                        Active Opportunities: {{ $selectedPipeline->opportunities->count() }}
                    </p>
                @endif
            </div>
        </div>
        
        <div class="mt-4 md:mt-0 flex items-center gap-4">
            <label class="text-branding text-brand-muted">Switch Stream:</label>
            <x-select wire:model.live="selectedPipelineId" class="rounded-xl py-2 px-4 bg-gray-50 border-none shadow-inner min-w-[200px]">
                @foreach($pipelines as $pipeline)
                    <option value="{{ $pipeline->id }}">{{ $pipeline->name }}</option>
                @endforeach
            </x-select>
        </div>
    </x-card>

    <!-- Pipeline Kanban -->
    <div class="flex overflow-x-auto space-x-6 pb-10 scrollbar-thin scrollbar-thumb-gray-200">
        @if($selectedPipeline)
            @foreach($selectedPipeline->stages as $stage)
                <div class="w-80 flex-shrink-0 flex flex-col">
                    <div class="bg-gray-50/80 border border-gray-100 rounded-card-premium flex flex-col h-full shadow-inner">
                        <div class="px-6 py-5 flex items-center justify-between">
                            <h4 class="text-branding text-gray-700 uppercase tracking-widest">{{ $stage->name }}</h4>
                            <span class="bg-white px-2.5 py-0.5 rounded-full text-branding text-brand-muted shadow-sm border border-gray-50">
                                {{ $stage->opportunities->count() }}
                            </span>
                        </div>

                        <div class="px-3 pb-6 flex-1 min-h-[500px] transition-all duration-300 rounded-[2rem]"
                            x-data="{ dragOver: false }"
                            @dragover.prevent="dragOver = true"
                            @dragenter.prevent="dragOver = true"
                            @dragleave.prevent="dragOver = false"
                            @drop.prevent="dragOver = false; $wire.updateOpportunityStage(event.dataTransfer.getData('opportunityId'), {{ $stage->id }})"
                            :class="{ 'bg-primary-soft scale-[0.98] ring-2 ring-primary/20 ring-dashed': dragOver }"
                        >
                            <div class="space-y-4">
                                @forelse($stage->opportunities as $opportunity)
                                    <x-card variant="default" class="cursor-grab active:cursor-grabbing hover:shadow-2xl hover:scale-[1.02] hover:border-primary/20 transition-all duration-300 p-5 rounded-card shadow-lg shadow-gray-200/20 group relative overflow-hidden"
                                        draggable="true"
                                        @dragstart="event.dataTransfer.setData('opportunityId', {{ $opportunity->id }})"
                                        wire:key="opportunity-{{ $opportunity->id }}"
                                    >
                                        <div class="flex justify-between items-start mb-4">
                                            <span class="text-branding text-primary bg-primary-soft px-2.5 py-1 rounded-full border border-primary/10">
                                                ID-{{ substr($opportunity->id, 0, 4) }}
                                            </span>
                                            <div class="flex items-center text-gray-900 font-black tracking-tight text-xs">
                                                <span class="text-gray-400 mr-0.5">$</span>
                                                {{ number_format($opportunity->monetary_value) }}
                                            </div>
                                        </div>

                                        <h5 class="text-sm font-black text-gray-900 mb-2 group-hover:text-indigo-600 transition-colors tracking-tight">{{ $opportunity->name }}</h5>
                                        
                                        <div class="flex items-center gap-2 mb-4">
                                            <div class="h-5 w-5 rounded-lg bg-gray-100 flex items-center justify-center text-[8px] font-black text-gray-500">
                                                {{ $opportunity->contact ? substr($opportunity->contact->first_name, 0, 1) : '?' }}
                                            </div>
                                            <p class="text-branding-wide text-brand-muted truncate mt-1">
                                                @if($opportunity->contact)
                                                    {{ $opportunity->contact->first_name }}
                                                    @if($opportunity->contact->company_name)
                                                        <span class="text-gray-300 font-medium">@ {{ $opportunity->contact->company_name }}</span>
                                                    @endif
                                                @else
                                                    No Contact
                                                @endif
                                            </p>
                                        </div>

                                        <div class="flex justify-between items-center pt-4 border-t border-gray-50">
                                            <x-status-badge :status="$opportunity->status" type="lead" class="!px-3 !py-1 text-branding uppercase" />
                                            <span class="text-branding text-brand-muted">{{ $opportunity->created_at->diffForHumans() }}</span>
                                        </div>

                                        <div class="absolute right-0 top-0 bottom-0 w-1 bg-primary opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                    </x-card>
                                @empty
                                    <div class="py-20 text-center">
                                        <p class="text-branding-wide text-brand-muted">Stage Optimized</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <!-- Processing Overlay -->
    <div wire:loading class="fixed bottom-10 right-10 z-50">
        <div class="bg-gray-900 rounded-3xl p-5 flex items-center space-x-4 shadow-2xl scale-75 md:scale-100 origin-bottom-right">
            <div class="relative">
                <div class="h-6 w-6 border-4 border-indigo-600/20 border-t-indigo-500 rounded-full animate-spin"></div>
            </div>
            <span class="text-white text-branding-wide">Synching...</span>
        </div>
    </div>
</x-app-container>