<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reports Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Financial Report -->
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 flex flex-col justify-between">
                    <div>
                        <div class="h-12 w-12 bg-green-100 rounded-2xl flex items-center justify-center text-green-600 mb-6">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 mb-2 tracking-tight">Financial Health</h4>
                        <div class="space-y-4 mt-6">
                            @forelse($monthlyInvoices as $invoice)
                                <div class="flex justify-between items-center text-sm border-b border-gray-50 pb-3">
                                    <span class="text-gray-500 font-medium">{{ $invoice->month }}</span>
                                    <span class="font-bold text-gray-900">${{ number_format($invoice->total, 2) }}</span>
                                </div>
                            @empty
                                <p class="text-xs text-gray-400">No financial data available yet.</p>
                            @endforelse
                        </div>
                    </div>
                    <button class="w-full mt-8 bg-gray-900 text-white py-3 rounded-2xl font-bold text-sm hover:bg-gray-800 transition shadow-lg shadow-gray-200">
                        Export Report
                    </button>
                </div>

                <!-- Campaign Stats -->
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                    <div class="h-12 w-12 bg-blue-100 rounded-2xl flex items-center justify-center text-blue-600 mb-6">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                        </svg>
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 mb-2 tracking-tight">Campaign Distribution</h4>
                    <div class="space-y-4 mt-6">
                        @forelse($campaignStats as $stat)
                            <div>
                                <div class="flex justify-between text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">
                                    <span>{{ $stat->status }}</span>
                                    <span>{{ $stat->count }}</span>
                                </div>
                                <div class="w-full bg-gray-50 rounded-full h-1.5">
                                    <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ ($stat->count / max($campaignStats->sum('count'), 1)) * 100 }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400">No campaign data.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Lead Sources -->
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                    <div class="h-12 w-12 bg-purple-100 rounded-2xl flex items-center justify-center text-purple-600 mb-6">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 mb-2 tracking-tight">Lead Performance</h4>
                    <div class="space-y-4 mt-6">
                        @forelse($leadStats as $stat)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                                <span class="text-xs font-bold text-gray-600">{{ $stat->source }}</span>
                                <span class="bg-white shadow-sm px-3 py-1 rounded-lg text-xs font-black text-gray-900">{{ $stat->count }}</span>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400">No leads data.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>