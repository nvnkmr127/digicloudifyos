<x-app-container>
    <div class="max-w-3xl mx-auto py-12">
        <div class="mb-8">
            <a href="{{ route('clients.index') }}" wire:navigate class="text-sm font-bold text-gray-400 hover:text-primary transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to All Clients
            </a>
        </div>

        <div class="mb-12">
            <h1 class="text-4xl font-black text-gray-900 tracking-tight">Create <span class="text-primary italic">New Client</span></h1>
            <p class="text-gray-500 font-medium mt-2">Start with basic details, and we'll guide you through the full onboarding next.</p>
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-indigo-100/50 border border-indigo-50 p-10">
            <form wire:submit="save" class="space-y-8">
                <x-form-field label="Brand Name" name="name" required>
                    <x-input id="name" type="text" placeholder="e.g. Acme Corporation" wire:model.live="name" />
                </x-form-field>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <x-form-field label="Contact Email" name="email">
                        <x-input id="email" type="email" placeholder="client@example.com" wire:model="email" />
                    </x-form-field>
                    <x-form-field label="Corporate Website" name="website_url">
                        <x-input id="website_url" type="url" placeholder="https://example.com" wire:model="website_url" />
                    </x-form-field>
                </div>

                <x-form-field label="Initial Status" name="status" required>
                    <x-select id="status" wire:model="status">
                        <option value="ACTIVE">Active (Live in System)</option>
                        <option value="INACTIVE">Inactive (Pending Setup)</option>
                        <option value="ARCHIVED">Archived</option>
                    </x-select>
                </x-form-field>

                <div class="flex justify-end gap-4 pt-8 border-t border-gray-50">
                    <x-button color="outline" href="{{ route('clients.index') }}" wire:navigate class="px-8">Cancel</x-button>
                    <x-button color="primary" type="submit" class="px-10 py-4 shadow-xl shadow-primary/25">Initialize Onboarding &rarr;</x-button>
                </div>
            </form>
        </div>
    </div>
</x-app-container>
