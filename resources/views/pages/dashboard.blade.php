<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-gray-600 mt-1">Welcome back, {{ Auth::user()->full_name }}</p>
        </div>

        @livewire('dashboard.stats')

        <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">Recent Campaigns</h2>
                <p class="text-gray-500 text-sm">Recent campaigns will appear here...</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">Recent Tasks</h2>
                <p class="text-gray-500 text-sm">Recent tasks will appear here...</p>
            </div>
        </div>
    </div>
</x-app-layout>
