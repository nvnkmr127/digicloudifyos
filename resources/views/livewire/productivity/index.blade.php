<x-app-container>
    <x-page-header title="Productivity Analytics" />

    <x-card>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <div class="text-sm font-black text-gray-900">Daily Summary</div>
                <div class="text-xs text-gray-500">Operational metrics from time tracking, task throughput, and workload allocations.</div>
            </div>
            <div class="w-56">
                <x-input type="date" wire:model.live="date" />
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-4">
            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                <div class="text-xs text-gray-500">Hours Tracked</div>
                <div class="text-xl font-black text-gray-900">{{ number_format($totals['hours_tracked'], 2) }}</div>
            </div>
            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                <div class="text-xs text-gray-500">Billable Ratio</div>
                <div class="text-xl font-black text-gray-900">{{ number_format($totals['billable_ratio'], 1) }}%</div>
            </div>
            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                <div class="text-xs text-gray-500">Tasks Completed</div>
                <div class="text-xl font-black text-gray-900">{{ $totals['tasks_completed'] }}</div>
            </div>
            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                <div class="text-xs text-gray-500">Overdue Tasks</div>
                <div class="text-xl font-black text-gray-900">{{ $totals['overdue_tasks'] }}</div>
            </div>
        </div>

        <div class="mt-8 space-y-2">
            @forelse($rows as $r)
                <div class="p-4 border border-gray-100 rounded-2xl bg-white flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        <div class="text-sm font-bold text-gray-900 truncate">{{ $r->employee->full_name ?? 'Employee' }}</div>
                        <div class="text-xs text-gray-500">
                            {{ number_format((float) $r->hours_tracked, 2) }}h tracked •
                            {{ number_format((float) $r->billable_ratio, 1) }}% billable •
                            {{ number_format((float) $r->utilization_rate, 1) }}% utilization
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-black text-gray-900">{{ (int) $r->tasks_completed }} tasks</div>
                        <div class="text-xs text-gray-500">{{ number_format((float) $r->avg_task_cycle_days, 1) }}d avg cycle</div>
                    </div>
                </div>
            @empty
                <div class="p-6 border border-dashed border-gray-200 rounded-2xl text-sm text-gray-600">
                    No productivity data for this date.
                </div>
            @endforelse
        </div>
    </x-card>
</x-app-container>

