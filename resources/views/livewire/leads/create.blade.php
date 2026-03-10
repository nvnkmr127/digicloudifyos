<x-app-container>
    <div class="mb-4">
        <a href="{{ route('leads.index') }}" wire:navigate class="text-sm text-text-muted hover:text-primary">
            &larr; Back to Leads
        </a>
    </div>

    <x-page-header title="Create Lead" />

    <x-card>
        <form wire:submit="save" class="space-y-6 max-w-2xl">
            <div>
                <label for="name" class="block text-sm font-medium text-text-primary">Lead Name</label>
                <x-input id="name" type="text" placeholder="e.g. John Doe" wire:model="name" />
                @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-text-primary">Email</label>
                    <x-input id="email" type="email" placeholder="john@example.com" wire:model="email" />
                    @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-text-primary">Phone</label>
                    <x-input id="phone" type="text" placeholder="+1234567890" wire:model="phone" />
                    @error('phone') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="source" class="block text-sm font-medium text-text-primary">Source</label>
                    <x-input id="source" type="text" placeholder="Google, Referral, etc." wire:model="source" />
                    @error('source') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-text-primary">Status</label>
                    <select id="status" wire:model="status"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="New">New</option>
                        <option value="Contacted">Contacted</option>
                        <option value="Qualified">Qualified</option>
                        <option value="Lost">Lost</option>
                        <option value="Won">Won</option>
                    </select>
                    @error('status') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label for="assigned_user" class="block text-sm font-medium text-text-primary">Assign To</label>
                <select id="assigned_user" wire:model="assigned_user"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Select a user...</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                    @endforeach
                </select>
                @error('assigned_user') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-text-primary">Notes</label>
                <x-textarea id="notes" rows="4" placeholder="Additional details..." wire:model="notes"></x-textarea>
                @error('notes') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <x-button color="outline" href="{{ route('leads.index') }}" wire:navigate>Cancel</x-button>
                <x-button color="primary" type="submit">Save Lead</x-button>
            </div>
        </form>
    </x-card>
</x-app-container>