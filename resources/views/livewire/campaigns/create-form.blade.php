<x-app-container>
    <div class="mb-4">
        <a href="{{ route('campaigns.index') }}" wire:navigate class="text-sm text-text-muted hover:text-primary">
            &larr; Back to Campaigns
        </a>
    </div>

    <x-page-header title="Create Campaign" />

    <x-card>
        <form wire:submit="save" class="space-y-6 max-w-2xl">
            <div>
                <label for="name" class="block text-sm font-medium text-text-primary">Campaign Name</label>
                <x-input id="name" type="text" placeholder="e.g. Summer Sale 2024" wire:model="name" />
                @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="client_id" class="block text-sm font-medium text-text-primary">Client</label>
                    <select id="client_id" wire:model="client_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Select a client...</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                    @error('client_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="ad_account_id" class="block text-sm font-medium text-text-primary">Ad Account</label>
                    <select id="ad_account_id" wire:model="ad_account_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Select an account...</option>
                        @foreach($adAccounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }} ({{ strtoupper($account->platform) }})
                            </option>
                        @endforeach
                    </select>
                    @error('ad_account_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="objective" class="block text-sm font-medium text-text-primary">Objective</label>
                    <select id="objective" wire:model="objective"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Select objective...</option>
                        <option value="awareness">Awareness</option>
                        <option value="traffic">Traffic</option>
                        <option value="engagement">Engagement</option>
                        <option value="leads">Leads</option>
                        <option value="app_promotion">App Promotion</option>
                        <option value="sales">Sales</option>
                    </select>
                    @error('objective') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-text-primary">Status</label>
                    <select id="status" wire:model="status"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="planned">Planned</option>
                        <option value="active">Active</option>
                        <option value="paused">Paused</option>
                        <option value="completed">Completed</option>
                        <option value="archived">Archived</option>
                    </select>
                    @error('status') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-text-primary">Start Date</label>
                    <x-input id="start_date" type="date" wire:model="start_date" />
                    @error('start_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-text-primary">End Date</label>
                    <x-input id="end_date" type="date" wire:model="end_date" />
                    @error('end_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="daily_budget" class="block text-sm font-medium text-text-primary">Daily Budget</label>
                    <x-input id="daily_budget" type="number" step="0.01" wire:model="daily_budget" />
                    @error('daily_budget') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="lifetime_budget" class="block text-sm font-medium text-text-primary">Lifetime
                        Budget</label>
                    <x-input id="lifetime_budget" type="number" step="0.01" wire:model="lifetime_budget" />
                    @error('lifetime_budget') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <x-button color="outline" href="{{ route('campaigns.index') }}" wire:navigate>Cancel</x-button>
                <x-button color="primary" type="submit">Create Campaign</x-button>
            </div>
        </form>
    </x-card>
</x-app-container>