<x-app-container>
    <x-page-header title="Opportunities & Pipelines">
        <x-button color="outline" class="mr-2">Manage Pipelines</x-button>
        <x-button color="primary">Add Opportunity</x-button>
    </x-page-header>

    <div class="mb-6 flex justify-between items-center bg-white p-3 rounded-md shadow-sm border border-gray-100">
        <div class="flex items-center space-x-4">
            <h3 class="font-semibold text-text-primary">
                {{ $selectedPipeline ? $selectedPipeline->name : 'No Pipeline Selected' }}
            </h3>
            @if($selectedPipeline)
                <span class="text-text-muted text-sm">Showing {{ $selectedPipeline->opportunities->count() }}
                    opportunities</span>
            @endif
        </div>
        <div class="flex items-center space-x-2">
            <x-select wire:model.live="selectedPipelineId" class="py-1 text-sm border-gray-300">
                @foreach($pipelines as $pipeline)
                    <option value="{{ $pipeline->id }}">{{ $pipeline->name }}</option>
                @endforeach
            </x-select>
        </div>
    </div>

    <!-- Pipeline Kanban -->
    <div class="flex overflow-x-auto space-x-4 pb-4">

        @if($selectedPipeline)
            @foreach($selectedPipeline->stages as $stage)
                <div class="w-80 flex-shrink-0 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="p-3 border-b border-gray-200 flex justify-between items-center">
                        <h4 class="font-semibold text-text-primary">{{ $stage->name }}</h4>
                        <span
                            class="bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded-full">{{ $stage->opportunities->count() }}</span>
                    </div>
                    <!-- Calculate colors dynamically safely -->
                    @php
                        $colors = ['blue', 'indigo', 'yellow', 'green', 'purple', 'red'];
                        $theme = $colors[$loop->index % count($colors)];
                    @endphp
                    <div class="p-2 space-y-3 min-h-[100px]"
                        x-data="{ dragOver: false }"
                        @dragover.prevent="dragOver = true"
                        @dragleave.prevent="dragOver = false"
                        @drop.prevent="dragOver = false; $wire.updateOpportunityStage(event.dataTransfer.getData('opportunityId'), {{ $stage->id }})"
                        :class="{ 'bg-gray-100 ring-2 ring-primary rounded-lg': dragOver }"
                    >
                        @forelse($stage->opportunities as $opportunity)
                            <x-card class="cursor-grab hover:border-primary p-3 shadow-sm border-l-4 border-l-{{ $theme }}-500"
                                draggable="true"
                                @dragstart="event.dataTransfer.setData('opportunityId', {{ $opportunity->id }})"
                                wire:key="opportunity-{{ $opportunity->id }}"
                            >
                                <h5 class="text-sm font-bold text-text-primary mb-1">{{ $opportunity->name }}
                                    (${{ number_format($opportunity->monetary_value) }})</h5>
                                <p class="text-xs text-text-muted mb-2">
                                    @if($opportunity->contact)
                                        {{ $opportunity->contact->first_name }} {{ $opportunity->contact->last_name }}
                                        @if($opportunity->contact->company_name)
                                            &bull; {{ $opportunity->contact->company_name }}
                                        @endif
                                    @else
                                        No Contact Assigned
                                    @endif
                                </p>
                                <div class="flex justify-between items-center mt-2">
                                    <span
                                        class="text-xs bg-{{ $theme }}-100 text-{{ $theme }}-800 px-2 py-0.5 rounded">{{ $opportunity->status }}</span>
                                    <span class="text-xs text-text-muted">{{ $opportunity->created_at->diffForHumans() }}</span>
                                </div>
                            </x-card>
                        @empty
                            <div class="text-center p-4">
                                <span class="text-xs text-gray-400">No opportunities in this stage.</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        @endif

    </div>
</x-app-container>