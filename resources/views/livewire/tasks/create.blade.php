<x-app-container>
    <div class="mb-4">
        <a href="{{ route('tasks.index') }}" wire:navigate class="text-xs font-black text-gray-400 hover:text-indigo-600 uppercase tracking-widest transition-colors flex items-center">
            <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Task Pipeline
        </a>
    </div>

    <x-page-header title="Issue New Task" />

    <div class="max-w-3xl">
        <x-card class="p-8 rounded-[2rem] shadow-xl shadow-indigo-50/50">
            <form wire:submit="save" class="space-y-8">
                <x-form-field label="Task Objective" name="title">
                    <x-input id="title" type="text" placeholder="e.g. Optimize conversion funnel for Q3" wire:model="title" class="rounded-xl" />
                </x-form-field>

                <x-form-field label="Execution Directives" name="description">
                    <x-textarea id="description" rows="4" placeholder="Detailed instructions and context..." wire:model="description" class="rounded-2xl"></x-textarea>
                </x-form-field>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <x-form-field label="Specialization" name="task_type">
                        <x-select id="task_type" wire:model="task_type" class="rounded-xl">
                            <option value="general">General Execution</option>
                            <option value="creative">Creative Production</option>
                            <option value="ad_account">Infrastructure / Ad Account</option>
                            <option value="report">Analysis & Reporting</option>
                        </x-select>
                    </x-form-field>

                    <x-form-field label="Priority Level" name="priority">
                        <x-select id="priority" wire:model="priority" class="rounded-xl">
                            <option value="low">Standard</option>
                            <option value="medium">Elevated</option>
                            <option value="high">Critical</option>
                            <option value="urgent">Immediate Action</option>
                        </x-select>
                    </x-form-field>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <x-form-field label="Workflow State" name="status">
                        <x-select id="status" wire:model="status" class="rounded-xl">
                            <option value="todo">Pending Allocation</option>
                            <option value="in_progress">Active Execution</option>
                            <option value="review">Quality Assurance</option>
                            <option value="completed">Finalized</option>
                        </x-select>
                    </x-form-field>

                    <x-form-field label="Deadline Protocol" name="deadline">
                        <x-input id="deadline" type="date" wire:model="deadline" class="rounded-xl" />
                    </x-form-field>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <x-form-field label="Assigned Operative" name="assigned_to">
                        <x-select id="assigned_to" wire:model="assigned_to" class="rounded-xl">
                            <option value="">Unassigned</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                            @endforeach
                        </x-select>
                    </x-form-field>

                    <x-form-field label="Related Client Entity" name="client_id">
                        <x-select id="client_id" wire:model="client_id" class="rounded-xl">
                            <option value="">No Specific Entity</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </x-select>
                    </x-form-field>
                </div>

                <div class="flex justify-end gap-4 pt-8 border-t border-gray-50">
                    <x-button color="outline" href="{{ route('tasks.index') }}" wire:navigate class="rounded-xl px-8">
                        Discard
                    </x-button>
                    <x-button color="primary" type="submit" class="rounded-xl px-8 shadow-lg shadow-indigo-100">
                        Commit Task
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-container>