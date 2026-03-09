<x-app-container>
    <x-page-header title="Users">
        <x-button color="primary" href="{{ route('users.create') }}" wire:navigate>
            Create User
        </x-button>
    </x-page-header>

    <x-card>
        <x-table>
            <x-slot name="header">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left">Name</th>
                    <th scope="col" class="px-6 py-3 text-left">Email</th>
                    <th scope="col" class="px-6 py-3 text-left">Role</th>
                    <th scope="col" class="px-6 py-3 text-right">Actions</th>
                </tr>
            </x-slot>

            @foreach([1, 2, 3] as $user)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div
                                    class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                                    U{{ $user }}
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-text-primary">
                                    User Name {{ $user }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-text-muted">
                        user{{ $user }}@example.com
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-text-muted">
                        Admin
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('users.edit', $user) }}" wire:navigate
                            class="text-primary hover:text-indigo-900 mr-3">Edit</a>
                        <button type="button" class="text-red-600 hover:text-red-900" x-data=""
                            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion-{{ $user }}')">
                            Delete
                        </button>

                        <x-modal name="confirm-user-deletion-{{ $user }}">
                            <div class="p-6">
                                <h2 class="text-lg font-medium text-text-primary">
                                    Are you sure you want to delete this user?
                                </h2>
                                <p class="mt-1 text-sm text-text-muted">
                                    Once deleted, this action cannot be undone.
                                </p>
                                <div class="mt-6 flex justify-end gap-3 text-left">
                                    <x-button color="outline" x-on:click="$dispatch('close')">
                                        Cancel
                                    </x-button>
                                    <x-button color="danger" x-on:click="$dispatch('close')">
                                        Delete Account
                                    </x-button>
                                </div>
                            </div>
                        </x-modal>
                    </td>
                </tr>
            @endforeach
        </x-table>
    </x-card>
</x-app-container>