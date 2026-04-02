<x-app-container>
    <x-page-header title="Brand Kit">
        <x-button color="primary" wire:click="save">Save</x-button>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card>
            <div class="text-sm font-black text-gray-900">Identity</div>
            <div class="mt-4 space-y-4">
                <x-form-field label="Logos (URLs or notes, one per line)" name="logos">
                    <x-textarea rows="4" wire:model="logos"></x-textarea>
                </x-form-field>
                <x-form-field label="Brand Colors (hex, one per line)" name="colors">
                    <x-textarea rows="4" wire:model="colors"></x-textarea>
                </x-form-field>
                <x-form-field label="Fonts (one per line)" name="fonts">
                    <x-textarea rows="3" wire:model="fonts"></x-textarea>
                </x-form-field>
            </div>
        </x-card>

        <x-card>
            <div class="text-sm font-black text-gray-900">Voice & Messaging</div>
            <div class="mt-4 space-y-4">
                <x-form-field label="Tone (short description)" name="tone">
                    <x-input type="text" wire:model="tone" />
                </x-form-field>
                <x-form-field label="Do's (one per line)" name="dos">
                    <x-textarea rows="4" wire:model="dos"></x-textarea>
                </x-form-field>
                <x-form-field label="Don'ts (one per line)" name="donts">
                    <x-textarea rows="4" wire:model="donts"></x-textarea>
                </x-form-field>
            </div>
        </x-card>

        <x-card class="lg:col-span-2">
            <div class="text-sm font-black text-gray-900">Claims & Compliance</div>
            <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2">
                <x-form-field label="Approved Claims (one per line)" name="approvedClaims">
                    <x-textarea rows="6" wire:model="approvedClaims"></x-textarea>
                </x-form-field>
                <x-form-field label="Restricted Claims (one per line)" name="restrictedClaims">
                    <x-textarea rows="6" wire:model="restrictedClaims"></x-textarea>
                </x-form-field>
            </div>
        </x-card>
    </div>
</x-app-container>

