<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Creative Requests') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">Creative Dashboard</h3>
                    <p class="mb-4">Here you can manage all creative production requests, track their progress, and
                        upload final assets.</p>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
                        <!-- Example Columns for Kanban -->
                        <div class="bg-gray-100 p-4 rounded-lg min-h-[500px]">
                            <h4 class="font-bold text-gray-700 mb-3 border-b pb-2">Requested</h4>
                            <div class="bg-white p-3 rounded shadow-sm border border-gray-200">
                                <span class="text-xs font-semibold px-2 py-1 bg-blue-100 text-blue-800 rounded">Static
                                    Image</span>
                                <p class="mt-2 text-sm font-medium">Summer Sale Banner</p>
                                <p class="text-xs text-gray-500 mt-1">Due: Oct 15, 2024</p>
                            </div>
                        </div>

                        <div class="bg-yellow-50 p-4 rounded-lg min-h-[500px]">
                            <h4 class="font-bold text-yellow-700 mb-3 border-b border-yellow-200 pb-2">In Progress</h4>
                            <!-- Empty -->
                        </div>

                        <div class="bg-purple-50 p-4 rounded-lg min-h-[500px]">
                            <h4 class="font-bold text-purple-700 mb-3 border-b border-purple-200 pb-2">Review</h4>
                            <!-- Empty -->
                        </div>

                        <div class="bg-green-50 p-4 rounded-lg min-h-[500px]">
                            <h4 class="font-bold text-green-700 mb-3 border-b border-green-200 pb-2">Approved</h4>
                            <!-- Empty -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>