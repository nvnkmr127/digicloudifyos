<x-app-container>
    <div class="mb-4">
        <a href="{{ route('clients.index') }}" wire:navigate class="text-sm text-text-muted hover:text-primary">
            &larr; Back to Clients
        </a>
    </div>

    <x-page-header title="Create Client" />

    <x-card>
        <form wire:submit="save" class="space-y-6 max-w-2xl">
            <div>
                <label for="name" class="block text-sm font-medium text-text-primary">Client Name</label>
                <x-input id="name" type="text" placeholder="e.g. Acme Corp" wire:model="name" />
                @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-text-primary">Email Address</label>
                <x-input id="email" type="email" placeholder="client@example.com" wire:model="email" />
                @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="industry" class="block text-sm font-medium text-text-primary">Industry</label>
                    <x-input id="industry" type="text" placeholder="e.g. Technology" wire:model="industry" />
                    @error('industry') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="external_ref" class="block text-sm font-medium text-text-primary">External ID</label>
                    <x-input id="external_ref" type="text" placeholder="CRM-12345" wire:model="external_ref" />
                    @error('external_ref') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-text-primary">Status</label>
                <select id="status" wire:model="status"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="ACTIVE">Active</option>
                    <option value="INACTIVE">Inactive</option>
                    <option value="ARCHIVED">Archived</option>
                </select>
                @error('status') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <x-button color="outline" href="{{ route('clients.index') }}" wire:navigate>Cancel</x-button>
                <x-button color="primary" type="submit">Save Client</x-button>
            </div>
        </form>
    </x-card>
</x-app-container>