<x-app-container>
    <div class="mb-4">
        <a href="{{ route('tasks.index') }}" wire:navigate class="text-sm text-text-muted hover:text-primary">
            &larr; Back to Tasks
        </a>
    </div>

    <x-page-header title="Edit Task: {{ $task->title }}" />

    <x-card>
        <form wire:submit="update" class="space-y-6 max-w-2xl">
            <div>
                <label for="title" class="block text-sm font-medium text-text-primary">Task Title</label>
                <x-input id="title" type="text" placeholder="e.g. Design homepage layout" wire:model="title" />
                @error('title') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-text-primary">Description</label>
                <x-textarea id="description" rows="4" placeholder="Task details..."
                    wire:model="description"></x-textarea>
                @error('description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="task_type" class="block text-sm font-medium text-text-primary">Task Type</label>
                    <select id="task_type" wire:model="task_type"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="general">General</option>
                        <option value="creative">Creative</option>
                        <option value="ad_account">Ad Account</option>
                        <option value="report">Report</option>
                    </select>
                    @error('task_type') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
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

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="status" class="block text-sm font-medium text-text-primary">Status</label>
                    <select id="status" wire:model="status"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="todo">To Do</option>
                        <option value="in_progress">In Progress</option>
                        <option value="review">Review</option>
                        <option value="completed">Completed</option>
                    </select>
                    @error('status') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="deadline" class="block text-sm font-medium text-text-primary">Deadline</label>
                    <x-input id="deadline" type="date" wire:model="deadline" />
                    @error('deadline') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="assigned_to" class="block text-sm font-medium text-text-primary">Assign To</label>
                    <select id="assigned_to" wire:model="assigned_to"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Unassigned</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                        @endforeach
                    </select>
                    @error('assigned_to') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="client_id" class="block text-sm font-medium text-text-primary">Related Client</label>
                    <select id="client_id" wire:model="client_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">No Client</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                    @error('client_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <x-button color="outline" href="{{ route('tasks.index') }}" wire:navigate>Cancel</x-button>
                <x-button color="primary" type="submit">Update Task</x-button>
            </div>
        </form>
    </x-card>
</x-app-container>