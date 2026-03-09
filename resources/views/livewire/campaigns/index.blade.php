<x-app-container>
    <x-page-header title="Marketing Campaigns">
        <x-button color="outline" class="mr-2">Import</x-button>
        <x-button color="primary">Create Campaign</x-button>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Dashboard Kanban Style Mockup -->
        <x-card class="bg-gray-50 border border-gray-200">
            <h3 class="font-bold text-gray-700 mb-4 pb-2 border-b">Planning (2)</h3>
            <div class="space-y-4">
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                    <div class="flex justify-between items-start mb-2">
                        <span class="px-2 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Social
                            Media</span>
                        <div class="flex -space-x-1">
                            <img class="w-6 h-6 rounded-full border border-white"
                                src="https://ui-avatars.com/api/?name=A" />
                        </div>
                    </div>
                    <h4 class="font-medium text-text-primary mb-1">Q4 Brand Awareness</h4>
                    <p class="text-xs text-text-muted mb-3">Acme Corp</p>
                    <div class="w-full bg-gray-200 rounded-full h-1.5 mb-1">
                        <div class="bg-blue-600 h-1.5 rounded-full" style="width: 15%"></div>
                    </div>
                </div>
            </div>
        </x-card>

        <x-card class="bg-gray-50 border border-gray-200">
            <h3 class="font-bold text-gray-700 mb-4 pb-2 border-b">Active (1)</h3>
            <div class="space-y-4">
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                    <div class="flex justify-between items-start mb-2">
                        <span class="px-2 text-xs font-semibold rounded-full bg-green-100 text-green-800">PPC</span>
                        <div class="flex -space-x-1">
                            <img class="w-6 h-6 rounded-full border border-white"
                                src="https://ui-avatars.com/api/?name=B" />
                        </div>
                    </div>
                    <h4 class="font-medium text-text-primary mb-1">Google Ads Retargeting</h4>
                    <p class="text-xs text-text-muted mb-3">Global Tech</p>
                    <div class="w-full bg-gray-200 rounded-full h-1.5 mb-1">
                        <div class="bg-green-500 h-1.5 rounded-full" style="width: 60%"></div>
                    </div>
                </div>
            </div>
        </x-card>

        <x-card class="bg-gray-50 border border-gray-200">
            <h3 class="font-bold text-gray-700 mb-4 pb-2 border-b">Completed (12)</h3>
            <div class="space-y-4">
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 opacity-75">
                    <div class="flex justify-between items-start mb-2">
                        <span class="px-2 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Email</span>
                    </div>
                    <h4 class="font-medium text-text-primary mb-1">Summer Sale Newsletter</h4>
                    <p class="text-xs text-text-muted mb-3">Fashion Co.</p>
                </div>
            </div>
        </x-card>
    </div>
</x-app-container>