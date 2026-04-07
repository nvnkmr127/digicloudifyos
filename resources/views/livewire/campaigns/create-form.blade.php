<x-app-container>
    <div class="mb-4">
        <a href="{{ route('campaigns.index') }}" wire:navigate class="text-xs font-black text-gray-400 hover:text-indigo-600 uppercase tracking-widest transition-colors flex items-center">
            <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Campaigns
        </a>
    </div>

    <x-page-header title="Initiate New Campaign" />

    <div class="max-w-3xl">
        <x-card class="p-8 rounded-[2rem]">
            <form wire:submit="save" class="space-y-8">
                <x-form-field label="Campaign Strategy Name" name="name">
                    <x-input id="name" type="text" placeholder="e.g. Summer Growth Protocol 2024" wire:model="name" class="rounded-xl" />
                </x-form-field>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <x-form-field label="Client Partner" name="client_id">
                        <x-select id="client_id" wire:model="client_id" class="rounded-xl">
                            <option value="">Select a client...</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </x-select>
                    </x-form-field>

                    <x-form-field label="Deployment Infrastructure" name="ad_account_id">
                        <x-select id="ad_account_id" wire:model="ad_account_id" class="rounded-xl">
                            <option value="">Select an account...</option>
                            @foreach($adAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->account_name }} ({{ strtoupper($account->platform) }})</option>
                            @endforeach
                        </x-select>
                    </x-form-field>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <x-form-field label="Conversion Objective" name="objective">
                        <x-select id="objective" wire:model="objective" class="rounded-xl">
                            <option value="">Select objective...</option>
                            @foreach(\App\Enums\CampaignObjective::cases() as $objective)
                                <option value="{{ $objective->value }}">{{ $objective->label() }}</option>
                            @endforeach
                        </x-select>
                    </x-form-field>

                    <x-form-field label="Initial Status" name="status">
                        <x-select id="status" wire:model="status" class="rounded-xl">
                            @foreach(\App\Enums\CampaignStatus::cases() as $status)
                                <option value="{{ $status->value }}">{{ $status->label() }}</option>
                            @endforeach
                        </x-select>
                    </x-form-field>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <x-form-field label="Launch Date" name="start_date">
                        <x-input id="start_date" type="date" wire:model="start_date" class="rounded-xl" />
                    </x-form-field>

                    <x-form-field label="Conclusion Date" name="end_date">
                        <x-input id="end_date" type="date" wire:model="end_date" class="rounded-xl" />
                    </x-form-field>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <x-form-field label="Daily Allocation ($)" name="daily_budget">
                        <x-input id="daily_budget" type="number" step="0.01" wire:model="daily_budget" class="rounded-xl" />
                    </x-form-field>

                    <x-form-field label="Lifetime Ceiling ($)" name="lifetime_budget">
                        <x-input id="lifetime_budget" type="number" step="0.01" wire:model="lifetime_budget" class="rounded-xl" />
                    </x-form-field>
                </div>

                <div class="flex justify-end gap-4 pt-8 border-t border-gray-50">
                    <x-button color="outline" href="{{ route('campaigns.index') }}" wire:navigate class="rounded-xl px-8">
                        Cancel
                    </x-button>
                    <x-button color="primary" type="submit" class="rounded-xl px-8 shadow-lg shadow-indigo-100">
                        Protocol Initiation
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-container>
