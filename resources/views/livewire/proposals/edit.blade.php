<x-app-container>
    <div class="mb-4">
        <a href="{{ route('proposals.show', $proposal) }}" wire:navigate class="text-sm text-text-muted hover:text-primary">
            &larr; Back to Proposal
        </a>
    </div>

    <x-page-header title="Edit Proposal" />

    <x-card>
        <form wire:submit="save" class="space-y-6">
            <div>
                <x-input-label>Title</x-input-label>
                <x-input wire:model="title" class="w-full mt-2" />
                <x-input-error :messages="$errors->get('title')" class="mt-2" />
            </div>

            <div>
                <x-input-label>Description</x-input-label>
                <x-textarea wire:model="description" class="w-full mt-2" rows="5" />
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <x-input-label>Total Amount</x-input-label>
                    <x-input type="number" step="0.01" wire:model="total_amount" class="w-full mt-2" />
                    <x-input-error :messages="$errors->get('total_amount')" class="mt-2" />
                </div>

                <div>
                    <x-input-label>Status</x-input-label>
                    <x-select wire:model="status" class="w-full mt-2">
                        <option value="draft">Draft</option>
                        <option value="sent">Sent</option>
                        <option value="accepted">Accepted</option>
                        <option value="declined">Declined</option>
                        <option value="expired">Expired</option>
                    </x-select>
                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                </div>

                <div>
                    <x-input-label>Valid Until</x-input-label>
                    <x-input type="date" wire:model="valid_until" class="w-full mt-2" />
                    <x-input-error :messages="$errors->get('valid_until')" class="mt-2" />
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <x-button variant="outline" href="{{ route('proposals.show', $proposal) }}" wire:navigate>
                    Cancel
                </x-button>
                <x-button type="submit">
                    Save
                </x-button>
            </div>
        </form>
    </x-card>
</x-app-container>

