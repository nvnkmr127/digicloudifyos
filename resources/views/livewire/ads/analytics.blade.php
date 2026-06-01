<x-app-container>
    <x-page-header title="Ads Analytics">
        <div class="inline-flex rounded-button border border-gray-200 bg-white p-1">
            <button
                type="button"
                wire:click="$set('dateRange', 7)"
                class="{{ $dateRange == 7 ? 'bg-primary-soft text-primary' : 'text-text-muted hover:text-text-primary' }} px-3 py-2 rounded-button text-sm font-semibold transition"
            >
                7D
            </button>
            <button
                type="button"
                wire:click="$set('dateRange', 30)"
                class="{{ $dateRange == 30 ? 'bg-primary-soft text-primary' : 'text-text-muted hover:text-text-primary' }} px-3 py-2 rounded-button text-sm font-semibold transition"
            >
                30D
            </button>
            <button
                type="button"
                wire:click="$set('dateRange', 90)"
                class="{{ $dateRange == 90 ? 'bg-primary-soft text-primary' : 'text-text-muted hover:text-text-primary' }} px-3 py-2 rounded-button text-sm font-semibold transition"
            >
                90D
            </button>
        </div>
    </x-page-header>

    <!-- Executive Summary Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <x-stat-card
            label="Spend"
            value="${{ number_format($overview->total_spend, 2) }}"
            trend="Avg daily: ${{ number_format($overview->total_spend / $dateRange, 1) }}"
        />

        <x-stat-card
            label="Leads"
            value="{{ number_format($totalLeads) }}"
            trend="Facebook sync"
        />

        <x-stat-card
            label="CPL"
            value="${{ $totalLeads > 0 ? number_format($overview->total_spend / $totalLeads, 2) : '0.00' }}"
            trend="Efficiency"
        />

        <x-stat-card
            label="ROAS"
            value="{{ number_format($overview->avg_roas ?: 0, 2) }}x"
            trend="Direct attribution"
        />
    </div>

    <!-- Campaign Performance Center -->
    <div class="mb-10 space-y-4">
        <x-section title="Campaign Performance" description="Spend, leads, and efficiency by campaign" />

        <x-card class="p-0 overflow-hidden">
            <x-table>
                <x-slot name="header">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left">Campaign</th>
                        <th scope="col" class="px-6 py-3 text-right">Spend</th>
                        <th scope="col" class="px-6 py-3 text-right">Leads</th>
                        <th scope="col" class="px-6 py-3 text-right">CPL</th>
                        <th scope="col" class="px-6 py-3 text-right">CTR</th>
                        <th scope="col" class="px-6 py-3 text-right">Conversions</th>
                    </tr>
                </x-slot>

                @foreach($campaigns as $camp)
                    @php $cplVariant = ($camp['cpl'] ?? 0) < 20 ? 'success' : 'neutral'; @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="text-sm font-semibold text-text-primary">{{ $camp['name'] }}</div>
                        </td>
                        <td class="px-6 py-4 text-right text-sm text-text-primary">${{ number_format($camp['spend'], 2) }}</td>
                        <td class="px-6 py-4 text-right text-sm text-text-primary">{{ $camp['leads'] }}</td>
                        <td class="px-6 py-4 text-right">
                            <x-badge :variant="$cplVariant" size="xs">${{ number_format($camp['cpl'], 2) }}</x-badge>
                        </td>
                        <td class="px-6 py-4 text-right text-sm text-text-primary">{{ number_format($camp['ctr'], 2) }}%</td>
                        <td class="px-6 py-4 text-right text-sm text-text-primary">{{ number_format($camp['conversions']) }}</td>
                    </tr>
                @endforeach
            </x-table>
        </x-card>
    </div>

    <div class="mb-10 space-y-4">
        <x-section title="Creative Performance" description="CTR, CPL, engagement, and leads by creative asset" />

        <x-card class="p-0 overflow-hidden">
            <x-table>
                <x-slot name="header">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left">Creative</th>
                        <th scope="col" class="px-6 py-3 text-right">CTR</th>
                        <th scope="col" class="px-6 py-3 text-right">CPL</th>
                        <th scope="col" class="px-6 py-3 text-right">Engagement</th>
                        <th scope="col" class="px-6 py-3 text-right">Leads</th>
                    </tr>
                </x-slot>

                @foreach($creatives as $c)
                    @php $cplVariant = ($c['cpl'] ?? 0) < 15 ? 'success' : 'neutral'; @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if($c['image_url'])
                                    <img src="{{ $c['image_url'] }}" alt="{{ $c['asset_name'] }}" class="w-10 h-10 rounded-element object-cover bg-gray-50 border border-gray-100" />
                                @else
                                    <div class="w-10 h-10 rounded-element bg-primary-soft flex items-center justify-center text-primary">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 00-2 2z" />
                                        </svg>
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-text-primary truncate max-w-xs">{{ $c['asset_name'] }}</div>
                                    <div class="text-xs text-text-muted">{{ $c['video_id'] ? 'Video' : 'Image' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right text-sm text-text-primary">{{ number_format($c['ctr'], 2) }}%</td>
                        <td class="px-6 py-4 text-right">
                            <x-badge :variant="$cplVariant" size="xs">${{ number_format($c['cpl'], 2) }}</x-badge>
                        </td>
                        <td class="px-6 py-4 text-right text-sm text-text-primary">{{ number_format($c['engagement_rate'], 2) }}%</td>
                        <td class="px-6 py-4 text-right text-sm text-text-primary">{{ number_format($c['leads']) }}</td>
                    </tr>
                @endforeach
            </x-table>
        </x-card>
    </div>

    <!-- Demographic Intelligence -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- Age Performance -->
        <x-card>
            <x-section title="Age" description="Leads and CPL by age range" />
            <div class="mt-4 space-y-4">
                @php $maxAgeLeads = $ageStats->max('leads') ?: 1; @endphp
                @foreach($ageStats as $label => $stats)
                    @php $agePercent = ($stats['leads'] / $maxAgeLeads) * 100; @endphp
                    <div>
                        <div class="flex justify-between items-center text-sm text-text-muted mb-2">
                            <span class="font-semibold text-text-primary">{{ $label }}</span>
                            <div class="flex gap-4">
                                <span>{{ $stats['leads'] }} leads</span>
                                <span>CPL: ${{ number_format($stats['cpl'], 2) }}</span>
                            </div>
                        </div>
                        <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div x-data="{ p: @js($agePercent) }" class="h-full bg-primary rounded-full transition-all duration-500" :style="`width: ${p}%`"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>

        <x-card>
            <x-section title="Regions" description="Leads and CPL by region" />
            <div class="mt-4 space-y-4 max-h-[400px] overflow-y-auto pr-4 scrollbar-hide">
                @php $maxCityLeads = $cityStats->max('leads') ?: 1; @endphp
                @forelse($cityStats as $label => $stats)
                    @php $cityPercent = ($stats['leads'] / $maxCityLeads) * 100; @endphp
                    <div>
                        <div class="flex justify-between text-sm text-text-muted mb-2">
                            <span class="font-semibold text-text-primary">{{ $label }}</span>
                            <div class="flex gap-4">
                                <span>{{ $stats['leads'] }} leads</span>
                                <span>CPL: ${{ number_format($stats['cpl'], 2) }}</span>
                            </div>
                        </div>
                        <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div x-data="{ p: @js($cityPercent) }" class="h-full bg-primary rounded-full transition-all duration-500" :style="`width: ${p}%`"></div>
                        </div>
                    </div>
                @empty
                    <x-empty-state title="No regional data yet" description="Regional breakdown appears after the next sync." />
                @endforelse
            </div>
        </x-card>

        <!-- Gender Performance -->
        <x-card>
            <x-section title="Gender" description="Lead distribution by gender" />
            <div class="flex items-center justify-center py-8">
                @php
                    $totalGenderLeads = $genderStats->sum('leads') ?: 1;
                    $femaleLeads = $genderStats->get('female', ['leads' => 0])['leads'];
                    $maleLeads = $genderStats->get('male', ['leads' => 0])['leads'];
                @endphp
                <div class="relative w-48 h-48">
                    <svg viewBox="0 0 36 36" class="w-full h-full transform -rotate-90">
                        <circle class="text-gray-100" cx="18" cy="18" r="15.915" fill="none" stroke="currentColor" stroke-width="3"></circle>
                        <circle class="text-primary" cx="18" cy="18" r="15.915" fill="none" stroke="currentColor" stroke-width="3"
                            stroke-dasharray="{{ ($femaleLeads / $totalGenderLeads) * 100 }} 100" stroke-dashoffset="0">
                        </circle>
                        <circle class="text-secondary" cx="18" cy="18" r="15.915" fill="none" stroke="currentColor" stroke-width="3"
                            stroke-dasharray="{{ ($maleLeads / $totalGenderLeads) * 100 }} 100"
                            stroke-dashoffset="-{{ ($femaleLeads / $totalGenderLeads) * 100 }}"></circle>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center flex-col">
                        <span class="text-2xl font-semibold text-text-primary">{{ number_format($genderStats->sum('leads')) }}</span>
                        <span class="text-xs text-text-muted">Total leads</span>
                    </div>
                </div>
            </div>
            <div class="flex justify-around mt-6 text-sm text-text-muted">
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-primary"></div>
                    <span class="font-semibold text-text-primary">Female</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-secondary"></div>
                    <span class="font-semibold text-text-primary">Male</span>
                </div>
            </div>
        </x-card>

        <!-- Global Device & Placement Breakdown -->
        <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-card>
                <x-section title="Devices" description="Leads and CPL by device" />
                <div class="mt-4 space-y-3">
                    @foreach($deviceStats as $label => $stats)
                        <div class="p-4 bg-gray-50 rounded-card border border-gray-100 flex items-center justify-between gap-4">
                            <div class="text-sm font-semibold text-text-primary">{{ $label }}</div>
                            <div class="text-right text-sm text-text-muted">
                                <div class="font-semibold text-text-primary">{{ $stats['leads'] }} leads</div>
                                <div>CPL: ${{ number_format($stats['cpl'], 2) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>

            <x-card>
                <x-section title="Placements" description="Leads and CPL by placement" />
                <div class="mt-4 space-y-3">
                    @foreach($placementStats as $label => $stats)
                        <div class="p-4 bg-gray-50 rounded-card border border-gray-100 flex items-center justify-between gap-4">
                            <div class="text-sm font-semibold text-text-primary">{{ $label }}</div>
                            <div class="text-right text-sm text-text-muted">
                                <div class="font-semibold text-text-primary">{{ $stats['leads'] }} leads</div>
                                <div>CPL: ${{ number_format($stats['cpl'], 2) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>
        </div>
    </div>
</x-app-container>
