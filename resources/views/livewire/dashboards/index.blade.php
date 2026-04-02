<x-app-container>
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <x-page-header title="Dashboard" />
        <div class="flex items-center gap-3">
            <x-input type="date" wire:model.live="date" class="w-44" />
            <a href="{{ route('dashboards.builder') }}" wire:navigate class="text-sm font-bold text-primary hover:text-indigo-900">
                Customize
            </a>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        @foreach($widgets as $w)
            @if(($w['type'] ?? '') === 'org_kpis')
                <x-card class="lg:col-span-2">
                    <div class="text-sm font-black text-gray-900">Org KPIs (30 days)</div>
                    <div class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-4">
                        <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                            <div class="text-xs text-gray-500">Clients</div>
                            <div class="text-xl font-black text-gray-900">{{ $counts['clients'] }}</div>
                        </div>
                        <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                            <div class="text-xs text-gray-500">Revenue</div>
                            <div class="text-xl font-black text-gray-900">{{ number_format((float) ($orgKpis['performance']['total_revenue'] ?? 0), 2) }}</div>
                        </div>
                        <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                            <div class="text-xs text-gray-500">Spend</div>
                            <div class="text-xl font-black text-gray-900">{{ number_format((float) ($orgKpis['performance']['total_spend'] ?? 0), 2) }}</div>
                        </div>
                        <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                            <div class="text-xs text-gray-500">ROI (est.)</div>
                            <div class="text-xl font-black text-gray-900">
                                @if(($orgKpis['performance']['roi_estimate'] ?? null) === null)
                                    —
                                @else
                                    {{ number_format((float) $orgKpis['performance']['roi_estimate'], 1) }}%
                                @endif
                            </div>
                        </div>
                    </div>
                </x-card>
            @endif

            @if(($w['type'] ?? '') === 'productivity')
                <x-card>
                    <div class="text-sm font-black text-gray-900">Productivity ({{ $date }})</div>
                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                            <div class="text-xs text-gray-500">Hours</div>
                            <div class="text-xl font-black text-gray-900">{{ number_format((float) $prodTotals['hours'], 2) }}</div>
                        </div>
                        <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                            <div class="text-xs text-gray-500">Billable</div>
                            <div class="text-xl font-black text-gray-900">{{ number_format((float) $prodTotals['billable_ratio'], 1) }}%</div>
                        </div>
                        <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                            <div class="text-xs text-gray-500">Tasks Done</div>
                            <div class="text-xl font-black text-gray-900">{{ (int) $prodTotals['tasks_completed'] }}</div>
                        </div>
                        <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                            <div class="text-xs text-gray-500">Overdue</div>
                            <div class="text-xl font-black text-gray-900">{{ (int) $prodTotals['overdue_tasks'] }}</div>
                        </div>
                    </div>
                </x-card>
            @endif

            @if(($w['type'] ?? '') === 'competitive')
                <x-card>
                    <div class="text-sm font-black text-gray-900">Competitive ({{ $date }})</div>
                    <div class="mt-4 space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">New competitor ads</span>
                            <span class="font-black text-gray-900">{{ $competitive['new_ads'] }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Open alerts</span>
                            <span class="font-black text-gray-900">{{ $competitive['open_alerts'] }}</span>
                        </div>
                        <div class="pt-2">
                            <a href="{{ route('clients.performance') }}" class="text-sm font-bold text-primary hover:text-indigo-900">View Client Performance</a>
                        </div>
                    </div>
                </x-card>
            @endif

            @if(($w['type'] ?? '') === 'playbooks')
                <x-card>
                    <div class="text-sm font-black text-gray-900">Playbooks ({{ $date }})</div>
                    <div class="mt-4 space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Runs today</span>
                            <span class="font-black text-gray-900">{{ $playbooks['runs_today'] }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Open playbook tasks</span>
                            <span class="font-black text-gray-900">{{ $playbooks['open_tasks'] }}</span>
                        </div>
                        <div class="pt-2">
                            <a href="{{ route('playbooks.index') }}" class="text-sm font-bold text-primary hover:text-indigo-900">View Playbooks</a>
                        </div>
                    </div>
                </x-card>
            @endif

            @if(($w['type'] ?? '') === 'seo_audit')
                <x-card>
                    <div class="text-sm font-black text-gray-900">SEO Audit ({{ $date }})</div>
                    <div class="mt-4 space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Critical/High issues</span>
                            <span class="font-black text-gray-900">{{ $seoAudit['critical_high_issues_today'] }}</span>
                        </div>
                        <div class="pt-2">
                            <a href="{{ route('seo.index') }}" class="text-sm font-bold text-primary hover:text-indigo-900">SEO Intelligence</a>
                        </div>
                    </div>
                </x-card>
            @endif

            @if(($w['type'] ?? '') === 'brand_kit')
                <x-card>
                    <div class="text-sm font-black text-gray-900">Brand Kits</div>
                    <div class="mt-4 space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Clients</span>
                            <span class="font-black text-gray-900">{{ $branding['clients'] }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Brand kits created</span>
                            <span class="font-black text-gray-900">{{ $branding['brand_kits'] }}</span>
                        </div>
                    </div>
                </x-card>
            @endif
        @endforeach
    </div>
</x-app-container>
