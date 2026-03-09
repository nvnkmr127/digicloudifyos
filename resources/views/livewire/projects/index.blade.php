<x-app-container>
    <x-page-header title="Projects">
        <x-button color="primary">Create Project</x-button>
    </x-page-header>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
        @foreach([1, 2, 3, 4] as $project)
            <x-card class="hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-bold text-text-primary">Website Redesign 2026</h3>
                        <p class="text-sm text-text-muted">Client: Beta Industries</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">On Track</span>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-4 border-t border-b border-gray-100 py-3">
                    <div>
                        <p class="text-xs text-text-muted uppercase tracking-wider">Budget</p>
                        <p class="font-semibold text-text-primary">$25,000</p>
                    </div>
                    <div>
                        <p class="text-xs text-text-muted uppercase tracking-wider">Costs (Actual)</p>
                        <p class="font-semibold text-text-primary">$12,450</p>
                    </div>
                    <div>
                        <p class="text-xs text-text-muted uppercase tracking-wider">Deadline</p>
                        <p class="font-semibold text-text-primary">Oct 12</p>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <div class="flex -space-x-2">
                        <img class="w-8 h-8 rounded-full border-2 border-white" src="https://ui-avatars.com/api/?name=PM"
                            title="Project Manager">
                        <img class="w-8 h-8 rounded-full border-2 border-white" src="https://ui-avatars.com/api/?name=D"
                            title="Designer">
                        <img class="w-8 h-8 rounded-full border-2 border-white" src="https://ui-avatars.com/api/?name=Dev"
                            title="Developer">
                    </div>
                    <x-button color="outline" class="text-xs py-1 px-3">Manage</x-button>
                </div>
            </x-card>
        @endforeach
    </div>
</x-app-container>