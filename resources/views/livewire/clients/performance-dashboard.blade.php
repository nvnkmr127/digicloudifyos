<div class="p-8">
    <div class="mb-10 flex justify-between items-end">
        <div>
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3 text-branding text-gray-400">
                    <li><a href="{{ route('clients.index') }}" class="hover:text-primary transition">Clients</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li class="text-gray-900">Portfolio Performance</li>
                </ol>
            </nav>
            <h1 class="text-4xl font-black text-gray-900 tracking-tight">Agency Command Center</h1>
            <p class="text-gray-500 mt-2 font-medium">Global cross-client marketing efficiency and ROI tracker</p>
        </div>
        <div class="flex gap-4">
            <div class="inline-flex rounded-2xl shadow-sm bg-white border border-gray-100 p-1">
                <button wire:click="$set('dateRange', 7)" class="px-6 py-2 rounded-xl text-branding {{ $dateRange == 7 ? 'bg-gray-900 text-white shadow-lg' : 'text-gray-400 hover:text-gray-900' }} transition-all">7D</button>
                <button wire:click="$set('dateRange', 30)" class="px-6 py-2 rounded-xl text-branding {{ $dateRange == 30 ? 'bg-gray-900 text-white shadow-lg' : 'text-gray-400 hover:text-gray-900' }} transition-all">30D</button>
                <button wire:click="$set('dateRange', 90)" class="px-6 py-2 rounded-xl text-branding {{ $dateRange == 90 ? 'bg-gray-900 text-white shadow-lg' : 'text-gray-400 hover:text-gray-900' }} transition-all">90D</button>
            </div>
        </div>
    </div>

    <!-- Global Portfolio Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
        <x-card variant="premium" class="group relative overflow-hidden">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-primary-soft rounded-full opacity-0 group-hover:opacity-100 transition-all"></div>
            <p class="text-branding text-brand-muted mb-2 relative z-10">Total Managed Spend</p>
            <h3 class="text-4xl font-black text-brand-black tracking-tight relative z-10">${{ number_format($clients->sum('spend'), 2) }}</h3>
            <div class="mt-6 flex items-center text-branding text-primary">
                <span>Across {{ $clients->count() }} active clients</span>
            </div>
        </x-card>

        <x-card variant="brand" class="group relative overflow-hidden">
            <div class="absolute right-0 top-0 w-32 h-32 bg-white opacity-5 rounded-full -mr-16 -mt-16"></div>
            <p class="text-branding opacity-60 mb-2">Portfolio Total Leads</p>
            <h3 class="text-4xl font-black tracking-tight">{{ number_format($clients->sum('leads')) }}</h3>
            <div class="mt-6 flex items-center text-branding opacity-80">
                <span>Aggregated Performance</span>
            </div>
        </x-card>

        <x-card variant="premium" class="group relative overflow-hidden">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-purple-50 rounded-full opacity-0 group-hover:opacity-100 transition-all"></div>
            <p class="text-branding text-brand-muted mb-2 relative z-10">Portfolio Avg CPL</p>
            @php
                $totalSpend = $clients->sum('spend');
                $totalLeads = $clients->sum('leads');
                $avgCpl = $totalLeads > 0 ? $totalSpend / $totalLeads : 0;
            @endphp
            <h3 class="text-4xl font-black text-brand-black tracking-tight relative z-10">${{ number_format($avgCpl, 2) }}</h3>
            <div class="mt-6 flex items-center text-branding text-purple-600">
                <span>Agency Target: < $15.00</span>
            </div>
        </x-card>
    </div>

    <!-- Client Performance Table -->
    <x-card variant="default" class="rounded-card overflow-hidden mb-12 p-0 border-none shadow-sm">
        <div class="px-8 py-6 border-b border-gray-50 flex justify-between items-center bg-brand-light/50">
            <h2 class="text-branding-wide text-brand-muted">Client Acquisition Matrix</h2>
            <div class="text-branding text-brand-muted">
                Sync Status: <span class="text-success">Active</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-50">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-8 py-6 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Business Entity</th>
                        <th class="px-8 py-6 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Spend</th>
                        <th class="px-8 py-6 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Global Leads</th>
                        <th class="px-8 py-6 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Blended CPL</th>
                        <th class="px-8 py-6 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Portfolio ROAS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($clients as $client)
                        <tr class="hover:bg-gray-50/50 transition cursor-pointer">
                            <td class="px-8 py-6 whitespace-nowrap">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center font-black text-gray-400 text-xs">
                                        {{ substr($client['name'], 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="font-black text-gray-900 tracking-tight">{{ $client['name'] }}</div>
                                        <div class="text-[8px] font-black text-gray-400 uppercase tracking-widest mt-1">Direct Client</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-right whitespace-nowrap font-bold text-gray-700">
                                ${{ number_format($client['spend'], 2) }}
                            </td>
                            <td class="px-8 py-6 text-right whitespace-nowrap font-bold text-gray-900">
                                {{ number_format($client['leads']) }}
                            </td>
                            <td class="px-8 py-6 text-right whitespace-nowrap">
                                <span class="inline-flex px-3 py-1 rounded-full text-branding {{ $client['cpl'] < 20 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                                    ${{ number_format($client['cpl'], 2) }}
                                </span>
                            </td>
                            <td class="px-8 py-6 text-right whitespace-nowrap font-black text-indigo-600">
                                {{ number_format($client['roas'], 2) }}x
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
