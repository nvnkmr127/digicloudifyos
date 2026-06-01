<x-app-container>
    <x-page-header title="Create Campaign">
        <x-badge variant="primary" size="xs">Step {{ $step }} of 3</x-badge>
    </x-page-header>

    <x-card class="p-0 overflow-hidden">
        <div class="bg-gray-50 px-6 py-5 border-b border-gray-100">
            <div class="flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <div class="text-sm font-semibold text-text-primary">Campaign Creation Wizard</div>
                    <div class="text-sm text-text-muted">Complete the setup in three steps</div>
                </div>
            </div>

            @php $progress = ($step / 3) * 100; @endphp
            <div class="mt-4 h-2 w-full bg-gray-200 rounded-full overflow-hidden">
                <div x-data="{ p: @js($progress) }" class="h-full bg-primary rounded-full transition-all duration-500" :style="`width: ${p}%`"></div>
            </div>
        </div>

        <div class="p-6">
            @if (session()->has('error'))
                <x-alert type="error" class="mb-6">
                    {{ session('error') }}
                </x-alert>
            @endif

            @if($step == 1)
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-text-muted">Client</label>
                            <x-select wire:model="client_id">
                                <option value="">Select a client…</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </x-select>
                            @error('client_id') <div class="text-sm text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-text-muted">Ad Account (Meta)</label>
                            <x-select wire:model="ad_account_id">
                                <option value="">Select account…</option>
                                @foreach($adAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->account_name }} ({{ $account->external_account_id }})</option>
                                @endforeach
                            </x-select>
                            @error('ad_account_id') <div class="text-sm text-danger">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-text-muted">Campaign Name</label>
                        <x-input type="text" wire:model="campaign_name" placeholder="e.g. Summer 2026 Brand Lift" />
                        @error('campaign_name') <div class="text-sm text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-text-muted">Objective</label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            @foreach(['OUTCOME_REACH' => 'Reach', 'OUTCOME_TRAFFIC' => 'Traffic', 'OUTCOME_ENGAGEMENT' => 'Engagement', 'OUTCOME_LEADS' => 'Leads'] as $val => $label)
                                <label class="cursor-pointer">
                                    <input type="radio" wire:model="objective" value="{{ $val }}" class="sr-only peer">
                                    <div class="bg-gray-50 p-3 rounded-input border border-gray-200 text-center text-sm font-semibold text-text-primary transition peer-checked:bg-primary-soft peer-checked:border-primary peer-checked:text-primary hover:bg-gray-100">
                                        {{ $label }}
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            @elseif($step == 2)
                <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-text-muted">Ad Set Name</label>
                        <x-input type="text" wire:model="ad_set_name" placeholder="e.g. US · Interests: Marketing · 25-45" />
                        @error('ad_set_name') <div class="text-sm text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-text-muted">Daily Budget ($)</label>
                            <x-input type="number" wire:model="daily_budget" inputmode="decimal" />
                            @error('daily_budget') <div class="text-sm text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-text-muted">Geography</label>
                            <x-input type="text" value="United States" disabled />
                            <div class="text-sm text-text-muted">Targeting is currently simplified to US.</div>
                        </div>
                    </div>
                </div>
            @elseif($step == 3)
                <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-text-muted">Ad Name</label>
                        <x-input type="text" wire:model="ad_name" placeholder="e.g. Hero Image · Main CTA" />
                        @error('ad_name') <div class="text-sm text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-text-muted">Headline</label>
                        <x-input type="text" wire:model="headline" placeholder="Stop searching, start scaling." />
                        @error('headline') <div class="text-sm text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-text-muted">Body</label>
                        <x-textarea wire:model="body_text" rows="4" placeholder="Write the offer, proof, and call to action…" />
                        @error('body_text') <div class="text-sm text-danger">{{ $message }}</div> @enderror
                    </div>

                    <x-alert type="info">
                        Creative asset selection will be available once the creative library integration ships.
                    </x-alert>
                </div>
            @endif
        </div>

        <div class="bg-gray-50 px-6 py-5 border-t border-gray-100 flex items-center justify-between gap-4">
            <div>
                @if($step > 1)
                    <x-button variant="outline" wire:click="previousStep" wire:loading.attr="disabled">
                        Back
                    </x-button>
                @endif
            </div>
            <div class="flex items-center gap-3">
                @if($step < 3)
                    <x-button variant="primary" wire:click="nextStep" wire:loading.attr="disabled">
                        Continue
                    </x-button>
                @else
                    <x-button variant="primary" wire:click="create" wire:loading.attr="disabled">
                        Create Campaign
                    </x-button>
                @endif
            </div>
        </div>
    </x-card>
</x-app-container>
