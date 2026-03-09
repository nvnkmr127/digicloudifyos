<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Campaign Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-2xl font-bold">Campaign: Summer Sale 2024</h3>
                        <div>
                            <span
                                class="bg-green-100 text-green-800 px-3 py-1 rounded font-semibold mr-2">Running</span>
                            <button
                                class="bg-gray-200 text-gray-800 px-4 py-2 rounded text-sm hover:bg-gray-300">Edit</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h4 class="font-semibold text-gray-700 border-b pb-2 mb-4">Overview</h4>
                            <ul class="space-y-3">
                                <li><strong>Client:</strong> Acme Corp</li>
                                <li><strong>Ad Account:</strong> Acme Facebook Ads</li>
                                <li><strong>Start Date:</strong> Jun 01, 2024</li>
                                <li><strong>End Date:</strong> Aug 31, 2024</li>
                                <li><strong>Budget:</strong> $15,000.00</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-700 border-b pb-2 mb-4">Performance Metrics</h4>
                            <ul class="space-y-3">
                                <li class="flex justify-between"><span>Spend:</span> <strong>$4,500.00</strong></li>
                                <li class="flex justify-between"><span>Impressions:</span> <strong>124,500</strong></li>
                                <li class="flex justify-between"><span>Clicks:</span> <strong>3,400</strong></li>
                                <li class="flex justify-between"><span>Conversions:</span> <strong>45</strong></li>
                                <li class="flex justify-between"><span>CPA:</span> <strong>$100.00</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>