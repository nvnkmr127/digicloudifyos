<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Task Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <!-- Task Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl border border-gray-100">
                <div class="p-8 md:p-12">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                        <div>
                            <div class="flex items-center space-x-3 mb-4">
                                <span
                                    class="bg-indigo-50 text-indigo-700 text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full border border-indigo-100">
                                    {{ $task->campaign->name ?? 'Global Task' }}
                                </span>
                                @php
                                    $priorityColors = [
                                        'high' => 'bg-red-50 text-red-700 border-red-100',
                                        'medium' => 'bg-yellow-50 text-yellow-700 border-yellow-100',
                                        'low' => 'bg-green-50 text-green-700 border-green-100',
                                    ];
                                    $pColor = $priorityColors[$task->priority] ?? 'bg-gray-50 text-gray-700 border-gray-100';
                                @endphp
                                <span
                                    class="{{ $pColor }} text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full border">
                                    {{ $task->priority }} Priority
                                </span>
                            </div>
                            <h3 class="text-3xl font-black text-gray-900 tracking-tight">{{ $task->title }}</h3>
                        </div>
                        <div class="mt-6 md:mt-0 flex items-center space-x-4">
                            @php
                                $statusColors = [
                                    'todo' => 'bg-gray-100 text-gray-600',
                                    'in_progress' => 'bg-blue-100 text-blue-700',
                                    'review' => 'bg-purple-100 text-purple-700',
                                    'completed' => 'bg-green-600 text-white',
                                ];
                                $color = $statusColors[$task->status] ?? 'bg-gray-100 text-gray-600';
                            @endphp
                            <span
                                class="{{ $color }} px-5 py-2 rounded-full text-xs font-black uppercase tracking-widest shadow-sm">
                                {{ str_replace('_', ' ', $task->status) }}
                            </span>
                            <button
                                class="bg-indigo-600 text-white px-6 py-2.5 rounded-2xl text-sm font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition active:scale-95">
                                Mark Complete
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-8">
                    <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm">
                        <h4 class="text-xl font-black text-gray-900 tracking-tight mb-6">Briefing & Requirements</h4>
                        <div class="prose prose-indigo max-w-none text-gray-600 font-medium">
                            {{ $task->description ?? 'No detailed description provided for this task.' }}
                        </div>
                    </div>

                    <!-- Checklist / Subtasks (Placeholder for now) -->
                    <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm">
                        <h4 class="text-xl font-black text-gray-900 tracking-tight mb-6">Sub-tasks</h4>
                        <div class="space-y-4">
                            <div class="flex items-center p-4 bg-gray-50 rounded-2xl border border-gray-100 opacity-50">
                                <span class="text-sm font-bold text-gray-400 italic">No sub-tasks defined.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Info -->
                <div class="space-y-8">
                    <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm">
                        <h4 class="text-lg font-black text-gray-900 tracking-tight mb-6">Task Logistics</h4>
                        <div class="space-y-6">
                            <div>
                                <label
                                    class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 shadow-sm block">Assignee</label>
                                <div class="flex items-center">
                                    <div
                                        class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-[10px] font-black text-indigo-700 mr-2 uppercase">
                                        {{ $task->assignee ? substr($task->assignee->full_name, 0, 1) : '?' }}
                                    </div>
                                    <span
                                        class="text-sm font-bold text-gray-900">{{ $task->assignee->full_name ?? 'Unassigned' }}</span>
                                </div>
                            </div>
                            <div>
                                <label
                                    class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 block">Due
                                    Date</label>
                                <div
                                    class="text-sm font-bold {{ $task->due_date && $task->due_date->isPast() ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ $task->due_date ? $task->due_date->format('M d, Y \a\t g:i A') : 'No deadline' }}
                                </div>
                            </div>
                            <div>
                                <label
                                    class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 block">Created
                                    by</label>
                                <div class="text-sm font-medium text-gray-500">
                                    {{ $task->creator->full_name ?? 'System' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>