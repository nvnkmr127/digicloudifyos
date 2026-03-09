<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Campaign Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <!-- Header Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl border border-gray-100">
                <div class="p-8 md:p-12">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                        <div class="flex items-center">
                            <div
                                class="h-16 w-16 bg-indigo-600 rounded-3xl flex items-center justify-center text-white text-2xl font-black shadow-xl shadow-indigo-100 mr-6">
                                {{ substr($campaign->name, 0, 1) }}
                            </div>
                            <div>
                                <h3 class="text-3xl font-black text-gray-900 tracking-tight">{{ $campaign->name }}</h3>
                                <p class="text-gray-400 font-bold text-sm tracking-widest uppercase mt-1">Project ID:
                                    {{ substr($campaign->id, 0, 8) }}</p>
                            </div>
                        </div>
                        <div class="mt-6 md:mt-0 flex items-center space-x-4">
                            @php
                                $statusColors = [
                                    'planning' => 'bg-gray-100 text-gray-600',
                                    'creative_requested' => 'bg-blue-100 text-blue-700',
                                    'ready' => 'bg-purple-100 text-purple-700',
                                    'running' => 'bg-green-100 text-green-700',
                                    'completed' => 'bg-black text-white',
                                ];
                                $color = $statusColors[$campaign->status] ?? 'bg-gray-100 text-gray-600';
                            @endphp
                            <span
                                class="{{ $color }} px-5 py-2 rounded-full text-xs font-black uppercase tracking-widest shadow-sm">
                                {{ str_replace('_', ' ', $campaign->status) }}
                            </span>
                            <button
                                class="bg-indigo-600 text-white px-6 py-2.5 rounded-2xl text-sm font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition active:scale-95">
                                Edit Project
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mt-12 pt-12 border-t border-gray-50">
                        <div>
                            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Client
                                Partner</div>
                            <div class="text-lg font-bold text-gray-900">{{ $campaign->client->name }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Ad
                                Infrastructure</div>
                            <div class="text-lg font-bold text-gray-900">
                                {{ $campaign->adAccount->account_name ?? 'Not Linked' }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Budgeting
                            </div>
                            <div class="text-lg font-bold text-gray-900">${{ number_format($campaign->budget, 2) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Timeline
                            </div>
                            <div class="text-lg font-bold text-gray-900">
                                {{ $campaign->start_date ? $campaign->start_date->format('M d') : 'TBD' }}
                                @if($campaign->end_date) - {{ $campaign->end_date->format('M d, Y') }} @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Creative Pipeline -->
                <div class="lg:col-span-2 space-y-8">
                    <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm">
                        <div class="flex justify-between items-center mb-8">
                            <h4 class="text-2xl font-black text-gray-900 tracking-tight">Creative Production</h4>
                            <button class="text-indigo-600 font-bold text-xs uppercase tracking-widest">Add
                                Asset</button>
                        </div>

                        <div class="space-y-4">
                            @forelse($campaign->creativeRequests as $request)
                                <div
                                    class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100 hover:border-indigo-100 transition-all">
                                    <div class="flex items-center">
                                        <div
                                            class="h-10 w-10 bg-white rounded-xl shadow-sm flex items-center justify-center text-gray-400 mr-4">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900 text-sm">{{ $request->title }}</div>
                                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                                {{ $request->type }}</div>
                                        </div>
                                    </div>
                                    <span
                                        class="bg-white px-3 py-1 rounded-full text-[10px] font-black text-gray-500 shadow-sm border border-gray-100">
                                        {{ strtoupper($request->status) }}
                                    </span>
                                </div>
                            @empty
                                <div class="py-12 text-center bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                                    <p class="text-sm text-gray-400 font-medium">No creative requests for this campaign.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Active Tasks -->
                <div class="space-y-8">
                    <div class="bg-indigo-600 p-8 rounded-3xl text-white shadow-xl shadow-indigo-100">
                        <h4 class="text-xl font-black mb-6 tracking-tight">Execution Roadmap</h4>
                        <div class="space-y-6">
                            @forelse($campaign->tasks->where('status', '!=', 'completed') as $task)
                                <div class="flex items-start">
                                    <div
                                        class="h-5 w-5 rounded bg-indigo-500 border-2 border-indigo-400 mt-1 mr-3 flex-shrink-0">
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold">{{ $task->title }}</div>
                                        <div class="text-[10px] opacity-70 font-bold mt-1">DUE
                                            {{ $task->due_date ? $task->due_date->format('M d') : 'TODAY' }}</div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm opacity-70 font-bold">No pending tasks. Clear for liftoff.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>