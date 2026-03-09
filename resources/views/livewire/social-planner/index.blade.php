<x-app-container>
    <x-page-header title="Social Planner">
        <x-button color="outline" class="mr-2">Connect Accounts</x-button>
        <x-button color="primary">New Post</x-button>
    </x-page-header>

    <div class="flex justify-between items-center mb-6">
        <div class="flex space-x-2">
            <span class="bg-blue-100 text-blue-800 rounded-full px-3 py-1 text-sm font-bold flex items-center">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                </svg>
                Connected
            </span>
            <span class="bg-pink-100 text-pink-800 rounded-full px-3 py-1 text-sm font-bold flex items-center">
                Instagram Connected
            </span>
            <span
                class="bg-blue-50 text-blue-400 rounded-full border border-blue-200 px-3 py-1 text-sm font-bold flex items-center">
                + LinkedIn
            </span>
        </div>
        <div class="flex space-x-2 text-sm bg-gray-100 p-1 rounded">
            <button class="bg-white shadow-sm px-3 py-1.5 rounded text-text-primary font-medium">Calendar</button>
            <button class="px-3 py-1.5 rounded text-text-muted hover:text-text-primary font-medium">List View</button>
        </div>
    </div>

    <!-- Calendar View -->
    <x-card class="p-0 overflow-hidden">
        <div class="border-b border-gray-200 bg-gray-50 p-4 flex justify-between items-center">
            <h3 class="font-bold text-lg text-text-primary">October 2026</h3>
            <div class="flex space-x-2">
                <button class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                        </path>
                    </svg></button>
                <button class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg></button>
            </div>
        </div>
        <div
            class="grid grid-cols-7 border-b border-gray-200 bg-gray-50 text-center text-xs font-semibold text-gray-500 uppercase">
            <div class="py-2 border-r border-gray-200">Sun</div>
            <div class="py-2 border-r border-gray-200">Mon</div>
            <div class="py-2 border-r border-gray-200">Tue</div>
            <div class="py-2 border-r border-gray-200">Wed</div>
            <div class="py-2 border-r border-gray-200">Thu</div>
            <div class="py-2 border-r border-gray-200">Fri</div>
            <div class="py-2">Sat</div>
        </div>

        <!-- Grid Cells (Truncated for example) -->
        <div class="grid grid-cols-7 bg-gray-200 gap-px">
            <!-- Row 1 -->
            <div class="bg-white min-h-[120px] p-2 hover:bg-gray-50 transition"><span
                    class="text-gray-400 text-xs">27</span></div>
            <div class="bg-white min-h-[120px] p-2 hover:bg-gray-50 transition"><span
                    class="text-gray-400 text-xs">28</span></div>
            <div class="bg-white min-h-[120px] p-2 hover:bg-gray-50 transition"><span
                    class="text-gray-400 text-xs">29</span></div>
            <div class="bg-white min-h-[120px] p-2 hover:bg-gray-50 transition"><span
                    class="text-gray-400 text-xs">30</span></div>
            <div class="bg-white min-h-[120px] p-2 hover:bg-gray-50 transition">
                <span class="text-text-primary text-xs font-bold">1</span>
                <div
                    class="mt-2 bg-blue-100 border-l-2 border-blue-500 text-[10px] p-1 rounded text-blue-800 shadow-sm truncate">
                    10:00 AM FB Update
                </div>
            </div>
            <div class="bg-white min-h-[120px] p-2 hover:bg-gray-50 transition">
                <span class="text-text-primary text-xs font-bold">2</span>
                <div
                    class="mt-2 bg-pink-100 border-l-2 border-pink-500 text-[10px] p-1 rounded text-pink-800 shadow-sm truncate">
                    3:00 PM IG Layout
                </div>
            </div>
            <div class="bg-white min-h-[120px] p-2 hover:bg-gray-50 transition"><span
                    class="text-text-primary text-xs font-bold">3</span></div>
        </div>
    </x-card>
</x-app-container>