<div class="p-8 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-end mb-10">
            <div>
                <h1 class="text-4xl font-black text-gray-900 tracking-tight">Intelligence Reports</h1>
                <p class="text-gray-500 mt-2 font-medium">Generate and manage performance reports for your clients.</p>
            </div>
            <button wire:click="$set('showCreateModal', true)"
                class="bg-indigo-600 text-white px-8 py-4 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 transition shadow-xl shadow-indigo-100 flex items-center gap-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                Generate New Report
            </button>
        </div>

        @if (session()->has('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-100 text-green-700 rounded-2xl font-bold text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-100 text-red-700 rounded-2xl font-bold text-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-50">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-8 py-6 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Report Name</th>
                            <th class="px-8 py-6 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Type</th>
                            <th class="px-8 py-6 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Format</th>
                            <th class="px-8 py-6 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                            <th class="px-8 py-6 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Generated</th>
                            <th class="px-8 py-6 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($reports as $report)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <div class="font-black text-gray-900 tracking-tight">{{ $report->name }}</div>
                                    <div class="text-[9px] font-bold text-gray-400 uppercase mt-1">{{ $report->client?->name ?? 'Internal Report' }}</div>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-gray-600">{{ $report->type }}</span>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <span class="inline-flex px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest {{ $report->format === 'pdf' ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600' }}">
                                        {{ $report->format }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest {{ $report->status === 'COMPLETED' ? 'text-green-600' : ($report->status === 'FAILED' ? 'text-red-600' : 'text-amber-500') }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $report->status === 'COMPLETED' ? 'bg-green-600' : ($report->status === 'FAILED' ? 'bg-red-600' : 'bg-amber-500 animate-pulse') }}"></span>
                                        {{ $report->status }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap text-[10px] font-bold text-gray-500">
                                    {{ $report->generated_at ? $report->generated_at->diffForHumans() : 'N/A' }}
                                </td>
                                <td class="px-8 py-6 text-right whitespace-nowrap">
                                    <div class="flex justify-end gap-2">
                                        @if($report->status === 'COMPLETED')
                                            <button wire:click="download('{{ $report->id }}')" class="p-2 bg-indigo-50 text-indigo-600 rounded-xl hover:bg-indigo-600 hover:text-white transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                            </button>
                                        @endif
                                        <button wire:click="deleteReport('{{ $report->id }}')" wire:confirm="Are you sure?" class="p-2 bg-red-50 text-red-600 rounded-xl hover:bg-red-600 hover:text-white transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center text-gray-300 mb-4 text-2xl">📊</div>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">No intelligence reports found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-8 py-6 border-t border-gray-50">
                {{ $reports->links() }}
            </div>
        </div>
    </div>

    <!-- Create Report Modal -->
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="$set('showCreateModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-middle bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full p-10">
                    <h3 class="text-2xl font-black text-gray-900 tracking-tight mb-8">Generate Intelligence Report</h3>
                    
                    <form wire:submit.prevent="generateReport">
                        <div class="space-y-6">
                            <div>
                                <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-2">Report Name</label>
                                <input type="text" wire:model="reportName" placeholder="e.g., Q1 Performance Review" class="w-full bg-gray-50 border-none rounded-2xl px-5 py-4 font-bold text-gray-900 focus:ring-2 focus:ring-indigo-500 transition">
                                @error('reportName') <span class="text-red-500 text-[10px] font-bold mt-1 uppercase">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-2">Type</label>
                                    <select wire:model="reportType" class="w-full bg-gray-50 border-none rounded-2xl px-5 py-4 font-bold text-gray-900 focus:ring-2 focus:ring-indigo-500 transition">
                                        <option value="campaign">Campaign Performance</option>
                                        <option value="audience">Audience Insights</option>
                                        <option value="creative">Creative Assets</option>
                                        <option value="daily">Daily Performance</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-2">Format</label>
                                    <select wire:model="reportFormat" class="w-full bg-gray-50 border-none rounded-2xl px-5 py-4 font-bold text-gray-900 focus:ring-2 focus:ring-indigo-500 transition">
                                        <option value="pdf">Professional PDF</option>
                                        <option value="excel">Excel Datasheet</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-2">Target Client</label>
                                <select wire:model="clientId" class="w-full bg-gray-50 border-none rounded-2xl px-5 py-4 font-bold text-gray-900 focus:ring-2 focus:ring-indigo-500 transition">
                                    <option value="">Internal Use (Full Account)</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-2">Date Range (Last X Days)</label>
                                <select wire:model="dateRange" class="w-full bg-gray-50 border-none rounded-2xl px-5 py-4 font-bold text-gray-900 focus:ring-2 focus:ring-indigo-500 transition">
                                    <option value="7">Last 7 Days</option>
                                    <option value="30">Last 30 Days</option>
                                    <option value="90">Last 90 Days</option>
                                    <option value="365">Last Year</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-10 flex gap-4">
                            <button type="button" wire:click="$set('showCreateModal', false)" class="flex-1 px-8 py-4 rounded-2xl font-black text-xs uppercase tracking-widest text-gray-400 hover:text-gray-900 transition">
                                Cancel
                            </button>
                            <button type="submit" class="flex-1 bg-gray-900 text-white px-8 py-4 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-indigo-600 transition shadow-xl shadow-gray-200">
                                Start Generation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>