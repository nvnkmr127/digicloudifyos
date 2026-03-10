<x-app-container>
    <x-page-header title="Projects">
        <x-button color="primary" href="{{ route('projects.create') }}" wire:navigate>Create Project</x-button>
    </x-page-header>

    <!-- Search -->
    <div class="mb-6 max-w-md">
        <x-input wire:model.live="search" type="text" placeholder="Search by name or code..." />
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
        @forelse($projects as $project)
            <x-card class="hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-bold text-text-primary">{{ $project->name }}</h3>
                        <p class="text-sm text-text-muted">Client: {{ $project->client->name ?? 'N/A' }}</p>
                    </div>
                    <span
                        class="px-2 py-1 text-xs font-semibold rounded-full 
                            {{ $project->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ ucfirst($project->status) }}
                    </span>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-4 border-t border-b border-gray-100 py-3">
                    <div>
                        <p class="text-xs text-text-muted uppercase tracking-wider">Budget</p>
                        <p class="font-semibold text-text-primary">${{ number_format($project->budget, 0) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-text-muted uppercase tracking-wider">Actual Cost</p>
                        <p class="font-semibold text-text-primary">${{ number_format($project->actual_cost, 0) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-text-muted uppercase tracking-wider">Deadline</p>
                        <p class="font-semibold text-text-primary">
                            {{ $project->end_date ? $project->end_date->format('M d, Y') : 'N/A' }}</p>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        @if($project->projectManager)
                            <div class="flex items-center">
                                <span class="text-xs text-text-muted mr-1">PM:</span>
                                <span class="text-xs font-medium">{{ $project->projectManager->full_name }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('projects.edit', $project->id) }}" wire:navigate
                            class="text-xs text-primary hover:text-indigo-900 border border-primary/20 px-3 py-1 rounded">Edit</a>
                        <button type="button"
                            class="text-xs text-red-600 hover:text-red-900 border border-red-200 px-3 py-1 rounded"
                            x-on:click.prevent="$dispatch('open-modal', 'confirm-project-deletion-{{ $project->id }}')">
                            Delete
                        </button>

                        <x-modal name="confirm-project-deletion-{{ $project->id }}">
                            <div class="p-6">
                                <h2 class="text-lg font-medium text-text-primary">Delete Project</h2>
                                <p class="mt-1 text-sm text-text-muted">Are you sure you want to delete this project? Data
                                    will be soft-deleted.</p>
                                <div class="mt-6 flex justify-end gap-3 text-left">
                                    <x-button color="outline" x-on:click="$dispatch('close')">Cancel</x-button>
                                    <x-button color="danger" wire:click="delete('{{ $project->id }}')"
                                        x-on:click="$dispatch('close')">Delete</x-button>
                                </div>
                            </div>
                        </x-modal>
                    </div>
                </div>
            </x-card>
        @empty
            <div class="xl:col-span-2 py-12 text-center text-text-muted border-2 border-dashed border-gray-200 rounded-xl">
                No projects found. <a href="{{ route('projects.create') }}" class="text-primary font-medium">Create your
                    first project?</a>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $projects->links() }}
    </div>
</x-app-container>