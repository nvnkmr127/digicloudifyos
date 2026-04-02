<x-app-container>
    <x-page-header title="Automations & Workflows">
        <x-button color="primary">Create Workflow</x-button>
    </x-page-header>

    <div class="mb-4 flex space-x-4 border-b border-gray-200">
        <button class="pb-2 border-b-2 border-primary font-medium text-primary text-sm">Active ({{ $rules->where('is_active', true)->count() }})</button>
        <button
            class="pb-2 border-b-2 border-transparent hover:border-gray-300 font-medium text-text-muted text-sm">Drafts
            ({{ $rules->where('is_active', false)->count() }})</button>
        <button
            class="pb-2 border-b-2 border-transparent hover:border-gray-300 font-medium text-text-muted text-sm">Archived</button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        @forelse($rules as $rule)
            <x-card class="hover:border-primary transition duration-150 border-l-4 {{ $rule->is_active ? 'border-l-green-500' : 'border-l-gray-300' }}">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-text-primary">{{ $rule->name }}</h3>
                        <p class="text-sm text-text-muted mt-1">{{ $rule->description ?: 'No description provided.' }}</p>
                    </div>
                    <div class="flex items-center">
                        <span class="mr-2 text-xs font-medium {{ $rule->is_active ? 'text-green-600' : 'text-gray-400' }}">
                            {{ $rule->is_active ? 'Active' : 'Paused' }}
                        </span>
                        <div class="relative inline-block w-10 align-middle select-none">
                            <input type="checkbox" wire:click="toggleRule('{{ $rule->id }}')" {{ $rule->is_active ? 'checked' : '' }}
                                class="toggle-checkbox absolute block w-5 h-5 rounded-full bg-white border-4 appearance-none cursor-pointer {{ $rule->is_active ? 'border-green-500 right-0' : 'border-gray-300 left-0' }}" />
                            <label class="toggle-label block overflow-hidden h-5 rounded-full {{ $rule->is_active ? 'bg-green-500' : 'bg-gray-300' }} cursor-pointer"></label>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-md p-3 mb-4 space-y-2">
                    <div class="flex items-center text-sm text-gray-700">
                        <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <strong>Trigger:</strong> {{ str_replace('_', ' ', ucfirst($rule->event_type)) }}
                    </div>
                    
                    @foreach($rule->actions as $action)
                        <div class="flex items-center text-sm text-gray-700 ml-4">
                            <svg class="w-3 h-3 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7"></path>
                            </svg>
                            <strong>Action:</strong> {{ str_replace('_', ' ', ucfirst($action->action_type)) }}
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-between items-center text-xs text-gray-500">
                    <span>Last Active: {{ $rule->updated_at->diffForHumans() }}</span>
                    <div class="flex space-x-2">
                        <x-button href="{{ route('automations.edit', $rule->id) }}" color="outline" size="xs">Edit</x-button>
                        <x-button color="outline" size="xs">Logs</x-button>
                    </div>
                </div>
            </x-card>
        @empty
            <div class="lg:col-span-2 text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No workflows found</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new workflow sequence.</p>
                <div class="mt-6">
                    <a href="{{ route('automations.create') }}">
                        <x-button color="primary">Create Workflow</x-button>
                    </a>
                </div>
            </div>
        @endforelse

    </div>

    </div>
</x-app-container>
