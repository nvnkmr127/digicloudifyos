<x-app-container>
    <x-page-header title="Clients">
        <x-button color="primary">
            Add New Client
        </x-button>
    </x-page-header>

    <x-card>
        <div class="flex flex-col sm:flex-row gap-4 mb-6 text-sm">
            <x-input type="search" placeholder="Search clients..." class="max-w-md" />
            <x-select class="max-w-xs">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </x-select>
        </div>

        <x-table>
            <x-slot name="header">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left">Company Name</th>
                    <th scope="col" class="px-6 py-3 text-left">Contact Person</th>
                    <th scope="col" class="px-6 py-3 text-left">Active Projects</th>
                    <th scope="col" class="px-6 py-3 text-left">Total Value</th>
                    <th scope="col" class="px-6 py-3 text-right">Actions</th>
                </tr>
            </x-slot>

            @foreach([1, 2, 3, 4] as $client)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div
                                    class="h-10 w-10 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-primary font-bold">
                                    {{ chr(64 + $client) }}C
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-text-primary">
                                    {{ chr(64 + $client) }}orp Enterprises
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-text-muted">
                        Jane Smith {{ $client }}<br>
                        <span class="text-xs">jane.s{{ $client }}@example.com</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-text-muted">
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ $client + 1 }} Projects
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-text-primary font-medium">
                        ${{ number_format($client * 12500, 0) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="#" class="text-primary hover:text-indigo-900 mr-3">View</a>
                        <button type="button" class="text-red-600 hover:text-red-900">Delete</button>
                    </td>
                </tr>
            @endforeach
        </x-table>
    </x-card>
</x-app-container>