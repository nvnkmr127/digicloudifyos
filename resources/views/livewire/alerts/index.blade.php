<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('System Alerts') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-b">
                    <h3 class="text-lg font-medium mb-4">Latest Alerts</h3>
                    <p class="text-gray-500 text-sm mb-4">Notifications, warnings, and system alerts require your
                        attention.</p>

                    <div class="space-y-4 shadow p-4 rounded-lg bg-red-50 border-l-4 border-red-500">
                        <div class="flex items-center">
                            <span class="text-red-600 font-bold mr-2">Critical:</span>
                            <span>Campaign "Black Friday Sale" has exceeded 90% of its budget.</span>
                            <span class="ml-auto text-xs text-gray-500">2 hours ago</span>
                        </div>
                    </div>

                    <div class="mt-4 space-y-4 shadow p-4 rounded-lg bg-yellow-50 border-l-4 border-yellow-500">
                        <div class="flex items-center">
                            <span class="text-yellow-600 font-bold mr-2">Warning:</span>
                            <span>Low CTR detected on ad group "Retargeting Audiences"</span>
                            <span class="ml-auto text-xs text-gray-500">5 hours ago</span>
                        </div>
                    </div>

                    <div class="mt-4 space-y-4 shadow p-4 rounded-lg bg-blue-50 border-l-4 border-blue-500">
                        <div class="flex items-center">
                            <span class="text-blue-600 font-bold mr-2">Info:</span>
                            <span>Weekly sync with Meta Ads successful. Database updated with latest metrics.</span>
                            <span class="ml-auto text-xs text-gray-500">1 day ago</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>