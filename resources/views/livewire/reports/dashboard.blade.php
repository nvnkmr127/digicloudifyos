<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reports Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">Analytics & Reporting</h3>
                    <p>Select metrics below to generate reports for your clients.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
                        <div class="border rounded-lg p-5">
                            <h4 class="font-bold text-gray-800 border-b pb-2 mb-3">Financial Performance</h4>
                            <p class="text-sm text-gray-600 mb-4">Revenue, outstanding invoices, profit margins.</p>
                            <button class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">Generate
                                PDF</button>
                        </div>
                        <div class="border rounded-lg p-5">
                            <h4 class="font-bold text-gray-800 border-b pb-2 mb-3">Campaign Success</h4>
                            <p class="text-sm text-gray-600 mb-4">Spend vs ROI, CPC, Conversion rates.</p>
                            <button class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">Generate
                                CSV</button>
                        </div>
                        <div class="border rounded-lg p-5">
                            <h4 class="font-bold text-gray-800 border-b pb-2 mb-3">Project Profitability</h4>
                            <p class="text-sm text-gray-600 mb-4">Budget utilization vs overall team costs.</p>
                            <button class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">View
                                Data</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>