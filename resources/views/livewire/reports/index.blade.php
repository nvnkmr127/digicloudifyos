<x-app-container>
    <x-page-header title="Agency Performance Reports">
        <div class="flex space-x-2">
            <x-select wire:model.live="dateRange" class="rounded-xl border-none shadow-sm font-bold uppercase tracking-widest text-[10px]">
                <option value="today">Today</option>
                <option value="this_week">This Week</option>
                <option value="this_month">This Month</option>
                <option value="this_quarter">This Quarter</option>
                <option value="this_year">This Year</option>
            </x-select>
            <x-button color="outline" class="rounded-xl shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                PDF
            </x-button>
        </div>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Financial Report Card -->
        <x-card class="p-8 border-none shadow-2xl rounded-[3rem] bg-gradient-to-br from-indigo-50 to-white">
            <h3 class="text-xl font-black text-gray-900 tracking-tight mb-6 uppercase">Financial Overview</h3>
            <div class="space-y-6">
                <div class="flex justify-between items-center py-4 border-b border-gray-100 italic">
                    <span class="text-sm font-bold text-gray-400 uppercase tracking-widest">Total Invoiced</span>
                    <span class="text-lg font-black text-indigo-600">${{ number_format($reportData['financial']['total_invoiced'], 2) }}</span>
                </div>
                <div class="flex justify-between items-center py-4 border-b border-gray-100 italic">
                    <span class="text-sm font-bold text-gray-400 uppercase tracking-widest">Total Paid</span>
                    <span class="text-lg font-black text-green-600">${{ number_format($reportData['financial']['total_paid'], 2) }}</span>
                </div>
                <div class="flex justify-between items-center py-4 italic">
                    <span class="text-sm font-bold text-gray-400 uppercase tracking-widest">Pending</span>
                    <span class="text-lg font-black text-amber-500">${{ number_format($reportData['financial']['pending_amount'], 2) }}</span>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-gray-100 flex justify-center">
                <div class="h-32 w-32 rounded-full border-8 border-indigo-100 flex items-center justify-center">
                    <span class="text-xs font-black text-indigo-400 uppercase tracking-widest">Revenue</span>
                </div>
            </div>
        </x-card>

        <!-- Projects Report Card -->
        <x-card class="p-8 border-none shadow-2xl rounded-[3rem] bg-gradient-to-br from-amber-50 to-white">
            <h3 class="text-xl font-black text-gray-900 tracking-tight mb-6 uppercase">Workload & Projects</h3>
            <div class="space-y-6">
                <div class="flex justify-between items-center py-4 border-b border-gray-100 italic">
                    <span class="text-sm font-bold text-gray-400 uppercase tracking-widest">Active Projects</span>
                    <span class="text-lg font-black text-amber-600">{{ $reportData['performance']['active_projects'] }}</span>
                </div>
                <div class="flex justify-between items-center py-4 border-b border-gray-100 italic">
                    <span class="text-sm font-bold text-gray-400 uppercase tracking-widest">Total Hours</span>
                    <span class="text-lg font-black text-amber-600">{{ number_format($reportData['performance']['total_hours'], 1) }}</span>
                </div>
                <div class="flex justify-between items-center py-4 italic">
                    <span class="text-sm font-bold text-gray-400 uppercase tracking-widest">Efficiency</span>
                    <span class="text-lg font-black text-amber-600">92%</span>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-gray-100 flex justify-center">
                <div class="h-32 w-32 rounded-full border-8 border-amber-100 flex items-center justify-center">
                    <span class="text-xs font-black text-amber-400 uppercase tracking-widest">Efficiency</span>
                </div>
            </div>
        </x-card>

        <!-- Clients Report Card -->
        <x-card class="p-8 border-none shadow-2xl rounded-[3rem] bg-gradient-to-br from-pink-50 to-white">
            <h3 class="text-xl font-black text-gray-900 tracking-tight mb-6 uppercase">Retention & Clients</h3>
            <div class="space-y-6">
                <div class="flex justify-between items-center py-4 border-b border-gray-100 italic">
                    <span class="text-sm font-bold text-gray-400 uppercase tracking-widest">Total Clients</span>
                    <span class="text-lg font-black text-pink-600">{{ $reportData['clients']['total_clients'] }}</span>
                </div>
                <div class="flex justify-between items-center py-4 border-b border-gray-100 italic">
                    <span class="text-sm font-bold text-gray-400 uppercase tracking-widest">New Acquisition</span>
                    <span class="text-lg font-black text-pink-600">{{ $reportData['clients']['new_clients_this_month'] }}</span>
                </div>
                <div class="flex justify-between items-center py-4 italic">
                    <span class="text-sm font-bold text-gray-400 uppercase tracking-widest">Loyalty Rate</span>
                    <span class="text-lg font-black text-pink-600">88%</span>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-gray-100 flex justify-center">
                <div class="h-32 w-32 rounded-full border-8 border-pink-100 flex items-center justify-center">
                    <span class="text-xs font-black text-pink-400 uppercase tracking-widest">Retention</span>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Analytics Trends Placeholder -->
    <x-card class="mt-12 p-12 border-none shadow-2xl rounded-[4rem]">
        <h3 class="text-2xl font-black text-gray-900 tracking-tighter mb-10 text-center uppercase">Agency Growth Projection</h3>
        <div class="h-64 bg-gray-50 rounded-[3rem] border-4 border-dashed border-gray-100 flex items-center justify-center">
            <p class="text-sm font-black text-gray-300 uppercase tracking-[0.5em]">Real-time Visualization Engine Loading...</p>
        </div>
    </x-card>
</x-app-container>
