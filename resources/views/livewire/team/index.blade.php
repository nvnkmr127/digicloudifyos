<x-app-container>
    <x-page-header title="Team & Resources">
        <x-button color="outline" class="mr-2">Workload Analysis</x-button>
        <x-button color="primary">Add Employee</x-button>
    </x-page-header>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <x-card class="bg-indigo-50 border-indigo-100 p-4">
            <h4 class="text-sm font-semibold text-text-muted">Total Employees</h4>
            <p class="text-3xl font-bold text-primary mt-2">24</p>
        </x-card>
        <x-card class="bg-green-50 border-green-100 p-4">
            <h4 class="text-sm font-semibold text-text-muted">Billable Ratio</h4>
            <p class="text-3xl font-bold text-green-600 mt-2">76.5%</p>
        </x-card>
        <x-card class="bg-blue-50 border-blue-100 p-4">
            <h4 class="text-sm font-semibold text-text-muted">Avg Utilization</h4>
            <p class="text-3xl font-bold text-blue-600 mt-2">82%</p>
        </x-card>
        <x-card class="bg-red-50 border-red-100 p-4">
            <h4 class="text-sm font-semibold text-text-muted">Overallocated</h4>
            <p class="text-3xl font-bold text-red-600 mt-2">2</p>
        </x-card>
    </div>

    <x-card>
        <h3 class="text-lg font-bold text-text-primary mb-4">Team Members</h3>
        <x-table>
            <x-slot name="header">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left">Employee Name</th>
                    <th scope="col" class="px-6 py-3 text-left">Role & Dept</th>
                    <th scope="col" class="px-6 py-3 text-left">Capacity</th>
                    <th scope="col" class="px-6 py-3 text-left">Performance</th>
                    <th scope="col" class="px-6 py-3 text-right">Actions</th>
                </tr>
            </x-slot>

            @foreach([['Sales', 'Manager'], ['Creative', 'Designer'], ['Tech', 'Developer']] as $idx => $role)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <img class="w-10 h-10 rounded-full" src="https://ui-avatars.com/api/?name=Employee_{{$idx}}"
                                alt="">
                            <div class="ml-4">
                                <div class="text-sm font-medium text-text-primary">Employee Name {{$idx + 1}}</div>
                                <div class="text-xs text-text-muted">EMP-00{{$idx + 1}}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <p class="text-sm text-text-primary">{{$role[1]}}</p>
                        <p class="text-xs text-text-muted">{{$role[0]}} Dept</p>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $idx == 1 ? 'red' : 'green' }}-100 text-{{ $idx == 1 ? 'red' : 'green' }}-800">
                            {{ $idx == 1 ? 'Overallocated (45h/40h)' : 'Optimal (35h/40h)' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex text-yellow-400 text-sm">
                            ★★★★★
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="#" class="text-primary hover:text-indigo-900 mx-1">Edit</a>
                    </td>
                </tr>
            @endforeach
        </x-table>
    </x-card>
</x-app-container>