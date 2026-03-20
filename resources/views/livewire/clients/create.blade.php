<x-app-container>
    <div class="mb-4">
        <a href="{{ route('clients.index') }}" wire:navigate class="text-sm text-text-muted hover:text-primary">
            &larr; Back to Clients
        </a>
    </div>

    <x-page-header title="Create Client" />

    <x-card>
        <form wire:submit="save" class="space-y-6 max-w-2xl">
            <x-form-field label="Client Name" name="name">
                <x-input id="name" type="text" placeholder="e.g. Acme Corp" wire:model="name" />
            </x-form-field>

            <x-form-field label="Email Address" name="email">
                <x-input id="email" type="email" placeholder="client@example.com" wire:model="email" />
            </x-form-field>

            <div class="grid grid-cols-2 gap-6">
                <x-form-field label="Industry" name="industry">
                    <x-input id="industry" type="text" placeholder="e.g. Technology" wire:model="industry" />
                </x-form-field>
                <x-form-field label="External ID" name="external_ref">
                    <x-input id="external_ref" type="text" placeholder="CRM-12345" wire:model="external_ref" />
                </x-form-field>
            </div>

            <x-form-field label="Status" name="status">
                <x-select id="status" wire:model="status">
                    <option value="ACTIVE">Active</option>
                    <option value="INACTIVE">Inactive</option>
                    <option value="ARCHIVED">Archived</option>
                </x-select>
            </x-form-field>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <x-button color="outline" href="{{ route('clients.index') }}" wire:navigate>Cancel</x-button>
                <x-button color="primary" type="submit">Save Client</x-button>
            </div>
        </form>
    </x-card>
</x-app-container>