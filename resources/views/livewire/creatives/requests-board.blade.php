<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Creative Requests') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold">Creative Pipeline</h3>
                        <button
                            class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                            New Request
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        @foreach($statusGroups as $key => $group)
                            <div class="bg-gray-50 rounded-xl p-4 min-h-[600px] border border-gray-100">
                                <div class="flex items-center justify-between mb-4 border-b pb-2">
                                    <h4 class="font-bold {{ $group['text'] }}">{{ $group['title'] }}</h4>
                                    <span class="bg-white px-2 py-0.5 rounded-full text-xs font-bold shadow-sm">
                                        {{ count($requests[$key] ?? []) }}
                                    </span>
                                </div>

                                <div class="space-y-4">
                                    @forelse($requests[$key] ?? [] as $request)
                                        <div
                                            class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition cursor-pointer group">
                                            <div class="flex justify-between items-start mb-2">
                                                <span
                                                    class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">
                                                    {{ $request['type'] }}
                                                </span>
                                                <span
                                                    class="text-[10px] font-bold {{ $request['priority'] === 'high' ? 'text-red-600' : 'text-gray-400' }}">
                                                    {{ strtoupper($request['priority']) }}
                                                </span>
                                            </div>

                                            <h5
                                                class="font-bold text-sm text-gray-800 group-hover:text-indigo-600 transition truncate">
                                                {{ $request['title'] }}
                                            </h5>

                                            <p class="text-xs text-gray-500 mt-2 line-clamp-2">
                                                {{ $request['description'] }}
                                            </p>

                                            <div class="mt-4 pt-3 border-t border-gray-50 flex items-center justify-between">
                                                <div class="flex -space-x-2">
                                                    @if($request['assignee'])
                                                        <div class="h-6 w-6 rounded-full bg-indigo-100 flex items-center justify-center text-[10px] font-bold text-indigo-700 border-2 border-white"
                                                            title="{{ $request['assignee']['full_name'] }}">
                                                            {{ substr($request['assignee']['full_name'], 0, 1) }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="text-[10px] text-gray-400 font-medium">
                                                    {{ $request['deadline'] ? \Carbon\Carbon::parse($request['deadline'])->format('M d') : 'No date' }}
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="py-8 text-center border-2 border-dashed border-gray-200 rounded-xl">
                                            <span class="text-xs text-gray-400 font-medium">No requests</span>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>