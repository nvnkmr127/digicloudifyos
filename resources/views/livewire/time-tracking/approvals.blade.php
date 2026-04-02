<x-app-container>
    <x-page-header title="Time Entry Approvals" />

    <x-card>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <div class="text-sm font-black text-gray-900">Pending Approval</div>
                <div class="text-xs text-gray-500">Approve time entries before invoicing and utilization reporting.</div>
            </div>

            <div class="flex items-center gap-3">
                <div class="w-56">
                    <x-select wire:model.live="employeeId">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                        @endforeach
                    </x-select>
                </div>
                <x-button color="primary" type="button" wire:click="approveSelected">
                    Approve Selected ({{ count($selected) }})
                </x-button>
            </div>
        </div>

        <div class="mt-6 space-y-2">
            @forelse($entries as $e)
                <div class="p-4 border border-gray-100 rounded-2xl bg-white flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            wire:click="toggleSelect('{{ $e->id }}')"
                            @checked(in_array($e->id, $selected, true)) />
                        <div class="min-w-0">
                            <div class="text-sm font-bold text-gray-900 truncate">
                                {{ $e->employee->full_name ?? ($e->employee->user->full_name ?? 'Employee') }}
                                <span class="text-gray-500 font-semibold">— {{ $e->date->toDateString() }}</span>
                            </div>
                            <div class="text-xs text-gray-500 truncate">
                                {{ $e->project->name ?? 'No project' }}
                                @if($e->task)
                                    • Task: {{ $e->task->title }}
                                @endif
                                @if($e->description)
                                    • {{ $e->description }}
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-black text-gray-900">{{ $e->hours }}h</div>
                        <div class="text-xs text-gray-500">{{ $e->billable ? 'Billable' : 'Non-billable' }}</div>
                    </div>
                </div>
            @empty
                <div class="p-6 border border-dashed border-gray-200 rounded-2xl text-sm text-gray-600">
                    No pending approvals.
                </div>
            @endforelse
        </div>
    </x-card>
</x-app-container>

