<x-app-container>
    <x-page-header title="Analytics & Reporting">
        <x-select class="py-1 px-4 mr-2">
            <option>Last 30 Days</option>
            <option>This Quarter</option>
            <option>Year to Date</option>
        </x-select>
        <x-button color="primary">Generate Report</x-button>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
        <x-card class="bg-white border-l-4 border-primary">
            <h4 class="text-xs font-semibold text-text-muted uppercase">Gross Revenue</h4>
            <h2 class="text-3xl font-black text-text-primary mt-2">${{ number_format($revenue) }}</h2>
            <p class="text-[10px] text-green-600 mt-1 uppercase font-bold">&uarr; +${{ number_format($pending) }}
                pending</p>
        </x-card>
        <x-card class="bg-white border-l-4 border-green-500">
            <h4 class="text-xs font-semibold text-text-muted uppercase">Total Clients</h4>
            <h2 class="text-3xl font-black text-text-primary mt-2">{{ $clientsCount }}</h2>
            <p class="text-[10px] text-green-600 mt-1 font-bold uppercase">&uarr; {{ $projectsCount }} active projects
            </p>
        </x-card>
        <x-card class="bg-white border-l-4 border-yellow-500">
            <h4 class="text-xs font-semibold text-text-muted uppercase">Conversion Rate</h4>
            <h2 class="text-3xl font-black text-text-primary mt-2">{{ $conversionRate }}%</h2>
            <p class="text-[10px] text-red-600 mt-1 font-bold uppercase">&darr; 2.1% vs last period</p>
        </x-card>
        <x-card class="bg-white border-l-4 border-purple-500">
            <h4 class="text-xs font-semibold text-text-muted uppercase">CAC (Cost of Acq.)</h4>
            <h2 class="text-3xl font-black text-text-primary mt-2">${{ $cac }}</h2>
            <p class="text-[10px] text-green-600 mt-1 font-bold uppercase">&uarr; $50 better vs last period</p>
        </x-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card>
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-text-primary">Source vs Conversion</h3>
            </div>
            <div
                class="h-64 bg-gray-50 rounded-lg flex items-center justify-center border border-gray-100 text-gray-400">
                [ Bar Chart Visualization Placeholder ]
            </div>
        </x-card>
        <x-card>
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-text-primary">Sales Pipeline Funnel</h3>
            </div>
            <div
                class="h-64 bg-gray-50 rounded-lg flex items-center justify-center border border-gray-100 text-gray-400">
                [ Funnel Chart Visualization Placeholder ]
            </div>
        </x-card>
    </div>
</x-app-container>