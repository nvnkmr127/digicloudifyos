<x-app-container>
    <div class="mb-4">
        <a href="{{ route('projects.index') }}" wire:navigate class="text-sm text-text-muted hover:text-primary">
            &larr; Back to Projects
        </a>
    </div>

    <x-page-header title="Create Project" />

    <x-card>
        <form wire:submit="save" class="space-y-6 max-w-4xl">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="client_id" class="block text-sm font-medium text-text-primary">Client</label>
                    <select id="client_id" wire:model="client_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Select a client...</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                    @error('client_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="name" class="block text-sm font-medium text-text-primary">Project Name</label>
                    <x-input id="name" type="text" placeholder="e.g. Website Overhaul" wire:model="name" />
                    @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="project_code" class="block text-sm font-medium text-text-primary">Project Code</label>
                    <x-input id="project_code" type="text" placeholder="PRJ-001" wire:model="project_code" />
                    @error('project_code') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-text-primary">Status</label>
                    <select id="status" wire:model="status"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                        <option value="on_hold">On Hold</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    @error('status') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="priority" class="block text-sm font-medium text-text-primary">Priority</label>
                    <select id="priority" wire:model="priority"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                    @error('priority') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-text-primary">Description</label>
                <x-textarea id="description" rows="3" wire:model="description"></x-textarea>
                @error('description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-text-primary">Start Date</label>
                    <x-input id="start_date" type="date" wire:model="start_date" />
                    @error('start_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-text-primary">Deadline</label>
                    <x-input id="end_date" type="date" wire:model="end_date" />
                    @error('end_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="billing_type" class="block text-sm font-medium text-text-primary">Billing Type</label>
                    <select id="billing_type" wire:model.live="billing_type"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="fixed">Fixed Price</option>
                        <option value="hourly">Hourly Rate</option>
                    </select>
                    @error('billing_type') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="budget" class="block text-sm font-medium text-text-primary">Budget / Fixed Fee</label>
                    <x-input id="budget" type="number" step="0.01" wire:model="budget" />
                    @error('budget') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                @if($billing_type === 'hourly')
                    <div>
                        <label for="hourly_rate" class="block text-sm font-medium text-text-primary">Hourly Rate</label>
                        <x-input id="hourly_rate" type="number" step="0.01" wire:model="hourly_rate" />
                        @error('hourly_rate') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                @endif
            </div>

            <div>
                <label for="project_manager_id" class="block text-sm font-medium text-text-primary">Project
                    Manager</label>
                <select id="project_manager_id" wire:model="project_manager_id"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Select a manager...</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                    @endforeach
                </select>
                @error('project_manager_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <x-button color="outline" href="{{ route('projects.index') }}" wire:navigate>Cancel</x-button>
                <x-button color="primary" type="submit">Create Project</x-button>
            </div>
        </form>
    </x-card>
</x-app-container>