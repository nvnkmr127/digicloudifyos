<x-app-container>
    <div class="mb-4">
        <a href="{{ route('users.index') }}" wire:navigate class="text-sm text-text-muted hover:text-primary">
            &larr; Back to Users
        </a>
    </div>

    <x-page-header title="Create User" />

    <x-card>
        <form wire:submit="save" class="space-y-6 max-w-2xl">
            <div>
                <label for="full_name" class="block text-sm font-medium text-text-primary">Full Name</label>
                <x-input id="full_name" type="text" placeholder="John Doe" wire:model="full_name" />
                @error('full_name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-text-primary">Email Address</label>
                <x-input id="email" type="email" placeholder="john@example.com" wire:model="email" />
                @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="role" class="block text-sm font-medium text-text-primary">Role</label>
                    <select id="role" wire:model="role"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="ADMIN">Administrator</option>
                        <option value="MANAGER">Manager</option>
                        <option value="VIEWER">Viewer</option>
                    </select>
                    @error('role') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-text-primary">Status</label>
                    <select id="status" wire:model="status"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="ACTIVE">Active</option>
                        <option value="INACTIVE">Inactive</option>
                    </select>
                    @error('status') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-text-primary">Password</label>
                <x-input id="password" type="password" placeholder="At least 8 characters" wire:model="password" />
                @error('password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <x-button color="outline" href="{{ route('users.index') }}" wire:navigate>Cancel</x-button>
                <x-button color="primary" type="submit">Save User</x-button>
            </div>
        </form>
    </x-card>
</x-app-container>