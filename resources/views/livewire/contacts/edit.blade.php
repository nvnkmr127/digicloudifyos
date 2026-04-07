<x-app-container>
    <x-page-header title="Edit Contact">
        <a href="{{ route('contacts.show', $contact->id) }}" class="text-sm font-medium text-text-muted hover:text-text-primary">Cancel</a>
    </x-page-header>

    <div class="max-w-4xl">
        <x-card>
            <form wire:submit.prevent="update" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="first_name" value="First Name" />
                        <x-text-input id="first_name" type="text" class="mt-1 block w-full" wire:model="first_name" required />
                        <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="last_name" value="Last Name" />
                        <x-text-input id="last_name" type="text" class="mt-1 block w-full" wire:model="last_name" required />
                        <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email" type="email" class="mt-1 block w-full" wire:model="email" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="phone" value="Phone" />
                        <x-text-input id="phone" type="text" class="mt-1 block w-full" wire:model="phone" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>

                    <div class="md:col-span-2">
                        <x-input-label for="company_name" value="Company Name" />
                        <x-text-input id="company_name" type="text" class="mt-1 block w-full" wire:model="company_name" />
                        <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="type" value="Contact Type" />
                        <select id="type" wire:model="type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-primary focus:ring-primary whitespace-nowrap overflow-hidden text-ellipsis">
                            <option value="lead">Lead</option>
                            <option value="customer">Customer</option>
                            <option value="partner">Partner</option>
                        </select>
                        <x-input-error :messages="$errors->get('type')" class="mt-2" />
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <x-button color="primary" type="submit" wire:loading.attr="disabled">
                        {{ __('Update Contact') }}
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-container>
