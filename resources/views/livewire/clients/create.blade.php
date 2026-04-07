<x-app-container>
    <div class="mb-4">
        <a href="{{ route('clients.index') }}" wire:navigate class="text-sm text-text-muted hover:text-primary">
            &larr; Back to Clients
        </a>
    </div>

    <x-page-header title="Create Client" />

    <x-card>
        <form wire:submit="save" class="space-y-6 max-w-2xl">
            <x-form-field label="Client Name" name="name" required>
                <x-input id="name" type="text" placeholder="e.g. Acme Corp" wire:model="name" />
            </x-form-field>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <x-form-field label="Email Address" name="email">
                    <x-input id="email" type="email" placeholder="client@example.com" wire:model="email" />
                </x-form-field>
                <x-form-field label="Website" name="website_url">
                    <x-input id="website_url" type="url" placeholder="https://example.com" wire:model="website_url" />
                </x-form-field>
            </div>

            <x-form-field label="Phone" name="phone">
                <x-input id="phone" type="text" placeholder="+1 555 000 0000" wire:model="phone" />
            </x-form-field>

            <div class="grid grid-cols-2 gap-6">
                <x-form-field label="Industry" name="industry">
                    <x-input id="industry" type="text" placeholder="e.g. Technology" wire:model="industry" />
                </x-form-field>
                <x-form-field label="External ID" name="external_ref">
                    <x-input id="external_ref" type="text" placeholder="CRM-12345" wire:model="external_ref" />
                </x-form-field>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <x-form-field label="Timezone" name="timezone">
                    <x-input id="timezone" type="text" placeholder="e.g. America/New_York" wire:model="timezone" />
                </x-form-field>
                <x-form-field label="Currency" name="currency_code">
                    <x-input id="currency_code" type="text" placeholder="e.g. USD" wire:model="currency_code" />
                </x-form-field>
            </div>

            <div class="space-y-4 pt-2">
                <div class="text-xs font-black text-gray-400 uppercase tracking-widest">Business Address</div>
                <x-form-field label="Address Line 1" name="address_line1">
                    <x-input id="address_line1" type="text" wire:model="address_line1" />
                </x-form-field>
                <x-form-field label="Address Line 2" name="address_line2">
                    <x-input id="address_line2" type="text" wire:model="address_line2" />
                </x-form-field>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <x-form-field label="City" name="city">
                        <x-input id="city" type="text" wire:model="city" />
                    </x-form-field>
                    <x-form-field label="State" name="state">
                        <x-input id="state" type="text" wire:model="state" />
                    </x-form-field>
                </div>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <x-form-field label="Postal Code" name="postal_code">
                        <x-input id="postal_code" type="text" wire:model="postal_code" />
                    </x-form-field>
                    <x-form-field label="Country Code" name="country_code">
                        <x-input id="country_code" type="text" placeholder="e.g. US" wire:model="country_code" />
                    </x-form-field>
                </div>
            </div>

            <x-form-field label="Business Description" name="business_description">
                <x-textarea id="business_description" rows="3" wire:model="business_description"></x-textarea>
            </x-form-field>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <x-form-field label="Goals (one per line)" name="goalsText">
                    <x-textarea id="goalsText" rows="4" wire:model="goalsText"></x-textarea>
                </x-form-field>
                <x-form-field label="Primary KPIs (one per line)" name="primaryKpisText">
                    <x-textarea id="primaryKpisText" rows="4" wire:model="primaryKpisText"></x-textarea>
                </x-form-field>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <x-form-field label="Target Audience (one per line)" name="targetAudienceText">
                    <x-textarea id="targetAudienceText" rows="4" wire:model="targetAudienceText"></x-textarea>
                </x-form-field>
                <x-form-field label="Competitors (one per line)" name="competitorsText">
                    <x-textarea id="competitorsText" rows="4" wire:model="competitorsText"></x-textarea>
                </x-form-field>
            </div>

            <div class="space-y-4 pt-2">
                <div class="text-xs font-black text-gray-400 uppercase tracking-widest">Privacy</div>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <x-form-field label="Privacy Contact Email" name="privacy_contact_email">
                        <x-input id="privacy_contact_email" type="email" wire:model="privacy_contact_email" />
                    </x-form-field>
                    <x-form-field label="Data Retention (days)" name="data_retention_days">
                        <x-input id="data_retention_days" type="number" wire:model="data_retention_days" />
                    </x-form-field>
                </div>

                <div class="flex flex-col gap-3">
                    <label class="flex items-center gap-3 text-sm text-gray-700">
                        <input type="checkbox" wire:model="gdpr_consent" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                        GDPR consent received
                    </label>
                    <label class="flex items-center gap-3 text-sm text-gray-700">
                        <input type="checkbox" wire:model="ccpa_opt_out" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                        CCPA opt-out requested
                    </label>
                </div>
            </div>

            <x-form-field label="Status" name="status" required>
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
