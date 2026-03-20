<x-app-container>
    <div class="mb-4">
        <a href="{{ route('projects.index') }}" wire:navigate class="text-xs font-black text-gray-400 hover:text-indigo-600 uppercase tracking-widest transition-colors flex items-center">
            <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Project Matrix
        </a>
    </div>

    <x-page-header title="Initiate New Strategic Project" />

    <div class="max-w-4xl">
        <x-card class="p-10 rounded-card-premium shadow-xl shadow-indigo-50/50">
            <form wire:submit="save" class="space-y-10">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <x-form-field label="Target Client Entity" name="client_id">
                        <x-select id="client_id" wire:model="client_id" class="rounded-xl">
                            <option value="">Select entity...</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </x-select>
                    </x-form-field>

                    <x-form-field label="Project Designation" name="name">
                        <x-input id="name" type="text" placeholder="e.g. Q4 Growth Acceleration" wire:model="name" class="rounded-xl" />
                    </x-form-field>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                    <x-form-field label="Internal Project Code" name="project_code">
                        <x-input id="project_code" type="text" placeholder="STRAT-001" wire:model="project_code" class="rounded-xl uppercase shadow-inner" />
                    </x-form-field>

                    <x-form-field label="Operational Status" name="status">
                        <x-select id="status" wire:model="status" class="rounded-xl">
                            <option value="active">Active Execution</option>
                            <option value="completed">Finalized</option>
                            <option value="on_hold">Deferred</option>
                            <option value="cancelled">Terminated</option>
                        </x-select>
                    </x-form-field>

                    <x-form-field label="Priority Protocol" name="priority">
                        <x-select id="priority" wire:model="priority" class="rounded-xl">
                            <option value="low">Standard</option>
                            <option value="medium">Elevated</option>
                            <option value="high">Critical</option>
                            <option value="urgent">Immediate</option>
                        </x-select>
                    </x-form-field>
                </div>

                <x-form-field label="Strategic Directives & Scope" name="description">
                    <x-textarea id="description" rows="4" placeholder="Define the project's parameters and expected outcomes..." wire:model="description" class="rounded-[1.5rem]"></x-textarea>
                </x-form-field>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <x-form-field label="Commencement Date" name="start_date">
                        <x-input id="start_date" type="date" wire:model="start_date" class="rounded-xl" />
                    </x-form-field>

                    <x-form-field label="Hard Deadline" name="end_date">
                        <x-input id="end_date" type="date" wire:model="end_date" class="rounded-xl" />
                    </x-form-field>
                </div>

                <div class="p-8 bg-gray-50/50 rounded-[2rem] border border-gray-100/50">
                    <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-8">Financial Configuration</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-10 items-end">
                        <x-form-field label="Billing Model" name="billing_type">
                            <x-select id="billing_type" wire:model.live="billing_type" class="rounded-xl bg-white">
                                <option value="fixed">Fixed Strategic Fee</option>
                                <option value="hourly">Variable Hourly Rate</option>
                            </x-select>
                        </x-form-field>

                        <x-form-field label="Projected Capital / Fee" name="budget">
                            <div class="relative group">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold">$</span>
                                <x-input id="budget" type="number" step="0.01" wire:model="budget" class="pl-8 rounded-xl bg-white" />
                            </div>
                        </x-form-field>

                        @if($billing_type === 'hourly')
                            <x-form-field label="Operative Hourly Rate" name="hourly_rate">
                                <div class="relative group">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold">$</span>
                                    <x-input id="hourly_rate" type="number" step="0.01" wire:model="hourly_rate" class="pl-8 rounded-xl bg-white" />
                                </div>
                            </x-form-field>
                        @endif
                    </div>
                </div>

                <x-form-field label="Lead Project Strategist" name="project_manager_id">
                    <x-select id="project_manager_id" wire:model="project_manager_id" class="rounded-xl">
                        <option value="">Select strategist...</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                        @endforeach
                    </x-select>
                </x-form-field>

                <div class="flex justify-end gap-4 pt-10 border-t border-gray-50">
                    <x-button color="outline" href="{{ route('projects.index') }}" wire:navigate class="rounded-xl px-8 transition-all hover:bg-gray-50">
                        Discard
                    </x-button>
                    <x-button color="primary" type="submit" class="rounded-xl px-12 shadow-xl shadow-indigo-100 hover:scale-105 active:scale-95 transition-all">
                        Commit Project
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-container>