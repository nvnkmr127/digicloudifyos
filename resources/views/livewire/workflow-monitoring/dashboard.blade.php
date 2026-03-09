<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Workflow Monitoring') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">Automation Rules</h3>
                    <p>Track your automated workflows, executed events, and performance logs.</p>

                    <div class="mt-8 border rounded-md p-4 bg-gray-50">
                        <div class="flex justify-between items-center border-b pb-2 mb-4">
                            <span class="font-bold text-gray-700">Recent Executions</span>
                            <span class="text-sm text-green-600 bg-green-100 px-2 py-1 rounded">System Healthy</span>
                        </div>
                        <ul class="space-y-3">
                            <li class="flex items-center text-sm py-2">
                                <span class="bg-blue-100 text-blue-800 p-1 rounded mr-3">Triggered</span>
                                <span class="text-gray-600"><strong>Rule #120</strong> "Alert on Ad Spend &gt; 90%"
                                    executed for Campaign <em>"Summer Sale Ads"</em></span>
                                <span class="ml-auto text-gray-400 text-xs text-right">2 mins ago</span>
                            </li>
                            <li class="flex items-center text-sm py-2">
                                <span class="bg-blue-100 text-blue-800 p-1 rounded mr-3">Triggered</span>
                                <span class="text-gray-600"><strong>Rule #45</strong> "Create task on new Lead" executed
                                    for Lead <em>"John Doe"</em></span>
                                <span class="ml-auto text-gray-400 text-xs text-right">18 mins ago</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>