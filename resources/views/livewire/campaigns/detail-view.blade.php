<div class="max-w-7xl mx-auto w-full">
    @if (session()->has('message'))
        <x-alert type="success" class="mb-4">
            {{ session('message') }}
        </x-alert>
    @endif

    @if (session()->has('error'))
        <x-alert type="error" class="mb-4">
            {{ session('error') }}
        </x-alert>
    @endif

    <x-page-header title="Campaign">
        @if($campaign->adAccount)
            <x-button variant="outline" wire:click="syncMetrics" wire:loading.attr="disabled">
                Force Sync
            </x-button>

            @if($campaign->status !== 'INACTIVE')
                <x-button variant="outline" wire:click="pauseCampaign" wire:loading.attr="disabled">
                    Pause
                </x-button>
            @else
                <x-button variant="primary" wire:click="pauseCampaign" wire:loading.attr="disabled">
                    Resume
                </x-button>
            @endif

            <x-button variant="outline" wire:click="archiveCampaign" wire:loading.attr="disabled">
                Archive
            </x-button>

            <x-button
                variant="danger"
                wire:click="deleteCampaign"
                wire:loading.attr="disabled"
                wire:confirm="Are you sure you want to delete this campaign permanently from both DC OS and Meta?"
            >
                Delete
            </x-button>
        @endif
    </x-page-header>

    <x-card class="mb-6">
        <div class="flex items-start justify-between gap-6">
            <div class="min-w-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-primary-soft rounded-element flex items-center justify-center text-primary font-semibold">
                        {{ substr($campaign->name, 0, 1) }}
                    </div>
                    <div class="min-w-0">
                        <div class="text-xl font-semibold text-text-primary truncate">{{ $campaign->name }}</div>
                        <div class="text-sm text-text-muted">
                            ID: {{ substr($campaign->id, 0, 8) }} · {{ $campaign->adAccount?->platform ?? 'Internal' }}
                        </div>
                    </div>
                </div>
            </div>
            @php
                $statusValue = $campaign->status instanceof \App\Enums\CampaignStatus ? $campaign->status->value : (string) ($campaign->status ?? '');
                $status = strtoupper($statusValue);
                $statusVariant = match ($status) {
                    'ACTIVE' => 'success',
                    'INACTIVE' => 'warning',
                    'ARCHIVED' => 'neutral',
                    default => 'neutral',
                };
            @endphp
            <x-badge :variant="$statusVariant" size="xs">
                {{ $campaign->status instanceof \App\Enums\CampaignStatus ? $campaign->status->label() : $campaign->status }}
            </x-badge>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6 pt-6 border-t border-gray-100">
            <div>
                <div class="text-xs font-semibold text-text-muted">Client</div>
                <div class="text-sm font-semibold text-text-primary mt-1">{{ $campaign->client->name }}</div>
            </div>
            <div>
                <div class="text-xs font-semibold text-text-muted">Ad Account</div>
                <div class="text-sm text-text-primary mt-1">
                    @if($campaign->adAccount)
                        {{ $campaign->adAccount->account_name }}
                    @else
                        <span class="text-text-muted">Not linked</span>
                    @endif
                </div>
            </div>
            <div>
                <div class="text-xs font-semibold text-text-muted">Budget</div>
                <div class="text-sm font-semibold text-text-primary mt-1">{{ $campaign->getFormattedBudgetAttribute() }}</div>
            </div>
            <div>
                <div class="text-xs font-semibold text-text-muted">Timeline</div>
                <div class="text-sm text-text-primary mt-1">
                    {{ $campaign->start_date ? $campaign->start_date->format('M d') : 'TBD' }}
                    @if($campaign->end_date)
                        - {{ $campaign->end_date->format('M d, Y') }}
                    @endif
                </div>
            </div>
        </div>
    </x-card>

    <x-toolbar class="mb-6" variant="subtle">
        <x-slot name="left">
            <div class="inline-flex rounded-button border border-gray-200 bg-white p-1">
                <button
                    type="button"
                    wire:click="setTab('creative')"
                    class="{{ $activeTab === 'creative' ? 'bg-primary-soft text-primary' : 'text-text-muted hover:text-text-primary' }} px-3 py-2 rounded-button text-sm font-semibold transition"
                >
                    Creative
                </button>
                <button
                    type="button"
                    wire:click="setTab('adsets')"
                    class="{{ $activeTab === 'adsets' ? 'bg-primary-soft text-primary' : 'text-text-muted hover:text-text-primary' }} px-3 py-2 rounded-button text-sm font-semibold transition"
                >
                    Ad Sets
                </button>
                <button
                    type="button"
                    wire:click="setTab('performance')"
                    class="{{ $activeTab === 'performance' ? 'bg-primary-soft text-primary' : 'text-text-muted hover:text-text-primary' }} px-3 py-2 rounded-button text-sm font-semibold transition"
                >
                    Performance
                </button>
                <button
                    type="button"
                    wire:click="setTab('audience')"
                    class="{{ $activeTab === 'audience' ? 'bg-primary-soft text-primary' : 'text-text-muted hover:text-text-primary' }} px-3 py-2 rounded-button text-sm font-semibold transition"
                >
                    Audience
                </button>
                <button
                    type="button"
                    wire:click="setTab('leads')"
                    class="{{ $activeTab === 'leads' ? 'bg-primary-soft text-primary' : 'text-text-muted hover:text-text-primary' }} px-3 py-2 rounded-button text-sm font-semibold transition"
                >
                    Leads
                </button>
            </div>
        </x-slot>
    </x-toolbar>

            @if($activeTab === 'creative')
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2">
                        <x-card>
                            <x-section title="Creative Requests" description="Assets and briefs for this campaign">
                                <x-slot name="actions">
                                    <x-button variant="outline" size="sm" href="{{ route('creative-requests.index') }}" wire:navigate>
                                        New Request
                                    </x-button>
                                </x-slot>
                            </x-section>

                            <div class="mt-4 space-y-3">
                                @forelse($campaign->creativeRequests as $request)
                                    @php
                                        $reqStatus = strtolower($request->status ?? '');
                                        $reqVariant = match ($reqStatus) {
                                            'approved', 'done', 'completed' => 'success',
                                            'in_progress', 'in progress' => 'info',
                                            'blocked' => 'danger',
                                            default => 'neutral',
                                        };
                                    @endphp
                                    <div class="flex items-center justify-between gap-4 p-3 bg-gray-50 rounded-element border border-gray-100">
                                        <div class="min-w-0">
                                            <div class="text-sm font-semibold text-text-primary truncate">{{ $request->title }}</div>
                                            <div class="text-xs text-text-muted">Type: {{ $request->type }}</div>
                                        </div>
                                        <x-badge :variant="$reqVariant" size="xs">
                                            {{ $request->status }}
                                        </x-badge>
                                    </div>
                                @empty
                                    <x-empty-state title="No creative requests yet" description="Create a request to start the creative workflow for this campaign." />
                                @endforelse
                            </div>
                        </x-card>
                    </div>

                    <div>
                        <x-card>
                            <x-section title="Open Tasks" description="Tasks linked to this campaign" />
                            <div class="mt-4 space-y-3">
                                @forelse($campaign->tasks->where('status', '!=', 'completed') as $task)
                                    <div class="flex items-start justify-between gap-4 p-3 bg-gray-50 rounded-element border border-gray-100">
                                        <div class="min-w-0">
                                            <div class="text-sm font-semibold text-text-primary truncate">{{ $task->title }}</div>
                                            <div class="text-xs text-text-muted">
                                                Due {{ $task->deadline ? $task->deadline->format('M d') : 'Today' }}
                                            </div>
                                        </div>
                                        <x-badge variant="neutral" size="xs">
                                            {{ $task->status }}
                                        </x-badge>
                                    </div>
                                @empty
                                    <x-empty-state title="No open tasks" description="This campaign has no pending tasks." />
                                @endforelse
                            </div>
                        </x-card>
                    </div>
                </div>
            @elseif($activeTab === 'adsets')
                <div class="space-y-8">
                    <div class="grid grid-cols-1 gap-6">
                        @forelse($campaign->adSets as $adSet)
                            <div class="bg-white rounded-card border border-gray-100 shadow-sm overflow-hidden transition hover:border-gray-200"
                                x-data="{ expanded: false }">
                                <div class="p-6 flex items-center justify-between cursor-pointer" @click="expanded = !expanded">
                                    <div class="flex items-center space-x-6">
                                        <div
                                            class="h-10 w-10 bg-primary-soft rounded-element flex items-center justify-center text-primary font-semibold text-xs">
                                            Ad
                                        </div>
                                        <div>
                                            <h5 class="text-lg font-semibold text-text-primary">{{ $adSet->name }}
                                            </h5>
                                            <div
                                                class="flex items-center mt-1 space-x-3 text-sm text-text-muted">
                                                <span>ID: {{ $adSet->external_adset_id }}</span>
                                                <span class="text-gray-200">•</span>
                                                <span>{{ $adSet->status }}</span>
                                                <span class="text-gray-200">•</span>
                                                <span>DAILY: ${{ number_format((float) $adSet->daily_budget, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <span class="text-sm font-semibold text-text-muted mr-4">{{ $adSet->ads->count() }} Ads
                                            Active</span>
                                        <svg class="h-5 w-5 text-gray-400 transition" :class="expanded ? 'rotate-180' : ''"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>

                                <div x-show="expanded" x-transition class="bg-gray-50/50 border-t border-gray-100">
                                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                        @foreach($adSet->ads as $ad)
                                            <div
                                                class="bg-white p-5 rounded-card border border-gray-100 shadow-sm group transition hover:border-gray-200">
                                                <div class="flex justify-between items-start mb-4">
                                                    <div
                                                        class="h-8 w-8 bg-gray-50 rounded-element flex items-center justify-center text-xs font-semibold text-text-muted transition">
                                                        AD</div>
                                                    @php
                                                        $adStatus = strtoupper($ad->status ?? '');
                                                        $adVariant = match ($adStatus) {
                                                            'ACTIVE' => 'success',
                                                            'PAUSED' => 'warning',
                                                            default => 'neutral',
                                                        };
                                                    @endphp
                                                    <x-badge :variant="$adVariant" size="xs">{{ $ad->status }}</x-badge>
                                                </div>
                                                <div class="text-sm font-semibold text-text-primary line-clamp-1 mb-1">{{ $ad->name }}</div>
                                                <div class="text-xs font-semibold text-text-muted mb-4">
                                                    ID:
                                                    {{ $ad->external_ad_id }}
                                                </div>

                                                <div
                                                    class="pt-4 border-t border-gray-100 flex justify-between items-center text-sm text-text-muted">
                                                    <span>Creative</span>
                                                    <button type="button" wire:click="showAdPreview('{{ $ad->id }}')" class="text-primary hover:underline font-semibold text-sm">
                                                        Preview
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @empty
                            <x-card>
                                <x-empty-state title="No ad sets yet" description="Sync campaign data to import ad sets from Meta.">
                                    <x-slot name="actions">
                                        <x-button variant="outline" wire:click="syncMetrics" wire:loading.attr="disabled">
                                            Run Deep Sync
                                        </x-button>
                                    </x-slot>
                                </x-empty-state>
                            </x-card>
                        @endforelse
                    </div>
                </div>
            @elseif($activeTab === 'performance')
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                    <!-- Metric Cards -->
                    @php
                        $insights = $campaign->adInsights->where('level', 'campaign');
                    @endphp
                    <div class="lg:col-span-4 grid grid-cols-1 md:grid-cols-4 gap-6">
                        <x-stat-card label="Impressions" value="{{ number_format($insights->sum('impressions')) }}" />
                        <x-stat-card label="Clicks" value="{{ number_format($insights->sum('clicks')) }}" />
                        <x-stat-card label="Spend" value="${{ number_format((float) $insights->sum('spend'), 2) }}" />
                        <x-stat-card label="Conversions" value="{{ number_format($insights->sum('conversions')) }}" />
                    </div>

                    <x-card class="lg:col-span-4">
                        <x-section title="Metric History" description="Past 14 days activity (campaign level)" />
                        <div class="mt-6">
                        <div class="h-64 flex items-end space-x-2">
                            @php
                                $impressionsMax = $insights->max('impressions') ?: 1;
                            @endphp
                            @foreach($insights->sortBy('date')->take(-14) as $insight)
                                @php
                                    $barHeight = ($insight->impressions / $impressionsMax) * 100;
                                    $clicksHeight = ($insight->clicks / max($insight->impressions ?: 1, 1)) * 100;
                                @endphp
                                <div x-data="{ barHeight: @js($barHeight), clicksHeight: @js($clicksHeight) }" class="flex-1 bg-primary-soft rounded-t-xl relative group" :style="`height: ${barHeight}%`">
                                    <div
                                        class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 bg-gray-900 text-white text-[10px] font-black px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap z-50">
                                        {{ number_format($insight->impressions) }} Impr
                                    </div>
                                    <div class="absolute top-0 left-0 w-full bg-primary rounded-t-xl opacity-0 group-hover:opacity-100 transition" :style="`height: ${clicksHeight}%`">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        </div>
                    </x-card>
                </div>
            @elseif($activeTab === 'audience')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    @php
                        $demographics = $campaign->audienceInsights;
                    @endphp

                    <!-- Gender Breakdown -->
                    <x-card>
                        <x-section title="Gender" description="Distribution by impressions" />
                        <div class="mt-4 space-y-4">
                            @php
                                $genderData = $demographics->where('breakdown_type', 'gender')->groupBy('dimension_1')->map->sum('impressions');
                                $totalGender = $genderData->sum();
                            @endphp
                            @forelse($genderData as $gender => $count)
                                @php $genderPercent = ($count / max($totalGender, 1)) * 100; @endphp
                                <div>
                                    <div class="flex justify-between text-xs font-semibold mb-1 text-text-muted">
                                        <span>{{ $gender }}</span>
                                        <span>{{ number_format($genderPercent, 1) }}%</span>
                                    </div>
                                    <div class="h-2 bg-gray-50 rounded-full overflow-hidden">
                                        <div x-data="{ p: @js($genderPercent) }" class="h-full bg-primary rounded-full" :style="`width: ${p}%`"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-text-muted italic">No gender data available yet.</p>
                            @endforelse
                        </div>
                    </x-card>

                    <!-- Age Breakdown -->
                    <x-card>
                        <x-section title="Age" description="Distribution by impressions" />
                        <div class="mt-4 space-y-4">
                            @php
                                $ageData = $demographics->where('breakdown_type', 'age')->groupBy('dimension_1')->map->sum('impressions');
                                $totalAge = $ageData->sum();
                            @endphp
                            @forelse($ageData as $age => $count)
                                @php $agePercent = ($count / max($totalAge, 1)) * 100; @endphp
                                <div>
                                    <div
                                        class="flex justify-between text-xs font-semibold mb-1 text-text-muted">
                                        <span>{{ $age }}</span>
                                        <span>{{ number_format($count) }}</span>
                                    </div>
                                    <div class="h-1.5 bg-gray-50 rounded-full overflow-hidden">
                                        <div x-data="{ p: @js($agePercent) }" class="h-full bg-primary" :style="`width: ${p}%`"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-text-muted italic">No age data available yet.</p>
                            @endforelse
                        </div>
                    </x-card>

                    <!-- Placement Performance -->
                    <x-card class="md:col-span-2">
                        <x-section title="Placements" description="Spend and impressions by placement" />
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                            @php
                                $placementData = $demographics->where('breakdown_type', 'placement')->groupBy('dimension_1');
                            @endphp
                            @forelse($placementData as $placement => $pInsights)
                                <div class="p-4 bg-gray-50 rounded-card border border-gray-100">
                                    <p class="text-sm font-semibold text-text-primary mb-2">{{ $placement }}</p>
                                    <div class="flex items-baseline justify-between gap-3">
                                        <span class="text-lg font-semibold text-text-primary">{{ number_format($pInsights->sum('impressions')) }}</span>
                                        <span class="text-sm text-text-muted">impr</span>
                                    </div>
                                    <div class="mt-2 text-sm text-text-muted">
                                        ${{ number_format($pInsights->sum('spend'), 2) }} spent
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-3 text-center py-8">
                                    <p class="text-xs text-text-muted italic">No placement data available for this campaign.</p>
                                </div>
                            @endforelse
                        </div>
                    </x-card>
                </div>
            @elseif($activeTab === 'leads')
                <x-card class="p-0 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-4">
                        <x-section title="Leads" description="Captured leads for this campaign" />
                        <x-badge variant="primary" size="xs">{{ $campaign->facebookLeads->count() }} total</x-badge>
                    </div>

                    <x-table>
                        <x-slot name="header">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left">Lead</th>
                                <th scope="col" class="px-6 py-3 text-left">Contact</th>
                                <th scope="col" class="px-6 py-3 text-left">Form</th>
                                <th scope="col" class="px-6 py-3 text-right">Captured</th>
                            </tr>
                        </x-slot>

                        @forelse($campaign->facebookLeads as $lead)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 bg-primary-soft rounded-element flex items-center justify-center text-primary font-semibold">
                                            {{ substr($lead->full_name, 0, 1) }}
                                        </div>
                                        <div class="text-sm font-semibold text-text-primary">{{ $lead->full_name }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-text-primary">{{ $lead->email }}</div>
                                    <div class="text-xs text-text-muted">{{ $lead->phone }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-badge variant="neutral" size="xs">
                                        {{ $lead->form_name ?: 'Meta Lead Ads' }}
                                    </x-badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="text-sm text-text-primary">{{ $lead->created_at->format('M d') }}</div>
                                    <div class="text-xs text-text-muted">{{ $lead->created_at->format('H:i') }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6">
                                    <x-empty-state title="No leads yet" description="This campaign has not captured any leads yet." />
                                </td>
                            </tr>
                        @endforelse
                    </x-table>
                </x-card>
            @endif

            <!-- Ad Preview Modal -->
            <x-modal name="ad-preview-modal" wire:model="showAdModal">
                @if($selectedAd)
                    <div class="p-8">
                        <div class="flex justify-between items-start mb-6">
                            <h3 class="text-lg font-semibold text-text-primary">Ad Preview</h3>
                            <button type="button" wire:click="$set('showAdModal', false)" class="text-text-muted hover:text-text-primary">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <div class="space-y-6">
                            <div>
                                <label class="text-xs font-semibold text-text-muted uppercase">Ad Name</label>
                                <div class="text-sm font-semibold text-text-primary mt-1">{{ $selectedAd->name }}</div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs font-semibold text-text-muted uppercase">Status</label>
                                    <div class="mt-1">
                                        @php
                                            $adStatus = strtoupper($selectedAd->status ?? '');
                                            $adVariant = match ($adStatus) {
                                                'ACTIVE' => 'success',
                                                'PAUSED' => 'warning',
                                                default => 'neutral',
                                            };
                                        @endphp
                                        <x-badge :variant="$adVariant" size="xs">{{ $selectedAd->status }}</x-badge>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-text-muted uppercase">Platform ID</label>
                                    <div class="text-sm text-text-primary mt-1">{{ $selectedAd->external_ad_id ?: 'N/A' }}</div>
                                </div>
                            </div>
                            @if($selectedAd->adCreative)
                                <div class="border-t border-gray-100 pt-4">
                                    <h4 class="text-sm font-semibold text-text-primary mb-3">Creative Specifications</h4>
                                    <div class="space-y-4">
                                        @if($selectedAd->adCreative->image_url)
                                            <div class="rounded-card overflow-hidden border border-gray-100 bg-gray-50 flex items-center justify-center max-h-64">
                                                <img src="{{ $selectedAd->adCreative->image_url }}" alt="{{ $selectedAd->adCreative->name }}" class="object-contain max-h-64">
                                            </div>
                                        @elseif($selectedAd->adCreative->thumbnail_url)
                                            <div class="rounded-card overflow-hidden border border-gray-100 bg-gray-50 flex items-center justify-center max-h-64">
                                                <img src="{{ $selectedAd->adCreative->thumbnail_url }}" alt="{{ $selectedAd->adCreative->name }}" class="object-contain max-h-64">
                                            </div>
                                        @endif
                                        <div>
                                            <label class="text-xs font-semibold text-text-muted uppercase">Title / Headline</label>
                                            <div class="text-sm font-medium text-text-primary mt-1">{{ $selectedAd->adCreative->title ?: 'N/A' }}</div>
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold text-text-muted uppercase">Body text</label>
                                            <div class="text-sm text-text-primary mt-1 whitespace-pre-wrap">{{ $selectedAd->adCreative->body ?: 'N/A' }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="flex justify-end mt-8">
                            <x-button type="button" variant="outline" wire:click="$set('showAdModal', false)">Close</x-button>
                        </div>
                    </div>
                @endif
            </x-modal>
</div>
