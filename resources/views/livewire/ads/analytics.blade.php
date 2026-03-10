<div class="p-8">
    <div class="mb-10 flex justify-between items-end">
        <div>
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol
                    class="inline-flex items-center space-x-1 md:space-x-3 text-[10px] font-black uppercase tracking-widest text-gray-400">
                    <li><a href="{{ route('ads.index') }}" class="hover:text-primary transition">Ads</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li class="text-gray-900">Analytics Intelligence</li>
                </ol>
            </nav>
            <h1 class="text-4xl font-black text-gray-900 tracking-tight">Performance Command Center</h1>
            <p class="text-gray-500 mt-2 font-medium">Real-time cross-platform marketing intelligence and attribution
            </p>
        </div>
        <div class="flex gap-4">
            <div class="inline-flex rounded-2xl shadow-sm bg-white border border-gray-100 p-1">
                <button wire:click="$set('dateRange', 7)"
                    class="px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest {{ $dateRange == 7 ? 'bg-gray-900 text-white shadow-lg' : 'text-gray-400 hover:text-gray-900' }} transition-all">7D</button>
                <button wire:click="$set('dateRange', 30)"
                    class="px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest {{ $dateRange == 30 ? 'bg-gray-900 text-white shadow-lg' : 'text-gray-400 hover:text-gray-900' }} transition-all">30D</button>
                <button wire:click="$set('dateRange', 90)"
                    class="px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest {{ $dateRange == 90 ? 'bg-gray-900 text-white shadow-lg' : 'text-gray-400 hover:text-gray-900' }} transition-all">90D</button>
            </div>
        </div>
    </div>

    <!-- Executive Summary Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
        <div
            class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm transition hover:shadow-xl group relative overflow-hidden">
            <div
                class="absolute -right-4 -top-4 w-24 h-24 bg-indigo-50 rounded-full opacity-0 group-hover:opacity-100 transition-all">
            </div>
            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2 relative z-10">Total
                Advertising Spend</p>
            <h3 class="text-4xl font-black text-gray-900 tracking-tight relative z-10">
                ${{ number_format($overview->total_spend, 2) }}</h3>
            <div class="mt-6 flex items-center text-[10px] font-black uppercase tracking-widest text-indigo-600">
                <span>Avg Daily: ${{ number_format($overview->total_spend / $dateRange, 1) }}</span>
            </div>
        </div>

        <div
            class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm transition hover:shadow-xl group relative overflow-hidden">
            <div
                class="absolute -right-4 -top-4 w-24 h-24 bg-green-50 rounded-full opacity-0 group-hover:opacity-100 transition-all">
            </div>
            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2 relative z-10">Verified Leads
                Captured</p>
            <h3 class="text-4xl font-black text-gray-900 tracking-tight relative z-10">{{ number_format($totalLeads) }}
            </h3>
            <div class="mt-6 flex items-center text-[10px] font-black uppercase tracking-widest text-green-600">
                <span>Source: Facebook Sync</span>
            </div>
        </div>

        <div class="bg-indigo-600 p-8 rounded-[2.5rem] shadow-xl text-white group relative overflow-hidden">
            <div class="absolute right-0 top-0 w-32 h-32 bg-white opacity-5 rounded-full -mr-16 -mt-16"></div>
            <p class="text-[10px] font-black uppercase tracking-widest opacity-60 mb-2">Cost Per Lead (CPL)</p>
            <h3 class="text-4xl font-black tracking-tight">
                ${{ $totalLeads > 0 ? number_format($overview->total_spend / $totalLeads, 2) : '0.00' }}</h3>
            <div class="mt-6 flex items-center text-[10px] font-black uppercase tracking-widest opacity-80">
                <span>Efficiency Metric</span>
            </div>
        </div>

        <div
            class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm transition hover:shadow-xl group relative overflow-hidden">
            <div
                class="absolute -right-4 -top-4 w-24 h-24 bg-purple-50 rounded-full opacity-0 group-hover:opacity-100 transition-all">
            </div>
            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2 relative z-10">Purchase ROAS
                (Avg)</p>
            <h3 class="text-4xl font-black text-gray-900 tracking-tight relative z-10">
                {{ number_format($overview->avg_roas ?: 0, 2) }}x
            </h3>
            <div class="mt-6 flex items-center text-[10px] font-black uppercase tracking-widest text-purple-600">
                <span>Direct Attribution</span>
            </div>
        </div>
    </div>

    <!-- Campaign Performance Center -->
    <div class="mb-12">
        <div class="flex items-center gap-4 mb-8">
            <div class="h-px bg-gray-100 flex-1"></div>
            <h2 class="text-[10px] font-black uppercase tracking-[0.3em] text-gray-400">Campaign Performance Attribution
            </h2>
            <div class="h-px bg-gray-100 flex-1"></div>
        </div>

        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-50">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th
                                class="px-8 py-6 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                Campaign Name</th>
                            <th
                                class="px-8 py-6 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                Spend</th>
                            <th
                                class="px-8 py-6 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                Leads</th>
                            <th
                                class="px-8 py-6 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                CPL</th>
                            <th
                                class="px-8 py-6 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                CTR</th>
                            <th
                                class="px-8 py-6 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                Conversions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($campaigns as $camp)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <div class="font-black text-gray-900 tracking-tight">{{ $camp['name'] }}</div>
                                </td>
                                <td class="px-8 py-6 text-right whitespace-nowrap font-bold text-gray-700">
                                    ${{ number_format($camp['spend'], 2) }}</td>
                                <td class="px-8 py-6 text-right whitespace-nowrap font-bold text-gray-700">
                                    {{ $camp['leads'] }}
                                </td>
                                <td class="px-8 py-6 text-right whitespace-nowrap">
                                    <span
                                        class="inline-flex px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $camp['cpl'] < 20 ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                        ${{ number_format($camp['cpl'], 2) }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-right whitespace-nowrap font-black text-indigo-600">
                                    {{ number_format($camp['ctr'], 2) }}%
                                </td>
                                <td class="px-8 py-6 text-right whitespace-nowrap font-black text-gray-900">
                                    {{ number_format($camp['conversions']) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mb-12">
        <div class="flex items-center gap-4 mb-8">
            <div class="h-px bg-gray-100 flex-1"></div>
            <h2 class="text-[10px] font-black uppercase tracking-[0.3em] text-gray-400">Creative Performance
                Intelligence</h2>
            <div class="h-px bg-gray-100 flex-1"></div>
        </div>

        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-50">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th
                                class="px-8 py-6 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                Creative Asset</th>
                            <th
                                class="px-8 py-6 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                CTR</th>
                            <th
                                class="px-8 py-6 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                CPL</th>
                            <th
                                class="px-8 py-6 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                Engagement</th>
                            <th
                                class="px-8 py-6 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                Leads</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($creatives as $c)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <div class="flex items-center gap-4">
                                        @if($c['image_url'])
                                            <img src="{{ $c['image_url'] }}"
                                                class="w-12 h-12 rounded-xl object-cover shadow-sm bg-gray-50" />
                                        @else
                                            <div
                                                class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-400">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path
                                                        d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 00-2 2z">
                                                </svg>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-black text-gray-900 tracking-tight truncate max-w-xs">
                                                {{ $c['asset_name'] }}</div>
                                            <div class="text-[8px] font-black text-gray-400 uppercase tracking-widest mt-1">
                                                {{ $c['video_id'] ? 'Video Asset' : 'Image Asset' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right whitespace-nowrap font-black text-indigo-600">
                                    {{ number_format($c['ctr'], 2) }}%
                                </td>
                                <td class="px-8 py-6 text-right whitespace-nowrap">
                                    <span
                                        class="inline-flex px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $c['cpl'] < 15 ? 'bg-green-50 text-green-700' : 'bg-gray-50 text-gray-500' }}">
                                        ${{ number_format($c['cpl'], 2) }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-right whitespace-nowrap font-bold text-gray-600">
                                    {{ number_format($c['engagement_rate'], 2) }}%
                                </td>
                                <td class="px-8 py-6 text-right whitespace-nowrap">
                                    <div class="text-xs font-black text-gray-900 tracking-tight">
                                        {{ number_format($c['leads']) }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Demographic Intelligence -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- Age Performance -->
        <div class="bg-white p-10 rounded-[2.5rem] border border-gray-100 shadow-sm">
            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em] mb-10">Age Performance (Leads &
                CPL)</h4>
            <div class="space-y-6">
                @php $maxAgeLeads = $ageStats->max('leads') ?: 1; @endphp
                @foreach($ageStats as $label => $stats)
                    <div>
                        <div class="flex justify-between text-[10px] font-black uppercase tracking-widest mb-2">
                            <span>{{ $label }}</span>
                            <div class="flex gap-4">
                                <span class="text-indigo-600">{{ $stats['leads'] }} Leads</span>
                                <span class="text-gray-400">CPL: ${{ number_format($stats['cpl'], 2) }}</span>
                            </div>
                        </div>
                        <div class="h-3 bg-gray-50 rounded-full overflow-hidden flex">
                            <div class="h-full bg-indigo-500 rounded-full transition-all duration-1000"
                                style="width: {{ ($stats['leads'] / $maxAgeLeads) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white p-10 rounded-[2.5rem] border border-gray-100 shadow-sm">
            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em] mb-10">Regional Reach (Leads)
            </h4>
            <div class="space-y-4 max-h-[400px] overflow-y-auto pr-4 scrollbar-hide">
                @php $maxCityLeads = $cityStats->max('leads') ?: 1; @endphp
                @forelse($cityStats as $label => $stats)
                    <div class="group">
                        <div
                            class="flex justify-between text-[9px] font-black uppercase tracking-widest mb-1.5 text-gray-500 group-hover:text-indigo-600 transition">
                            <span>{{ $label }}</span>
                            <div class="flex gap-4">
                                <span>{{ $stats['leads'] }} Leads</span>
                                <span class="text-gray-400">CPL: ${{ number_format($stats['cpl'], 2) }}</span>
                            </div>
                        </div>
                        <div class="h-1.5 bg-gray-50 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-400 rounded-full transition-all duration-1000"
                                style="width: {{ ($stats['leads'] / $maxCityLeads) * 100 }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-[10px] text-gray-400 italic font-black uppercase tracking-widest text-center py-20">No
                        regional data synced yet</p>
                @endforelse
            </div>
        </div>

        <!-- Gender Performance -->
        <div class="bg-white p-10 rounded-[2.5rem] border border-gray-100 shadow-sm">
            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em] mb-10">Gender Intelligence</h4>
            <div class="flex items-center justify-center py-8">
                @php
                    $totalGenderSpend = $genderStats->sum() ?: 1;
                    $femaleSpend = $genderStats->get('female', 0);
                    $maleSpend = $genderStats->get('male', 0);
                    $otherSpend = $totalGenderSpend - $femaleSpend - $maleSpend;
                @endphp
                <div class="relative w-48 h-48">
                    <svg viewBox="0 0 36 36" class="w-full h-full transform -rotate-90">
                        <circle cx="18" cy="18" r="15.915" fill="none" stroke="#f3f4f6" stroke-width="3"></circle>
                        <circle cx="18" cy="18" r="15.915" fill="none" stroke="#6366f1" stroke-width="3"
                            stroke-dasharray="{{ ($femaleSpend / $totalGenderSpend) * 100 }} 100" stroke-dashoffset="0">
                        </circle>
                        <circle cx="18" cy="18" r="15.915" fill="none" stroke="#a855f7" stroke-width="3"
                            stroke-dasharray="{{ ($maleSpend / $totalGenderSpend) * 100 }} 100"
                            stroke-dashoffset="-{{ ($femaleSpend / $totalGenderSpend) * 100 }}"></circle>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center flex-col">
                        <span
                            class="text-2xl font-black text-gray-900">{{ number_format($genderStats->sum('leads')) }}</span>
                        <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest">Total Leads</span>
                    </div>
                </div>
            </div>
            <div class="flex justify-around mt-6">
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-indigo-500"></div>
                    <span class="text-[10px] font-black text-gray-600 uppercase tracking-widest">Female</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-purple-500"></div>
                    <span class="text-[10px] font-black text-gray-600 uppercase tracking-widest">Male</span>
                </div>
            </div>
        </div>

        <!-- Global Device & Placement Breakdown -->
        <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-12">
            <div class="space-y-4">
                <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em] mb-8">Device Attribution
                    Profile</h4>
                @foreach($deviceStats as $label => $stats)
                    <div class="bg-gray-50 p-6 rounded-3xl border border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-2 h-2 rounded-full bg-indigo-400"></div>
                            <span class="text-xs font-black uppercase tracking-widest text-gray-900">{{ $label }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-xl font-black text-gray-900 tracking-tight">{{ $stats['leads'] }} Leads</span>
                            <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest">CPL:
                                ${{ number_format($stats['cpl'], 2) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="space-y-4">
                <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em] mb-8">Placement Efficiency
                    Insight</h4>
                @foreach($placementStats as $label => $stats)
                    <div
                        class="bg-indigo-50/30 p-6 rounded-3xl border border-indigo-100 flex items-center justify-between group hover:bg-white hover:shadow-lg transition-all">
                        <div class="flex items-center gap-4">
                            <img src="https://ui-avatars.com/api/?name={{ $label or 'P' }}&background=6366f1&color=fff"
                                class="w-8 h-8 rounded-xl shadow-sm" />
                            <span class="text-xs font-black uppercase tracking-widest text-gray-900">{{ $label }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-xl font-black text-indigo-600 tracking-tight">{{ $stats['leads'] }}
                                Leads</span>
                            <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest">CPL:
                                ${{ number_format($stats['cpl'], 2) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>