<x-app-container>
    <x-page-header title="Time Tracking">
        <x-button color="outline" class="mr-2">Export Timesheet</x-button>
        <x-button color="primary">Log Time</x-button>
    </x-page-header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <x-card class="bg-white border-l-4 border-l-primary p-4">
            <h4 class="text-sm font-semibold text-text-muted">Total Hours Logged</h4>
            <p class="text-3xl font-bold text-primary mt-2">{{ number_format($totalHours, 2) }}h</p>
        </x-card>
        <x-card class="bg-white border-l-4 border-l-green-500 p-4">
            <h4 class="text-sm font-semibold text-text-muted">Billable Ratio</h4>
            <p class="text-3xl font-bold text-green-600 mt-2">{{ $billableRatio }}%</p>
        </x-card>
        <x-card class="bg-white border-l-4 border-l-purple-500 p-4">
            <h4 class="text-sm font-semibold text-text-muted">Team Tracking Status</h4>
            <p class="text-xl font-bold text-purple-600 mt-2">Active</p>
        </x-card>
    </div>

    <x-card class="p-0">
        <div class="p-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
            <h3 class="font-bold text-text-primary">Recent Entries</h3>
        </div>
        <x-table>
            <x-slot name="header">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Team
                        Member</th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Project
                        / Task</th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">
                        Duration</th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Type
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Date
                    </th>
                </tr>
            </x-slot>
            <x-slot name="body">
                @forelse($timeEntries as $entry)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                @php
                                    $nameParts = explode(' ', $entry->employee->user->full_name ?? 'Unknown');
                                    $initials = substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : '');
                                @endphp
                                <div
                                    class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold mr-3">
                                    {{ strtoupper($initials) }}</div>
                                <span
                                    class="text-sm font-medium text-text-primary">{{ $entry->employee->user->full_name ?? 'Unknown' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted">
                            <span class="font-semibold block">{{ $entry->project->name ?? 'No Project' }}</span>
                            <span class="text-xs">{{ $entry->description }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-text-primary">
                            {{ number_format($entry->hours, 2) }}h</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($entry->billable)
                                <span
                                    class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full font-medium">Billable</span>
                            @else
                                <span
                                    class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full font-medium">Internal</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted">{{ $entry->date->format('M d, Y') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No time entries logged yet.</td>
                    </tr>
                @endforelse
            </x-slot>
        </x-table>
    </x-card>
</x-app-container>