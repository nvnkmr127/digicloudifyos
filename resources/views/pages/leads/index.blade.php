<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Leads</h1>
            <p class="text-gray-600 mt-1">Track your sales pipeline</p>
        </div>

        @livewire('leads.kanban-board')
    </div>
</x-app-layout>
