<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Clients Directory') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium">Clients Directory</h3>
                        <button class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">Add New
                            Client</button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Client Name</th>
                                    <th scope="col" class="px-6 py-3">Contact Email</th>
                                    <th scope="col" class="px-6 py-3">Status</th>
                                    <th scope="col" class="px-6 py-3">Active Projects</th>
                                    <th scope="col" class="px-6 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900">Acme Corp</td>
                                    <td class="px-6 py-4">contact@acmecorp.com</td>
                                    <td class="px-6 py-4"><span
                                            class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold">Active</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">3</td>
                                    <td class="px-6 py-4 text-blue-600 hover:underline cursor-pointer">View Details</td>
                                </tr>
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900">Global Tech</td>
                                    <td class="px-6 py-4">info@globaltech.inc</td>
                                    <td class="px-6 py-4"><span
                                            class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-semibold">Onboarding</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">1</td>
                                    <td class="px-6 py-4 text-blue-600 hover:underline cursor-pointer">View Details</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>