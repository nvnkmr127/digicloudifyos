<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white/80 backdrop-blur-xl border border-white/20 shadow-2xl rounded-[3rem] overflow-hidden">
            <!-- Progress Bar -->
            <div class="bg-gray-50/50 p-8 border-b border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-2xl font-black text-gray-900 tracking-tight">Campaign Creation Wizard</h2>
                    <span class="text-sm font-black text-indigo-600 uppercase tracking-widest">Step {{ $step }} of
                        3</span>
                </div>
                <div class="h-2 w-full bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-indigo-600 transition-all duration-500 rounded-full"
                        style="width: {{ ($step / 3) * 100 }}%"></div>
                </div>
            </div>

            <div class="p-12">
                @if (session()->has('error'))
                    <div
                        class="mb-8 bg-rose-50 border border-rose-100 text-rose-600 px-6 py-4 rounded-2xl font-bold flex items-center shadow-sm">
                        <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        {{ session('error') }}
                    </div>
                @endif

                @if($step == 1)
                    <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Select
                                    Client Partner</label>
                                <select wire:model="client_id"
                                    class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4 px-5 font-bold focus:ring-indigo-500 focus:border-indigo-500 transition">
                                    <option value="">Select a client...</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                                    @endforeach
                                </select>
                                @error('client_id') <span
                                    class="text-rose-500 text-[10px] font-bold uppercase tracking-widest ml-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Target Ad
                                    Account (Meta)</label>
                                <select wire:model="ad_account_id"
                                    class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4 px-5 font-bold focus:ring-indigo-500 focus:border-indigo-500 transition">
                                    <option value="">Select account...</option>
                                    @foreach($adAccounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->account_name }}
                                            ({{ $account->external_account_id }})</option>
                                    @endforeach
                                </select>
                                @error('ad_account_id') <span
                                    class="text-rose-500 text-[10px] font-bold uppercase tracking-widest ml-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Campaign
                                Strategy Name</label>
                            <input type="text" wire:model="campaign_name" placeholder="E.g. Summer 2026 Brand Lift"
                                class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4 px-5 font-bold focus:ring-indigo-500 focus:border-indigo-500 transition">
                            @error('campaign_name') <span
                                class="text-rose-500 text-[10px] font-bold uppercase tracking-widest ml-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Marketing
                                Objective</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-2">
                                @foreach(['OUTCOME_REACH' => 'Reach', 'OUTCOME_TRAFFIC' => 'Traffic', 'OUTCOME_ENGAGEMENT' => 'Engagement', 'OUTCOME_LEADS' => 'Leads'] as $val => $label)
                                    <label class="cursor-pointer relative">
                                        <input type="radio" wire:model="objective" value="{{ $val }}" class="sr-only peer">
                                        <div
                                            class="bg-gray-50 p-4 rounded-2xl border border-gray-100 text-center font-black text-xs uppercase tracking-widest transition peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600 hover:bg-gray-100">
                                            {{ $label }}
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                @elseif($step == 2)
                    <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Ad Set Label
                                (For Tracking)</label>
                            <input type="text" wire:model="ad_set_name"
                                placeholder="E.g. US - Interests: Marketing - Male 25-45"
                                class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4 px-5 font-bold focus:ring-indigo-500 focus:border-indigo-500 transition">
                            @error('ad_set_name') <span
                                class="text-rose-500 text-[10px] font-bold uppercase tracking-widest ml-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Daily Cap
                                    ($)</label>
                                <div class="relative">
                                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 font-black">$</span>
                                    <input type="number" wire:model="daily_budget"
                                        class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4 pl-10 pr-5 font-bold focus:ring-indigo-500 focus:border-indigo-500 transition">
                                </div>
                                @error('daily_budget') <span
                                    class="text-rose-500 text-[10px] font-bold uppercase tracking-widest ml-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Target
                                    Geographies</label>
                                <input type="text" value="United States" disabled
                                    class="w-full bg-gray-100 border-gray-200 rounded-2xl py-4 px-5 font-bold text-gray-500 opacity-60">
                                <p class="text-[9px] font-bold text-gray-400 mt-1 uppercase tracking-widest italic">*
                                    Targeting is currently simplified to US</p>
                            </div>
                        </div>
                    </div>

                @elseif($step == 3)
                    <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Ad Creative
                                Label</label>
                            <input type="text" wire:model="ad_name" placeholder="E.g. Hero Image - Main CTA"
                                class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4 px-5 font-bold focus:ring-indigo-500 focus:border-indigo-500 transition">
                            @error('ad_name') <span
                                class="text-rose-500 text-[10px] font-bold uppercase tracking-widest ml-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Primary Ad
                                Headline</label>
                            <input type="text" wire:model="headline" placeholder="Stop searching, start scaling."
                                class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4 px-5 font-bold focus:ring-indigo-500 focus:border-indigo-500 transition">
                            @error('headline') <span
                                class="text-rose-500 text-[10px] font-bold uppercase tracking-widest ml-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Body
                                Narratives (Hook + Story)</label>
                            <textarea wire:model="body_text" rows="4"
                                placeholder="Join 5,000+ organizations already utilizing DC OS for their daily operations..."
                                class="w-full bg-gray-50 border-gray-100 rounded-3xl py-4 px-5 font-bold focus:ring-indigo-500 focus:border-indigo-500 transition"></textarea>
                            @error('body_text') <span
                                class="text-rose-500 text-[10px] font-bold uppercase tracking-widest ml-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="bg-indigo-50 p-6 rounded-3xl border border-indigo-100">
                            <h5 class="text-sm font-black text-indigo-900 uppercase tracking-widest mb-2 flex items-center">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Creative Asset Selection
                            </h5>
                            <p class="text-[10px] font-medium text-indigo-600 uppercase tracking-widest mb-4">You'll be able
                                to select from your creative library in the next release.</p>
                            <div
                                class="h-40 border-2 border-dashed border-indigo-200 rounded-2xl flex items-center justify-center bg-white/50">
                                <span class="text-[10px] font-black text-indigo-300 uppercase tracking-widest">Library
                                    Integration Pending</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Footer Controls -->
            <div class="bg-gray-50/50 p-8 border-t border-gray-100 flex items-center justify-between">
                <div>
                    @if($step > 1)
                        <button wire:click="previousStep"
                            class="text-gray-400 font-black text-xs uppercase tracking-widest hover:text-gray-600 transition">Back
                            to Previous</button>
                    @endif
                </div>
                <div class="flex space-x-4">
                    @if($step < 3)
                        <button wire:click="nextStep"
                            class="bg-indigo-600 text-white px-10 py-4 rounded-2xl text-xs font-black uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-700 active:scale-95 transition-all">Continue
                            Journey</button>
                    @else
                        <button wire:click="create"
                            class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-10 py-4 rounded-2xl text-xs font-black uppercase tracking-widest shadow-xl shadow-indigo-100 hover:scale-[1.02] active:scale-95 transition-all group">
                            Deploy Infrastructure
                            <svg class="h-4 w-4 inline ml-2 group-hover:translate-x-1 transition" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>