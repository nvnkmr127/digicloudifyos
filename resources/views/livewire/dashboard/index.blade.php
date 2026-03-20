<x-app-container>
    <x-page-header title="Command Central" />

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
        <!-- Stat Cards -->
        <x-card variant="premium" class="group hover:scale-105 transition-all">
            <div class="flex items-center justify-between mb-6">
                <div class="h-12 w-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <span class="text-branding text-brand-muted">+12% vs Q3</span>
            </div>
            <p class="text-branding-wide text-brand-muted mb-1">Active Client Entity</p>
            <h3 class="text-4xl font-black text-gray-900 tracking-tight leading-none">{{ $total_clients }}</h3>
        </x-card>

        <x-card variant="premium" class="group hover:scale-105 transition-all">
            <div class="flex items-center justify-between mb-6">
                <div class="h-12 w-12 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition-all shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                    </svg>
                </div>
                <span class="text-branding text-brand-muted">Ongoing</span>
            </div>
            <p class="text-branding-wide text-brand-muted mb-1">Operational Campaigns</p>
            <h3 class="text-4xl font-black text-gray-900 tracking-tight leading-none">{{ $campaigns_count }}</h3>
        </x-card>

        <x-card variant="premium" class="group hover:scale-105 transition-all">
            <div class="flex items-center justify-between mb-6">
                <div class="h-12 w-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="text-branding text-success">Optimized</span>
            </div>
            <p class="text-branding-wide text-brand-muted mb-1">Processed Revenue</p>
            <h3 class="text-4xl font-black text-gray-900 tracking-tight leading-none">${{ number_format($total_revenue / 1000, 1) }}K</h3>
        </x-card>

        <x-card variant="brand" class="group hover:scale-105 transition-all">
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-6">
                    <div class="h-12 w-12 bg-white/10 rounded-2xl flex items-center justify-center text-white shadow-sm border border-white/10">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <x-button color="outline" class="!py-1 !px-3 text-branding border-white/20 bg-transparent hover:bg-white/10 rounded-xl">Create</x-button>
                </div>
                <p class="text-branding-wide text-primary-soft opacity-80 mb-1">Project Portfolio</p>
                <h3 class="text-4xl font-black text-white tracking-tight leading-none">{{ $projects_count }}</h3>
            </div>
            <div class="absolute -right-10 -bottom-10 h-40 w-40 bg-white/5 rounded-full blur-3xl"></div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <!-- Main Feed -->
        <div class="lg:col-span-2 space-y-10">
            <x-card variant="premium" class="p-10 rounded-card-xl shadow-xl shadow-gray-100/40 relative">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-10">
                    <div>
                        <h4 class="text-2xl font-black text-gray-900 tracking-tight">Financial Protocol Feed</h4>
                        <p class="text-branding-wide text-brand-muted mt-1">Real-time ledger updates</p>
                    </div>
                    <x-button color="outline" class="rounded-2xl px-6 text-branding border-gray-100 hover:bg-gray-50 flex items-center">
                        Execute Export
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                    </x-button>
                </div>

                <div class="space-y-4">
                    @forelse($recent_documents as $invoice)
                        <div class="group p-6 rounded-[2rem] bg-gray-50/50 hover:bg-white hover:shadow-2xl hover:scale-[1.01] transition-all border border-gray-100/50 hover:border-indigo-100 flex flex-col md:flex-row justify-between items-center gap-6">
                            <div class="flex items-center gap-6">
                                <div class="h-14 w-14 bg-white rounded-2xl flex items-center justify-center text-indigo-600 shadow-sm border border-gray-100 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-black text-gray-900 tracking-tight">{{ $invoice->client->name ?? 'External Entity' }}</p>
                                    <div class="flex items-center gap-3 mt-1">
                                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">INV-{{ substr($invoice->id, 0, 8) }}</span>
                                        <span class="text-gray-200 text-xs">|</span>
                                        <span class="text-[10px] font-bold text-gray-400">{{ $invoice->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-8">
                                <div class="text-right">
                                    <p class="text-lg font-black text-gray-900">${{ number_format($invoice->total_amount, 2) }}</p>
                                    <p class="text-branding text-success">Verified Payment</p>
                                </div>
                                <div class="h-10 w-1 bg-gray-100 rounded-full"></div>
                                <x-button color="outline" class="rounded-xl !py-2 !px-4 text-[9px] font-black uppercase border-gray-200">Inspect</x-button>
                            </div>
                        </div>
                    @empty
                        <div class="py-24 text-center">
                            <p class="text-[10px] font-black text-gray-300 uppercase tracking-[0.3em]">No activity detected in current cycle</p>
                        </div>
                    @endforelse
                </div>
            </x-card>
        </div>

        <!-- Sidebar / Intelligence -->
        <div class="space-y-10">
            <x-card variant="brand" class="p-8 shadow-indigo-100/20 shadow-xl">
                <div class="relative z-10">
                    <h4 class="text-xl font-black tracking-tight mb-8">System Analytics</h4>
                    <div class="space-y-8">
                        <div>
                            <div class="flex justify-between items-center mb-3">
                                <label class="text-branding text-primary-soft opacity-80">Resource Load</label>
                                <span class="text-[10px] font-black">78%</span>
                            </div>
                            <div class="w-full bg-white/10 h-2 rounded-full overflow-hidden">
                                <div class="h-full bg-white rounded-full w-[78%]"></div>
                            </div>
                        </div>
                        
                        <div>
                            <div class="flex justify-between items-center mb-3">
                                <label class="text-[10px] font-black text-indigo-200 uppercase tracking-widest">Storage Protocol</label>
                                <span class="text-[10px] font-black text-amber-300">CRITICAL</span>
                            </div>
                            <div class="w-full bg-white/10 h-2 rounded-full overflow-hidden">
                                <div class="h-full bg-amber-400 rounded-full w-[94%]"></div>
                            </div>
                        </div>

                        <div class="pt-8 border-t border-white/10">
                            <p class="text-branding-wide text-primary-soft opacity-80 mb-4">Core Operative Status</p>
                            <div class="flex items-center gap-3">
                                <div class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse shadow-[0_0_10px_rgba(52,211,153,0.5)]"></div>
                                <span class="text-xs font-black uppercase tracking-widest">All Modules Optimized</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Decorative Elements -->
                <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2 blur-3xl"></div>
            </x-card>

            <x-card variant="premium" class="p-8">
                <h4 class="text-lg font-black text-gray-900 tracking-tight mb-8">Strategic Shortcuts</h4>
                <div class="grid grid-cols-2 gap-4">
                    <x-button color="outline" class="rounded-2xl !py-6 !px-4 border-gray-50 bg-gray-50/30 hover:bg-white hover:shadow-lg hover:border-indigo-100 transition-all group flex flex-col items-center gap-3">
                        <div class="h-10 w-10 rounded-2xl bg-white shadow-sm flex items-center justify-center text-indigo-500 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <span class="text-branding text-brand-muted group-hover:text-primary">Add Lead</span>
                    </x-button>
                    
                    <x-button color="outline" class="rounded-2xl !py-6 !px-4 border-gray-50 bg-gray-50/30 hover:bg-white hover:shadow-lg hover:border-indigo-100 transition-all group flex flex-col items-center gap-3">
                        <div class="h-10 w-10 rounded-2xl bg-white shadow-sm flex items-center justify-center text-amber-500 group-hover:bg-amber-600 group-hover:text-white transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <span class="text-[9px] font-black uppercase text-gray-400 tracking-widest group-hover:text-amber-600">Invoice</span>
                    </x-button>
                </div>
            </x-card>
        </div>
    </div>
</x-app-container>