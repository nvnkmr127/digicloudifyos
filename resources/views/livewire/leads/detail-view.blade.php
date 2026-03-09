<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lead Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-b">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-2xl font-bold">Lead: TechGrow Solutions</h3>
                        <div>
                            <span
                                class="bg-blue-100 text-blue-800 px-3 py-1 rounded font-semibold mr-2">Contacted</span>
                            <button class="bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700">Convert
                                to Client</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-6">
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-4 border-b pb-2">Contact Information</h4>
                            <ul class="space-y-3">
                                <li><strong>Name:</strong> Jane Smith</li>
                                <li><strong>Email:</strong> jane.smith@techgrow.com</li>
                                <li><strong>Phone:</strong> (555) 123-4567</li>
                                <li><strong>Source:</strong> LinkedIn Campaign</li>
                                <li><strong>Estimated Value:</strong> $25,000</li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="font-semibold text-gray-700 mb-4 border-b pb-2">Activity History</h4>
                            <div class="space-y-4">
                                <div class="relative pl-6 border-l-2 border-gray-200">
                                    <div class="absolute w-3 h-3 bg-blue-500 rounded-full -left-[7px] top-1"></div>
                                    <p class="text-xs text-gray-500">Yesterday at 2:30 PM</p>
                                    <p class="text-sm">Sent introductory email with our service portfolio.</p>
                                </div>
                                <div class="relative pl-6 border-l-2 border-gray-200">
                                    <div class="absolute w-3 h-3 bg-gray-400 rounded-full -left-[7px] top-1"></div>
                                    <p class="text-xs text-gray-500">Oct 10, 2024</p>
                                    <p class="text-sm">Lead acquired via website contact form.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>