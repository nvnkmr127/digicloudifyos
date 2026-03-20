<x-app-container>
    <div class="mb-4">
        <a href="{{ route('tasks.index') }}" wire:navigate class="text-xs font-black text-gray-400 hover:text-indigo-600 uppercase tracking-widest transition-colors flex items-center">
            <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Task Center
        </a>
    </div>

    <x-page-header title="Update Task Directive" />

    <div class="max-w-4xl">
        <x-card class="p-10 rounded-card-premium shadow-xl shadow-indigo-50/50">
            <form wire:submit="update" class="space-y-10">
                <x-form-field label="Task Designation" name="title">
                    <x-input id="title" type="text" placeholder="e.g. Design homepage layout" wire:model="title" class="rounded-xl shadow-inner bg-gray-50/30" />
                </x-form-field>

                <x-form-field label="Technical Brief & Directives" name="description">
                    <x-textarea id="description" rows="5" placeholder="Elaborate on the task parameters..." wire:model="description" class="rounded-[1.5rem]"></x-textarea>
                </x-form-field>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <x-form-field label="Task Classification" name="task_type">
                        <x-select id="task_type" wire:model="task_type" class="rounded-xl">
                            <option value="general">General Operations</option>
                            <option value="creative">Creative Design</option>
                            <option value="ad_account">Ad Management</option>
                            <option value="report">Data Analytics</option>
                        </x-select>
                    </x-form-field>

                    <x-form-field label="Priority Protocol" name="priority">
                        <x-select id="priority" wire:model="priority" class="rounded-xl">
                            <option value="low">Standard</option>
                            <option value="medium">Elevated</option>
                            <option value="high">Critical</option>
                            <option value="urgent">Immediate Action</option>
                        </x-select>
                    </x-form-field>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <x-form-field label="Current Status" name="status">
                        <x-select id="status" wire:model="status" class="rounded-xl">
                            <option value="todo">Pending Queue</option>
                            <option value="in_progress">Active Execution</option>
                            <option value="review">Quality Assurance</option>
                            <option value="completed">Finalized</option>
                        </x-select>
                    </x-form-field>

                    <x-form-field label="Hard Deadline" name="deadline">
                        <x-input id="deadline" type="date" wire:model="deadline" class="rounded-xl" />
                    </x-form-field>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <x-form-field label="Assigned Operator" name="assigned_to">
                        <x-select id="assigned_to" wire:model="assigned_to" class="rounded-xl">
                            <option value="">Unassigned</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                            @endforeach
                        </x-select>
                    </x-form-field>

                    <x-form-field label="Project / Client Mapping" name="client_id">
                        <x-select id="client_id" wire:model="client_id" class="rounded-xl">
                            <option value="">No Client Entity</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </x-select>
                    </x-form-field>
                </div>

                <div class="flex justify-end gap-4 pt-10 border-t border-gray-50">
                    <x-button color="outline" href="{{ route('tasks.index') }}" wire:navigate class="rounded-xl px-8 transition-all hover:bg-gray-50">
                        Discard Changes
                    </x-button>
                    <x-button color="primary" type="submit" class="rounded-xl px-12 shadow-xl shadow-indigo-100 hover:scale-105 active:scale-95 transition-all">
                        Commit Core Update
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-container>