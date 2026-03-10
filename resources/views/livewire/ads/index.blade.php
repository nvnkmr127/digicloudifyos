<div class="p-6">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-text-primary">Social Ads Overview</h1>
            <p class="text-text-muted mt-1">Performance across all connected ad platforms</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('ads.analytics') }}"
                class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 rounded-lg font-semibold text-sm text-text-primary shadow-sm hover:bg-gray-50 transition duration-150">
                <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Analytics Dashboard
            </a>
            <a href="{{ route('ads.leads') }}"
                class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 rounded-lg font-semibold text-sm text-text-primary shadow-sm hover:bg-gray-50 transition duration-150">
                <svg class="w-4 h-4 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Leads Registry
            </a>
            <a href="{{ route('settings', ['tab' => 'ads']) }}"
                class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-lg font-semibold text-sm text-white shadow-lg hover:bg-primary-dark transition duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                </svg>
                Manage Connections
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <x-card class="border-l-4 border-primary">
            <p class="text-xs font-semibold text-text-muted uppercase tracking-wider">Total Connected Accounts</p>
            <h3 class="text-2xl font-bold text-text-primary mt-1">{{ count($accounts) }}</h3>
        </x-card>
        <x-card class="border-l-4 border-green-500">
            <p class="text-xs font-semibold text-text-muted uppercase tracking-wider">Total Spend (Last 30d)</p>
            <h3 class="text-2xl font-bold text-text-primary mt-1">${{ number_format($totalSpend, 2) }}</h3>
        </x-card>
        <x-card class="border-l-4 border-blue-500">
            <p class="text-xs font-semibold text-text-muted uppercase tracking-wider">Total Impressions (Last 30d)</p>
            <h3 class="text-2xl font-bold text-text-primary mt-1">{{ number_format($totalImpressions) }}</h3>
        </x-card>
        <x-card class="border-l-4 border-purple-500">
            <p class="text-xs font-semibold text-text-muted uppercase tracking-wider">Avg. CPM</p>
            <h3 class="text-2xl font-bold text-text-primary mt-1">
                ${{ $totalImpressions > 0 ? number_format(($totalSpend / $totalImpressions) * 1000, 2) : '0.00' }}</h3>
        </x-card>
    </div>

    @if(count($accounts) > 0)
        <!-- Accounts Table -->
        <x-card title="Connected Ad Accounts" class="mb-8">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-text-muted uppercase tracking-wider">
                                Account</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-text-muted uppercase tracking-wider">
                                Platform</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-text-muted uppercase tracking-wider">
                                Campaigns</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-text-muted uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-text-muted uppercase tracking-wider">
                                Latest Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($accounts as $account)
                            <tr class="hover:bg-gray-50/30 transition duration-150">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-text-primary">{{ $account->account_name }}</div>
                                    <div class="text-xs text-text-muted">{{ $account->external_account_id }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $account->platform_name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-text-primary font-bold">
                                    {{ $account->campaigns_count }} Campaigns
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $account->status === 'ACTIVE' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $account->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <a href="{{ route('campaigns.index', ['account' => $account->id]) }}"
                                        class="text-primary hover:text-primary-dark font-black tracking-tight underline-offset-4 hover:underline">
                                        View Campaigns
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>

        <!-- Latest Leads -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <x-card title="Latest Captured Leads">
                    <div class="space-y-4">
                        @forelse($latestLeads as $lead)
                            <div
                                class="flex items-center justify-between p-4 bg-gray-50/50 rounded-2xl border border-gray-100 hover:bg-white hover:border-indigo-100 transition-all group">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center text-primary font-black group-hover:scale-110 transition">
                                        {{ substr($lead->full_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-text-primary tracking-tight">{{ $lead->full_name }}</p>
                                        <p class="text-[10px] text-text-muted font-black uppercase tracking-widest mt-0.5">
                                            {{ $lead->email }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-indigo-600 mb-1">
                                        {{ $lead->form_name ?: 'Facebook Lead Ads' }}</p>
                                    <p class="text-[9px] text-text-muted font-bold">{{ $lead->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="py-12 text-center bg-gray-50/20 rounded-2xl border border-dashed border-gray-200">
                                <p class="text-sm text-text-muted font-black uppercase tracking-widest italic">No leads captured
                                    yet</p>
                            </div>
                        @endforelse
                    </div>
                </x-card>
            </div>

            <div class="space-y-6">
                <div
                    class="bg-indigo-600 p-8 rounded-[2rem] text-white shadow-xl shadow-indigo-100 relative overflow-hidden">
                    <h4 class="text-xl font-black mb-2 tracking-tight">Lead Gen Tips</h4>
                    <p class="text-indigo-100 text-xs font-medium leading-relaxed mb-6">Ensure your Facebook Page is
                        connected to enable real-time webhook capture.</p>
                    <a href="{{ route('settings', ['tab' => 'ads']) }}"
                        class="inline-flex items-center px-4 py-2 bg-white text-indigo-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-50 transition">
                        Check Connections
                    </a>
                    <div class="absolute -right-4 -bottom-4 h-24 w-24 bg-white opacity-5 rounded-full"></div>
                </div>
            </div>
        </div>
    @else
        <!-- Empty State -->
        <x-card class="flex flex-col items-center justify-center p-12 text-center bg-gray-50/20 border-dashed">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-text-primary">No Ad Accounts Connected</h3>
            <p class="text-text-muted mt-2 max-w-sm">Connect your Facebook, Google, or LinkedIn ad accounts to start syncing
                campaigns and performance data.</p>
            <a href="{{ route('settings', ['tab' => 'ads']) }}"
                class="mt-6 inline-flex items-center px-6 py-3 bg-primary border border-transparent rounded-lg font-semibold text-white shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition duration-150">
                Get Started
            </a>
        </x-card>
    @endif
</div>