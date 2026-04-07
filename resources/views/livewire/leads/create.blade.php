<x-app-container>
    <div class="mb-4">
        <a href="{{ route('leads.index') }}" wire:navigate class="text-sm text-text-muted hover:text-primary">
            &larr; Back to Leads
        </a>
    </div>

    <x-page-header title="Create Lead" />

    <x-card>
        <form wire:submit="save" class="space-y-6 max-w-2xl">
            <x-form-field label="Lead Name" name="name" required>
                <x-input id="name" type="text" placeholder="e.g. John Doe" wire:model="name" />
            </x-form-field>

            <div class="grid grid-cols-2 gap-6">
                <x-form-field label="Email" name="email">
                    <x-input id="email" type="email" placeholder="john@example.com" wire:model="email" />
                </x-form-field>
                <x-form-field label="Phone" name="phone">
                    <x-input id="phone" type="text" placeholder="+1234567890" wire:model="phone" />
                </x-form-field>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <x-form-field label="Source" name="source" required>
                    <x-input id="source" type="text" placeholder="Google, Referral, etc." wire:model="source" />
                </x-form-field>
                <x-form-field label="Status" name="status" required>
                    <x-select id="status" wire:model="status">
                        @foreach(\App\Enums\LeadStatus::cases() as $status)
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                        @endforeach
                    </x-select>
                </x-form-field>
            </div>

            <x-form-field label="Assign To" name="assigned_user">
                <x-select id="assigned_user" wire:model="assigned_user">
                    <option value="">Select a user...</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                    @endforeach
                </x-select>
            </x-form-field>

            <x-form-field label="Notes" name="notes">
                <x-textarea id="notes" rows="4" placeholder="Additional details..." wire:model="notes"></x-textarea>
            </x-form-field>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <x-button color="outline" href="{{ route('leads.index') }}" wire:navigate>Cancel</x-button>
                <x-button color="primary" type="submit">Save Lead</x-button>
            </div>
        </form>
    </x-card>
</x-app-container>
