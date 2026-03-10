<x-app-container>
    <x-page-header title="Users">
        <x-button color="primary" href="{{ route('users.create') }}" wire:navigate>
            Create User
        </x-button>
    </x-page-header>

    <x-card>
        <!-- Search -->
        <div class="mb-6 max-w-md">
            <x-input wire:model.live="search" type="text" placeholder="Search by name or email..." />
        </div>

        <x-table>
            <x-slot name="header">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left">Name</th>
                    <th scope="col" class="px-6 py-3 text-left">Email</th>
                    <th scope="col" class="px-6 py-3 text-left">Role</th>
                    <th scope="col" class="px-6 py-3 text-left">Status</th>
                    <th scope="col" class="px-6 py-3 text-right">Actions</th>
                </tr>
            </x-slot>

            @forelse($users as $user)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div
                                    class="h-10 w-10 rounded-full bg-indigo-50 flex items-center justify-center text-primary font-bold">
                                    {{ substr($user->full_name, 0, 1) }}
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-text-primary">
                                    {{ $user->full_name }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted">
                        {{ $user->email }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted">
                        <span class="px-2 py-1 bg-gray-100 rounded text-xs">{{ $user->role }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span
                            class="px-2 py-1 {{ $user->status === 'ACTIVE' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} rounded-full text-xs font-semibold">
                            {{ $user->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('users.edit', $user->id) }}" wire:navigate
                            class="text-primary hover:text-indigo-900 mr-3">Edit</a>
                        @if($user->id !== auth()->id())
                            <button type="button" class="text-red-600 hover:text-red-900"
                                x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion-{{ $user->id }}')">
                                Delete
                            </button>

                            <x-modal name="confirm-user-deletion-{{ $user->id }}">
                                <div class="p-6">
                                    <h2 class="text-lg font-medium text-text-primary">
                                        Are you sure you want to delete this user?
                                    </h2>
                                    <p class="mt-1 text-sm text-text-muted">
                                        Once deleted, this action cannot be undone. User access will be immediately revoked.
                                    </p>
                                    <div class="mt-6 flex justify-end gap-3 text-left">
                                        <x-button color="outline" x-on:click="$dispatch('close')">
                                            Cancel
                                        </x-button>
                                        <x-button color="danger" wire:click="delete('{{ $user->id }}')"
                                            x-on:click="$dispatch('close')">
                                            Delete User
                                        </x-button>
                                    </div>
                                </div>
                            </x-modal>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-text-muted">
                        No users found.
                    </td>
                </tr>
            @endforelse
        </x-table>
    </x-card>
</x-app-container>