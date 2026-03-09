<x-app-container>
    <div class="mb-4">
        <a href="{{ route('users.index') }}" wire:navigate class="text-sm text-text-muted hover:text-primary">
            &larr; Back to Users
        </a>
    </div>

    <x-page-header title="Edit User" />

    <x-card>
        <form wire:submit="update" class="space-y-6 max-w-2xl">
            <div>
                <label for="name" class="block text-sm font-medium text-text-primary">Name</label>
                <x-input id="name" type="text" value="User Name" />
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-text-primary">Email Address</label>
                <x-input id="email" type="email" value="user@example.com" />
            </div>

            <div>
                <label for="role" class="block text-sm font-medium text-text-primary">Role</label>
                <x-select id="role">
                    <option value="admin" selected>Administrator</option>
                    <option value="user">Standard User</option>
                </x-select>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <x-button color="outline" href="{{ route('users.index') }}" wire:navigate>Cancel</x-button>
                <x-button color="primary" type="submit">Update User</x-button>
            </div>
        </form>
    </x-card>
</x-app-container>