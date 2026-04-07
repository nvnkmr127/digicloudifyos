<x-app-container>
    <div class="max-w-4xl mx-auto py-8">
        <!-- Progress Header -->
        <div class="mb-12">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-3xl font-black text-gray-900 tracking-tight">Onboarding <span class="text-primary italic">{{ $client->name }}</span></h1>
                    <p class="text-gray-500 font-medium">Step {{ $currentStep }} of {{ $totalSteps }}: {{ [1 => 'Company Profile', 2 => 'Global Configuration', 3 => 'Strategic Goals', 4 => 'Compliance & Status'][$currentStep] }}</p>
                </div>
                <div class="text-right">
                    <span class="text-4xl font-black text-primary">{{ round(($currentStep / $totalSteps) * 100) }}%</span>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Onboarding Progress</p>
                </div>
            </div>
            
            <div class="relative h-2 w-full bg-gray-100 rounded-full overflow-hidden">
                <div class="absolute inset-y-0 left-0 bg-gradient-to-r from-primary to-indigo-400 transition-all duration-700 ease-out" 
                     style="width: {{ ($currentStep / $totalSteps) * 100 }}%">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-[3rem] shadow-2xl shadow-indigo-50/50 border border-indigo-50 overflow-hidden">
            <div class="p-12">
                @php
                    $stepTitles = [
                        1 => 'Company Profile',
                        2 => 'Global Configuration',
                        3 => 'Strategic Goals',
                        4 => 'Compliance & Status',
                        5 => 'Next Steps & Checklist'
                    ];
                @endphp
                
                @if($currentStep === 1)
                <!-- Step 1 content -->
                <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <div class="space-y-2">
                        <h2 class="text-xl font-bold text-gray-900">{{ $stepTitles[1] }}</h2>
                        <p class="text-sm text-gray-500">Detailed information about the client's business presence.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <x-form-field label="Industry" name="industry">
                            <x-input wire:model="industry" placeholder="e.g. Fintech, E-commerce" />
                        </x-form-field>
                        <x-form-field label="Website URL" name="website_url">
                            <x-input wire:model="website_url" type="url" placeholder="https://example.com" />
                        </x-form-field>
                    </div>

                    <x-form-field label="Phone Number" name="phone">
                        <x-input wire:model="phone" placeholder="+1 (555) 000-0000" />
                    </x-form-field>

                    <x-form-field label="Business Description" name="business_description">
                        <x-textarea wire:model="business_description" rows="4" placeholder="Briefly describe what this business does..." />
                    </x-form-field>
                </div>
                @endif

                @if($currentStep === 2)
                <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <div class="space-y-2">
                        <h2 class="text-xl font-bold text-gray-900">{{ $stepTitles[2] }}</h2>
                        <p class="text-sm text-gray-500">Regional settings and physical headquarters address.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <x-form-field label="Timezone" name="timezone">
                            <x-input wire:model="timezone" placeholder="e.g. UTC, America/New_York" />
                        </x-form-field>
                        <x-form-field label="Currency Code" name="currency_code">
                            <x-input wire:model="currency_code" placeholder="e.g. USD, EUR, GBP" />
                        </x-form-field>
                    </div>

                    <div class="space-y-6 pt-4 border-t border-gray-100">
                        <x-form-field label="Address Line 1" name="address_line1">
                            <x-input wire:model="address_line1" />
                        </x-form-field>
                        <x-form-field label="Address Line 2" name="address_line2">
                            <x-input wire:model="address_line2" />
                        </x-form-field>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <x-form-field label="City" name="city">
                                <x-input wire:model="city" />
                            </x-form-field>
                            <x-form-field label="State" name="state">
                                <x-input wire:model="state" />
                            </x-form-field>
                            <x-form-field label="Postal Code" name="postal_code">
                                <x-input wire:model="postal_code" />
                            </x-form-field>
                        </div>
                    </div>
                </div>
                @endif

                @if($currentStep === 3)
                <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <div class="space-y-2">
                        <h2 class="text-xl font-bold text-gray-900">{{ $stepTitles[3] }}</h2>
                        <p class="text-sm text-gray-500">Help us understand what success looks like for the client.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <x-form-field label="Primary Goals (One per line)" name="goalsText">
                            <x-textarea wire:model="goalsText" rows="5" placeholder="e.g. Increase revenue by 20%" />
                        </x-form-field>
                        <x-form-field label="Primary KPIs (One per line)" name="primaryKpisText">
                            <x-textarea wire:model="primaryKpisText" rows="5" placeholder="e.g. Cost Per Acquisition < $50" />
                        </x-form-field>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <x-form-field label="Target Audience (One per line)" name="targetAudienceText">
                            <x-textarea wire:model="targetAudienceText" rows="5" placeholder="e.g. Males 25-34 in United States" />
                        </x-form-field>
                        <x-form-field label="Top Competitors (One per line)" name="competitorsText">
                            <x-textarea wire:model="competitorsText" rows="5" placeholder="e.g. Competitor A, Competitor B" />
                        </x-form-field>
                    </div>
                </div>
                @endif

                @if($currentStep === 4)
                <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <div class="space-y-2">
                        <h2 class="text-xl font-bold text-gray-900">{{ $stepTitles[4] }}</h2>
                        <p class="text-sm text-gray-500">Legal requirements and initial system status.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <x-form-field label="Privacy Contact Email" name="privacy_contact_email">
                            <x-input wire:model="privacy_contact_email" type="email" placeholder="privacy@client.com" />
                        </x-form-field>
                        <x-form-field label="Data Retention (Days)" name="data_retention_days">
                            <x-input wire:model="data_retention_days" type="number" placeholder="730" />
                        </x-form-field>
                    </div>

                    <div class="space-y-4 p-6 bg-gray-50 rounded-3xl border border-gray-100">
                        <label class="flex items-start gap-3 cursor-pointer group">
                            <input type="checkbox" wire:model="gdpr_consent" class="mt-1 rounded border-gray-300 text-primary focus:ring-primary h-5 w-5" />
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-900">GDPR Consent Received</span>
                                <span class="text-xs text-gray-500">The client has explicitly consented to data processing under GDPR regulations.</span>
                            </div>
                        </label>
                        <label class="flex items-start gap-3 cursor-pointer group">
                            <input type="checkbox" wire:model="ccpa_opt_out" class="mt-1 rounded border-gray-300 text-primary focus:ring-primary h-5 w-5" />
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-900">CCPA Opt-Out Requested</span>
                                <span class="text-xs text-gray-500">The client has requested to opt-out of data sales under CCPA.</span>
                            </div>
                        </label>
                    </div>

                    <x-form-field label="Initial Client Status" name="status">
                        <x-select wire:model="status">
                            <option value="ACTIVE">Active (Live in System)</option>
                            <option value="INACTIVE">Inactive (Hidden from Dashboards)</option>
                        </x-select>
                    </x-form-field>
                </div>
                @endif

                @if($currentStep === 5)
                <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <div class="bg-primary/5 p-8 rounded-[2.5rem] border border-primary/10 flex items-center gap-6">
                        <div class="w-20 h-20 rounded-full bg-primary flex items-center justify-center text-white shadow-xl shadow-primary/30">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-2xl font-black text-gray-900 tracking-tight">Data Collection <span class="text-primary italic">Complete!</span></h2>
                            <p class="text-gray-500 font-medium leading-relaxed">All core details for <b>{{ $client->name }}</b> have been successfully captured. Now, finalize the remaining setup items below.</p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        @livewire('clients.onboarding-checklist', ['client' => $client])
                    </div>
                </div>
                @endif
            </div>

            <!-- Footer Controls -->
            <div class="px-12 py-8 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                <button type="button" 
                        wire:click="previousStep" 
                        @if($currentStep === 1) disabled @endif
                        class="px-8 py-3 rounded-2xl text-sm font-bold transition-all duration-300 {{ $currentStep === 1 ? 'text-gray-300 cursor-not-allowed' : 'text-gray-600 hover:bg-gray-200' }}">
                    Back
                </button>
                
                <button type="button" 
                        wire:click="nextStep" 
                        class="px-10 py-4 bg-primary hover:bg-primary-dark text-white rounded-2xl text-sm font-black shadow-xl shadow-primary/25 transition-all duration-300 transform hover:-translate-y-1 active:scale-95">
                    {{ $currentStep === $totalSteps ? 'Go to Client Dashboard' : 'Save & Continue' }}
                </button>
            </div>
        </div>
    </div>
</x-app-container>
