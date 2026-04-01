<x-app-container>
    <x-page-header title="Agency Intelligence Dashboard">
        <x-button color="outline" class="rounded-xl">Export Summary</x-button>
    </x-page-header>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
        <x-card class="p-8 bg-gradient-to-br from-indigo-50 to-white shadow-sm border-none rounded-[2rem]">
            <div class="text-[10px] font-black uppercase tracking-widest text-indigo-400 mb-2">Agency Revenue (YTD)</div>
            <div class="text-3xl font-black text-indigo-900 tracking-tighter">${{ number_format($stats['revenue'], 2) }}</div>
        </x-card>

        <x-card class="p-8 bg-gradient-to-br from-red-50 to-white shadow-sm border-none rounded-[2rem]">
            <div class="text-[10px] font-black uppercase tracking-widest text-red-300 mb-2">Pending Invoices</div>
            <div class="text-3xl font-black text-red-900 tracking-tighter">${{ number_format($stats['pending'], 2) }}</div>
        </x-card>

        <x-card class="p-8 bg-gradient-to-br from-green-50 to-white shadow-sm border-none rounded-[2rem]">
            <div class="text-[10px] font-black uppercase tracking-widest text-green-400 mb-2">Active Clients</div>
            <div class="text-3xl font-black text-green-900 tracking-tighter">{{ $stats['active_clients'] }}</div>
        </x-card>

        <x-card class="p-8 bg-gradient-to-br from-amber-50 to-white shadow-sm border-none rounded-[2rem]">
            <div class="text-[10px] font-black uppercase tracking-widest text-amber-500 mb-2">Completion Rate</div>
            <div class="text-3xl font-black text-amber-900 tracking-tighter">{{ number_format($stats['project_completion'], 1) }}%</div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            <x-card class="border-none shadow-xl rounded-[2.5rem] p-8">
                <h3 class="text-xl font-black text-gray-900 tracking-tight mb-6">Revenue Growth</h3>
                <div class="h-64 bg-gray-50 rounded-3xl border border-dashed border-gray-200 flex items-center justify-center text-gray-400 font-bold uppercase tracking-widest text-xs">
                    Growth Trends Chart Here
                </div>
            </x-card>
        </div>

        <div class="space-y-8">
            <x-card class="border-none shadow-xl rounded-[2.5rem] p-8 bg-branding text-white">
                <h3 class="text-lg font-black tracking-tight mb-4 text-white">Team & Capacity</h3>
                <div class="space-y-6">
                    <div class="flex justify-between items-center text-sm border-t border-white/10 pt-4">
                        <span class="opacity-70">Total Headcount</span>
                        <span class="font-black">{{ $stats['team_size'] }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm border-t border-white/10 pt-4">
                        <span class="opacity-70">Hours Logged (Month)</span>
                        <span class="font-black">{{ number_format($stats['monthly_hours'], 1) }}</span>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</x-app-container>
