<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lead Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <!-- Header Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl border border-gray-100">
                <div class="p-8 md:p-12">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                        <div class="flex items-center">
                            <div class="h-16 w-16 bg-blue-600 rounded-3xl flex items-center justify-center text-white text-2xl font-black shadow-xl shadow-blue-100 mr-6">
                                {{ substr($lead->name, 0, 1) }}
                            </div>
                            <div>
                                <h3 class="text-3xl font-black text-gray-900 tracking-tight">{{ $lead->name }}</h3>
                                <p class="text-gray-400 font-bold text-sm tracking-widest uppercase mt-1">Lead ID: {{ substr($lead->id, 0, 8) }}</p>
                            </div>
                        </div>
                        <div class="mt-6 md:mt-0 flex items-center space-x-4">
                            @php
                                $statusColors = [
                                    'new' => 'bg-gray-100 text-gray-600',
                                    'contacted' => 'bg-blue-100 text-blue-700',
                                    'qualified' => 'bg-green-100 text-green-700',
                                    'negotiation' => 'bg-purple-100 text-purple-700',
                                    'closed_won' => 'bg-indigo-600 text-white',
                                    'closed_lost' => 'bg-red-100 text-red-700',
                                ];
                                $color = $statusColors[$lead->status] ?? 'bg-gray-100 text-gray-600';
                            @endphp
                            <span class="{{ $color }} px-5 py-2 rounded-full text-xs font-black uppercase tracking-widest shadow-sm">
                                {{ str_replace('_', ' ', $lead->status) }}
                            </span>
                            <button class="bg-indigo-600 text-white px-6 py-2.5 rounded-2xl text-sm font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition active:scale-95">
                                Convert to Client
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mt-12 pt-12 border-t border-gray-50">
                        <div>
                            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Lead Source</div>
                            <div class="text-lg font-bold text-gray-900 uppercase">{{ $lead->source }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Project Score</div>
                            <div class="text-lg font-bold text-gray-900">{{ $lead->score ?? '0' }}/100</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Estimated Value</div>
                            <div class="text-lg font-bold text-gray-900">${{ number_format($lead->value ?? 0, 2) }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Acquisition</div>
                            <div class="text-lg font-bold text-gray-900">{{ $lead->created_at->format('M d, Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Info Section -->
                <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm">
                    <h4 class="text-xl font-black text-gray-900 tracking-tight mb-8">Prospect Intelligence</h4>
                    <div class="space-y-6">
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Email Address</label>
                            <div class="text-sm font-bold text-indigo-600">{{ $lead->email }}</div>
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Phone Number</label>
                            <div class="text-sm font-bold text-gray-900">{{ $lead->phone ?? 'Not provided' }}</div>
                        </div>
                        @if($lead->company)
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Organization</label>
                                <div class="text-sm font-bold text-gray-900">{{ $lead->company }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Notes Section -->
                <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm">
                    <h4 class="text-xl font-black text-gray-900 tracking-tight mb-8">Discovery Notes</h4>
                    <div class="p-6 bg-gray-50 rounded-2xl border border-gray-100 italic text-gray-600 text-sm">
                        {{ $lead->notes ?? 'No internal briefing notes available for this prospect.' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>