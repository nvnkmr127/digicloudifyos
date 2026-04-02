<x-app-container>
    <x-page-header title="Workload & Capacity" />

    <x-card>
        <div class="flex items-center justify-between gap-4">
            <div>
                <div class="text-sm font-black text-gray-900">Team Workload</div>
                <div class="text-xs text-gray-500">Allocation vs actual time tracking.</div>
            </div>

            <div class="w-56">
                <x-select wire:model.live="period">
                    <option value="current_week">Current Week</option>
                    <option value="next_week">Next Week</option>
                    <option value="current_month">Current Month</option>
                    <option value="next_month">Next Month</option>
                    <option value="current_quarter">Current Quarter</option>
                </x-select>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-5">
            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                <div class="text-xs text-gray-500">Total Capacity</div>
                <div class="text-xl font-black text-gray-900">{{ $data['capacity_analysis']['total_capacity'] ?? 0 }}</div>
            </div>
            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                <div class="text-xs text-gray-500">Allocated</div>
                <div class="text-xl font-black text-gray-900">{{ $data['capacity_analysis']['total_allocated'] ?? 0 }}</div>
            </div>
            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                <div class="text-xs text-gray-500">Actual</div>
                <div class="text-xl font-black text-gray-900">{{ $data['capacity_analysis']['total_actual'] ?? 0 }}</div>
            </div>
            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                <div class="text-xs text-gray-500">Utilization</div>
                <div class="text-xl font-black text-gray-900">{{ $data['capacity_analysis']['utilization_rate'] ?? 0 }}%</div>
            </div>
            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                <div class="text-xs text-gray-500">Efficiency</div>
                <div class="text-xl font-black text-gray-900">{{ $data['capacity_analysis']['efficiency_rate'] ?? 0 }}%</div>
            </div>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div>
                <div class="text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Employees</div>
                <div class="space-y-2">
                    @foreach(($data['employees'] ?? []) as $e)
                        <div class="p-4 border border-gray-100 rounded-2xl bg-white flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <div class="text-sm font-bold text-gray-900 truncate">{{ $e['name'] }}</div>
                                <div class="text-xs text-gray-500 truncate">{{ $e['department'] }} • {{ $e['position'] }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-black text-gray-900">{{ $e['utilization_rate'] }}%</div>
                                <div class="text-xs text-gray-500">{{ $e['allocated_hours'] }}/{{ $e['available_hours'] }}h</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div>
                <div class="text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Warnings</div>
                <div class="space-y-2">
                    @forelse(($data['overallocation_warnings'] ?? []) as $w)
                        <div class="p-4 border border-red-100 rounded-2xl bg-red-50 flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <div class="text-sm font-bold text-gray-900 truncate">{{ $w['name'] }}</div>
                                <div class="text-xs text-gray-600 truncate">{{ $w['department'] }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-black text-red-700">+{{ $w['overallocation_hours'] }}h</div>
                                <div class="text-xs text-red-700">{{ $w['overallocation_percentage'] }}%</div>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 border border-dashed border-gray-200 rounded-2xl text-sm text-gray-600">
                            No overallocation warnings.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </x-card>
</x-app-container>

