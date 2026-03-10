<x-card>
    @if (session()->has('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-md text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-md text-sm font-medium">
            {{ session('error') }}
        </div>
    @endif
    <div class="border-b border-gray-100 pb-4 mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-lg font-semibold text-text-primary">Facebook Ads Integration</h2>
            <p class="text-sm text-text-muted mt-1">Connect your clients' Facebook Ad accounts to sync campaigns and
                metrics.</p>
        </div>
        <div class="flex items-center gap-4">
            @if(!$facebookUser)
                @php
                    // For demonstration, we assume we want to link it with a specific client.
                    // Or let's just use the redirect route.
                    // $redirectUrl = route('ads.redirect', ['platform' => 'meta']); // This is now handled in the href below
                @endphp
                <div class="flex items-center gap-2">
                    <select wire:model.live="selectedClientId" class="text-xs rounded-md border-gray-200 focus:border-primary focus:ring-primary py-2 pr-8">
                        <option value="">Default Client (First)</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                    
                    <a href="{{ route('ads.redirect', ['platform' => 'meta', 'client_id' => $selectedClientId]) }}" class="inline-flex items-center px-4 py-2 bg-[#1877F2] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#166fe5] shadow-sm transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                        </svg>
                        Connect Facebook
                    </a>
                </div>
            @else
                <div class="flex items-center gap-2">
                    <button wire:click="syncNow" wire:loading.attr="disabled" class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-dark active:bg-primary-dark focus:outline-none transition ease-in-out duration-150">
                        <svg wire:loading wire:target="syncNow" class="animate-spin -ml-1 mr-2 h-3 w-3 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Sync Structure
                    </button>
                    <button wire:click="disconnectFacebook" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Disconnect
                    </button>
                </div>
            @endif
        </div>
    </div>

    @if($facebookUser)
        <div class="mb-8 p-4 bg-gray-50 rounded-lg flex items-center gap-4 border border-gray-100">
            @if($facebookUser->profile_pic)
                <img src="{{ $facebookUser->profile_pic }}" alt="{{ $facebookUser->name }}"
                    class="w-12 h-12 rounded-full ring-2 ring-blue-500 ring-offset-2 shadow-sm">
            @else
                <div
                    class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold border-2 border-blue-200">
                    {{ substr($facebookUser->name, 0, 1) }}
                </div>
            @endif
            <div>
                <p class="font-medium text-text-primary text-base">{{ $facebookUser->name }}</p>
                <p class="text-xs text-text-muted mt-0.5">Connected: {{ $facebookUser->created_at->format('M d, Y') }}</p>
                @if($facebookUser->email)
                    <p class="text-xs text-text-muted mt-0.5">{{ $facebookUser->email }}</p>
                @endif
            </div>
            <div class="ml-auto">
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    Active Connection
                </span>
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-text-muted">Connected Ad Accounts</h3>
                <span class="text-xs text-text-muted">{{ $adAccounts->count() }} Accounts Linked</span>
            </div>

            <div class="overflow-hidden border border-gray-100 rounded-lg shadow-sm">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wider">
                                Account Name</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wider">
                                Account ID</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wider">
                                Client</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wider">
                                Lead Sync</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wider">
                                Currency</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100 font-medium">
                        @forelse($adAccounts as $account)
                            <tr class="hover:bg-blue-50/20 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-text-primary">
                                    {{ $account->account_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted font-mono">
                                    {{ $account->external_account_id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-text-primary">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-6 h-6 rounded bg-gray-100 flex items-center justify-center text-[10px] font-bold text-gray-500">
                                            {{ substr($account->client->name ?? '?', 0, 1) }}
                                        </div>
                                        {{ $account->client->name ?? 'Unassigned' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2.5 py-1 text-[10px] font-bold uppercase rounded bg-green-50 text-green-600 border border-green-100">
                                        {{ $account->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($account->facebook_page_id)
                                        <div class="flex items-center gap-2 text-green-600 font-bold">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Linked
                                            <button wire:click="openPageSelector('{{ $account->id }}')" class="text-[10px] text-text-muted hover:text-primary underline uppercase tracking-widest ml-1 font-black">Change</button>
                                        </div>
                                    @else
                                        <button wire:click="openPageSelector('{{ $account->id }}')" class="inline-flex items-center px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-md text-[10px] font-black uppercase tracking-widest text-gray-600 hover:bg-white hover:border-primary hover:text-primary transition duration-150">
                                            Connect Page
                                        </button>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-text-primary">
                                    {{ $account->currency_code }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-sm text-text-muted italic font-medium">
                                    No ad accounts linked yet. Use the sync feature to import accounts.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Page Selector Modal -->
        @if($showPageSelector)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="$set('showPageSelector', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-middle bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                    <div class="bg-white p-8">
                        <div class="mb-6">
                            <h3 class="text-2xl font-black text-text-primary tracking-tight" id="modal-title">Select Facebook Page</h3>
                            <p class="text-sm text-text-muted font-bold mt-1 uppercase tracking-widest">For Lead Generation Sync</p>
                        </div>
                        
                        <div class="space-y-3 max-h-96 overflow-y-auto pr-2 custom-scrollbar">
                            @forelse($pages as $page)
                                <button wire:click="connectPage('{{ $page['id'] }}', '{{ $page['access_token'] }}')" class="w-full flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100 hover:bg-primary-50 hover:border-primary-100 hover:scale-[1.02] transition-all group">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center text-primary font-black group-hover:bg-primary group-hover:text-white transition cursor-pointer">
                                            {{ substr($page['name'], 0, 1) }}
                                        </div>
                                        <div class="text-left">
                                            <p class="font-bold text-text-primary group-hover:text-primary transition">{{ $page['name'] }}</p>
                                            <p class="text-[10px] text-text-muted font-black uppercase tracking-widest mt-0.5">ID: {{ $page['id'] }}</p>
                                        </div>
                                    </div>
                                    <svg class="w-5 h-5 text-gray-300 group-hover:text-primary transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            @empty
                                <div class="text-center py-12 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                                    <p class="text-sm text-text-muted font-bold uppercase tracking-widest">No managed pages found</p>
                                    <p class="text-[10px] text-text-muted mt-2 px-8">Ensure your Facebook account has admin access to the pages you want to connect.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    <div class="bg-gray-50/50 px-8 py-6 sm:flex sm:flex-row-reverse border-t border-gray-100">
                        <button type="button" class="w-full inline-flex justify-center rounded-xl border border-gray-200 shadow-sm px-6 py-3 bg-white text-sm font-bold text-text-primary hover:bg-gray-50 focus:outline-none transition sm:ml-3 sm:w-auto uppercase tracking-widest sm:text-xs" wire:click="$set('showPageSelector', false)">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @else
        <div class="text-center py-12 px-6 border-2 border-dashed border-gray-100 rounded-xl bg-gray-50/30">
            <div
                class="w-16 h-16 bg-[#1877F2]/10 rounded-full flex items-center justify-center mx-auto mb-4 text-[#1877F2]">
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                </svg>
            </div>
            <h3 class="text-base font-bold text-text-primary mb-1">No Connection Found</h3>
            <p class="text-sm text-text-muted mb-6 max-w-sm mx-auto">Connecting your Facebook account allows you to securely
                access ad accounts and pull real-time data into your dashboard.</p>
            <a href="{{ route('ads.redirect', ['platform' => 'meta']) }}"
                class="inline-flex items-center px-6 py-2.5 bg-[#1877F2] border border-transparent rounded-lg font-bold text-sm text-white shadow-lg shadow-blue-500/30 hover:bg-[#166fe5] hover:shadow-blue-500/40 transition-all">
                Connect your account
            </a>
        </div>
    @endif
</x-card>