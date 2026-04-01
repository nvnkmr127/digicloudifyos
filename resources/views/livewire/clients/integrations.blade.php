<x-app-container>
    <div class="mb-4">
        <a href="{{ route('clients.index') }}" wire:navigate class="text-sm text-text-muted hover:text-primary">
            &larr; Back to Clients
        </a>
    </div>

    <x-page-header title="Integrations: {{ $client->name }}" />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-card class="lg:col-span-2">
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-black text-gray-900">Connected Accounts</div>
                        <div class="text-xs text-gray-500">Data pulls run daily and feed the morning briefing.</div>
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse($connections as $c)
                        <div class="flex items-center justify-between p-4 border border-gray-100 rounded-2xl bg-gray-50">
                            <div class="min-w-0">
                                <div class="text-sm font-bold text-gray-900 truncate">
                                    {{ strtoupper($c->channel_type) }}
                                    @if($c->account_name)
                                        <span class="text-gray-500 font-semibold">— {{ $c->account_name }}</span>
                                    @elseif($c->account_id)
                                        <span class="text-gray-500 font-semibold">— {{ $c->account_id }}</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">
                                    Status: {{ $c->is_active ? 'Active' : 'Disabled' }}
                                    @if($c->last_synced_at)
                                        • Last synced: {{ $c->last_synced_at->diffForHumans() }}
                                    @endif
                                    @if($c->last_sync_status)
                                        • Last result: {{ $c->last_sync_status }}
                                    @endif
                                </div>
                            </div>
                            <div class="text-xs font-bold text-gray-500">
                                {{ $c->connected_at ? $c->connected_at->toDateString() : '' }}
                            </div>
                        </div>
                    @empty
                        <div class="p-6 border border-dashed border-gray-200 rounded-2xl text-sm text-gray-600">
                            No connected accounts yet.
                        </div>
                    @endforelse
                </div>

                <div class="pt-4 space-y-3">
                    <div class="text-xs font-black text-gray-400 uppercase tracking-widest">Recent Sync Runs</div>
                    <div class="space-y-2">
                        @forelse($recentRuns as $run)
                            <div class="flex items-center justify-between p-3 border border-gray-100 rounded-2xl bg-white">
                                <div class="min-w-0">
                                    <div class="text-sm font-bold text-gray-900 truncate">
                                        {{ strtoupper($run->channel_type) }} — {{ $run->run_date->toDateString() }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Status: {{ $run->status }}
                                        @if($run->attempt)
                                            • Attempt: {{ $run->attempt }}
                                        @endif
                                        @if($run->finished_at)
                                            • Finished: {{ $run->finished_at->diffForHumans() }}
                                        @endif
                                    </div>
                                    @if($run->error_message)
                                        <div class="text-xs text-red-600 truncate">{{ $run->error_message }}</div>
                                    @endif
                                </div>
                                <div class="text-xs font-bold text-gray-500">
                                    {{ $run->created_at->format('H:i') }}
                                </div>
                            </div>
                        @empty
                            <div class="p-4 border border-dashed border-gray-200 rounded-2xl text-sm text-gray-600">
                                No sync runs yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="space-y-6">
                <div>
                    <div class="text-sm font-black text-gray-900">Connect New</div>
                    <div class="text-xs text-gray-500">Connect accounts per client to enable daily analytics and recommendations.</div>
                </div>

                <div class="space-y-3">
                    <a class="w-full inline-flex justify-center px-4 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition"
                       href="{{ route('integrations.oauth.redirect', ['provider' => 'google_analytics', 'client_id' => $client->id]) }}">
                        Connect Google Analytics
                    </a>

                    <a class="w-full inline-flex justify-center px-4 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition"
                       href="{{ route('integrations.oauth.redirect', ['provider' => 'google_search_console', 'client_id' => $client->id]) }}">
                        Connect Search Console
                    </a>

                    <a class="w-full inline-flex justify-center px-4 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition"
                       href="{{ route('integrations.oauth.redirect', ['provider' => 'google_merchant_center', 'client_id' => $client->id]) }}">
                        Connect Merchant Center
                    </a>

                    <a class="w-full inline-flex justify-center px-4 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition"
                       href="{{ route('integrations.oauth.redirect', ['provider' => 'meta_organic', 'client_id' => $client->id]) }}">
                        Connect Facebook/Instagram (Organic)
                    </a>

                    <a class="w-full inline-flex justify-center px-4 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition"
                       href="{{ route('integrations.oauth.redirect', ['provider' => 'twitter', 'client_id' => $client->id]) }}">
                        Connect Twitter/X
                    </a>

                    <a class="w-full inline-flex justify-center px-4 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition"
                       href="{{ route('integrations.oauth.redirect', ['provider' => 'linkedin_organic', 'client_id' => $client->id]) }}">
                        Connect LinkedIn (Organic)
                    </a>
                </div>

                <div class="pt-2 space-y-3">
                    <div class="text-xs font-black text-gray-400 uppercase tracking-widest">Shopify</div>
                    <x-form-field label="Shop Domain" name="shopifyShop">
                        <x-input id="shopifyShop" type="text" placeholder="your-store.myshopify.com" wire:model="shopifyShop" />
                    </x-form-field>
                    <x-button color="primary" type="button" class="w-full justify-center" wire:click="connectShopify">
                        Connect Shopify
                    </x-button>
                </div>

                <div class="pt-2 space-y-3">
                    <div class="text-xs font-black text-gray-400 uppercase tracking-widest">WooCommerce</div>
                    <x-form-field label="Store URL" name="wooStoreUrl">
                        <x-input id="wooStoreUrl" type="text" placeholder="https://store.example.com" wire:model="wooStoreUrl" />
                    </x-form-field>
                    <x-form-field label="Consumer Key" name="wooConsumerKey">
                        <x-input id="wooConsumerKey" type="text" wire:model="wooConsumerKey" />
                    </x-form-field>
                    <x-form-field label="Consumer Secret" name="wooConsumerSecret">
                        <x-input id="wooConsumerSecret" type="password" wire:model="wooConsumerSecret" />
                    </x-form-field>
                    <x-button color="primary" type="button" class="w-full justify-center" wire:click="connectWooCommerce">
                        Connect WooCommerce
                    </x-button>
                </div>

                <div class="pt-2 space-y-3">
                    <div class="text-xs font-black text-gray-400 uppercase tracking-widest">Ads</div>
                    <a class="w-full inline-flex justify-center px-4 py-2.5 bg-gray-900 text-white text-sm font-bold rounded-xl hover:bg-black transition"
                       href="{{ route('ads.redirect', ['platform' => 'meta', 'client_id' => $client->id]) }}">
                        Connect Meta Ads
                    </a>
                    <a class="w-full inline-flex justify-center px-4 py-2.5 bg-gray-900 text-white text-sm font-bold rounded-xl hover:bg-black transition"
                       href="{{ route('ads.redirect', ['platform' => 'google', 'client_id' => $client->id]) }}">
                        Connect Google Ads
                    </a>
                    <a class="w-full inline-flex justify-center px-4 py-2.5 bg-gray-900 text-white text-sm font-bold rounded-xl hover:bg-black transition"
                       href="{{ route('ads.redirect', ['platform' => 'linkedin', 'client_id' => $client->id]) }}">
                        Connect LinkedIn Ads
                    </a>
                </div>

                <div class="pt-2 space-y-3">
                    <div class="text-xs font-black text-gray-400 uppercase tracking-widest">Privacy</div>
                    <a class="w-full inline-flex justify-center px-4 py-2.5 bg-white border border-gray-200 text-gray-800 text-sm font-bold rounded-xl hover:bg-gray-50 transition"
                       href="{{ route('clients.privacy.export', $client->id) }}">
                        Export Client Data (JSON)
                    </a>

                    <form method="POST" action="{{ route('clients.privacy.erase', $client->id) }}">
                        @csrf
                        <button type="submit"
                            class="w-full inline-flex justify-center px-4 py-2.5 bg-red-600 text-white text-sm font-bold rounded-xl hover:bg-red-700 transition"
                            onclick="return confirm('Erase and archive this client? This disables integrations and removes profile details.')">
                            Erase & Archive
                        </button>
                    </form>
                </div>
            </div>
        </x-card>
    </div>
</x-app-container>
