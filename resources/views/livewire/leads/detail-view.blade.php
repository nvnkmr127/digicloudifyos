<x-app-container>
    <div class="mb-4">
        <a href="{{ route('leads.index') }}" wire:navigate class="text-sm text-text-muted hover:text-primary hover:font-bold transition-all">
            &larr; Back to Leads Pipeline
        </a>
    </div>

    <x-page-header :title="'Lead Details: ' . $lead->name">
        <div class="flex items-center space-x-3">
            <x-status-badge :status="$lead->status" type="lead" class="py-2 px-6" />
            <x-button color="primary" class="rounded-2xl shadow-lg shadow-indigo-100">
                Convert to Client
            </x-button>
        </div>
    </x-page-header>

    <div class="space-y-8">
        <!-- Header Card -->
        <x-card class="border-none shadow-xl shadow-gray-100 bg-gradient-to-br from-white to-[#FDFDFF]">
            <div class="flex flex-col md:flex-row items-center md:items-start space-y-6 md:space-y-0 md:space-x-8">
                <div class="h-20 w-20 bg-indigo-600 rounded-card-premium flex items-center justify-center text-white text-3xl font-black shadow-2xl shadow-indigo-200">
                    {{ substr($lead->name, 0, 1) }}
                </div>
                <div class="flex-1 text-center md:text-left">
                    <h3 class="text-4xl font-black text-gray-900 tracking-tight leading-none mb-2">
                        {{ $lead->name }}
                    </h3>
                    <p class="text-indigo-600 font-black text-xs tracking-widest uppercase">ID: {{ substr($lead->id, 0, 8) }} • PROSPECT</p>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mt-10 pt-8 border-t border-gray-50 text-left">
                        <x-detail-label label="Lead Source" class="uppercase">{{ $lead->source }}</x-detail-label>
                        <x-detail-label label="Project Score">{{ $lead->score ?? '0' }}/100</x-detail-label>
                        <x-detail-label label="Est. Value">${{ number_format($lead->value ?? 0, 2) }}</x-detail-label>
                        <x-detail-label label="Acquired">{{ $lead->created_at->format('M d, Y') }}</x-detail-label>
                    </div>
                </div>
            </div>
        </x-card>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Contact Info -->
            <x-card class="lg:col-span-2 border-none shadow-lg shadow-gray-50 p-8">
                <h4 class="text-xl font-black text-gray-900 tracking-tight mb-8 flex items-center">
                    <span class="w-1.5 h-6 bg-indigo-600 rounded-full mr-3"></span>
                    Prospect Intelligence
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <x-detail-label label="Email Address">
                        <span class="text-indigo-600 underline font-bold">{{ $lead->email }}</span>
                    </x-detail-label>
                    <x-detail-label label="Phone Number">{{ $lead->phone ?? 'Not provided' }}</x-detail-label>
                    
                    @if($lead->company)
                        <x-detail-label label="Organization" class="md:col-span-2">{{ $lead->company }}</x-detail-label>
                    @endif
                </div>
            </x-card>

            <!-- Metadata / Notes -->
            <x-card class="border-none shadow-lg shadow-gray-50 p-8 bg-gray-50">
                <h4 class="text-xl font-black text-gray-900 tracking-tight mb-8">Discovery Notes</h4>
                <div class="p-6 bg-white rounded-3xl border border-gray-100 italic text-gray-600 text-sm leading-relaxed shadow-sm">
                    {{ $lead->notes ?? 'No internal briefing notes available for this prospect.' }}
                </div>
            </x-card>
        </div>
    </div>
</x-app-container>