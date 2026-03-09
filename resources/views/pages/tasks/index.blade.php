<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Tasks</h1>
            <p class="text-gray-600 mt-1">Manage your team tasks</p>
        </div>

        @livewire('tasks.kanban-board')
    </div>
</x-app-layout>
