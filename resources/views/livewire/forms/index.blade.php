<x-app-container>
    <x-page-header title="Forms & Surveys">
        <x-button color="outline" class="mr-2">Embed Codes</x-button>
        <x-button color="primary">Builder</x-button>
    </x-page-header>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        <x-card class="hover:border-primary transition duration-150 cursor-pointer pt-6">
            <div class="flex justify-between items-start mb-4">
                <div class="bg-indigo-100 text-indigo-700 w-10 h-10 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                        </path>
                    </svg>
                </div>
                <span class="bg-green-100 text-green-800 text-xs px-2 py-0.5 rounded-full font-bold">Active</span>
            </div>
            <h3 class="text-lg font-bold text-text-primary mb-1">Onboarding Questionnaire</h3>
            <p class="text-xs text-text-muted mb-4 border-b border-gray-100 pb-4">Standard form sent to clients upon
                signing contract.</p>
            <div class="flex justify-between items-center mt-4">
                <div class="text-xs text-gray-500">
                    <span class="block font-bold text-text-primary text-sm">342</span>
                    Submissions
                </div>
                <div class="text-xs text-gray-500">
                    <span class="block font-bold text-text-primary text-sm">82%</span>
                    Completion
                </div>
            </div>
        </x-card>

        <x-card
            class="hover:border-primary transition duration-150 cursor-pointer pt-6 border-dashed border-2 bg-gray-50 flex flex-col items-center justify-center text-center">
            <div
                class="bg-white border shadow-sm text-gray-400 w-12 h-12 rounded-full flex items-center justify-center mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
            </div>
            <h3 class="font-bold text-text-primary">Create New Form</h3>
            <p class="text-xs text-text-muted mt-1">Start from scratch or a template.</p>
        </x-card>

    </div>
</x-app-container>