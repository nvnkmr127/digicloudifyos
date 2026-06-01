<x-app-container>
    <x-page-header title="Reports" />

    <x-toolbar class="mb-6" variant="subtle">
        <x-slot name="left">
            <x-select wire:model.live="dateRange" class="w-full sm:w-56">
                <option value="today">Today</option>
                <option value="this_week">This Week</option>
                <option value="this_month">This Month</option>
                <option value="this_quarter">This Quarter</option>
                <option value="this_year">This Year</option>
            </x-select>
        </x-slot>
        <x-slot name="right">
            <x-button variant="outline" href="{{ route('reports.export.pdf', ['dateRange' => $dateRange]) }}">
                Export PDF
            </x-button>
        </x-slot>
    </x-toolbar>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <x-card>
            <x-section title="Financial" description="Invoice totals for the selected period" />
            <div class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between text-text-muted">
                    <span>Total invoiced</span>
                    <span class="text-text-primary font-semibold">${{ number_format($reportData['financial']['total_invoiced'], 2) }}</span>
                </div>
                <div class="flex justify-between text-text-muted">
                    <span>Total paid</span>
                    <span class="text-text-primary font-semibold">${{ number_format($reportData['financial']['total_paid'], 2) }}</span>
                </div>
                <div class="flex justify-between text-text-muted">
                    <span>Pending</span>
                    <span class="text-text-primary font-semibold">${{ number_format($reportData['financial']['pending_amount'], 2) }}</span>
                </div>
            </div>
        </x-card>

        <x-card>
            <x-section title="Delivery" description="Projects and tracked work" />
            <div class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between text-text-muted">
                    <span>Active projects</span>
                    <span class="text-text-primary font-semibold">{{ $reportData['performance']['active_projects'] }}</span>
                </div>
                <div class="flex justify-between text-text-muted">
                    <span>Total hours</span>
                    <span class="text-text-primary font-semibold">{{ number_format($reportData['performance']['total_hours'], 1) }}</span>
                </div>
            </div>
        </x-card>

        <x-card>
            <x-section title="Clients" description="Retention and acquisition" />
            <div class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between text-text-muted">
                    <span>Total clients</span>
                    <span class="text-text-primary font-semibold">{{ $reportData['clients']['total_clients'] }}</span>
                </div>
                <div class="flex justify-between text-text-muted">
                    <span>New this month</span>
                    <span class="text-text-primary font-semibold">{{ $reportData['clients']['new_clients_in_range'] }}</span>
                </div>
            </div>
        </x-card>
    </div>

    <x-card class="mt-8">
        <x-section title="Trends" description="Last 14 days ending at the selected range" />

        @php
            $maxInvoiced = max(array_map(fn ($d) => $d['invoiced'], $trendDays ?: [['invoiced' => 0]]));
            $maxHours = max(array_map(fn ($d) => $d['hours'], $trendDays ?: [['hours' => 0]]));
        @endphp

        <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <div class="text-xs font-semibold text-text-muted">Invoiced</div>
                <div class="mt-3 flex items-end gap-1 h-28">
                    @foreach($trendDays as $day)
                        @php
                            $pct = $maxInvoiced > 0 ? ($day['invoiced'] / $maxInvoiced) : 0;
                            $h = max(2, (int) round($pct * 112));
                        @endphp
                        <div class="flex-1">
                            <div class="w-full rounded-md bg-primary-soft" x-data :style="@js(['height' => $h.'px'])"></div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3 flex justify-between text-[10px] text-text-muted">
                    <span>{{ $trendDays[0]['label'] ?? '' }}</span>
                    <span>{{ $trendDays[count($trendDays) - 1]['label'] ?? '' }}</span>
                </div>
            </div>

            <div>
                <div class="text-xs font-semibold text-text-muted">Hours</div>
                <div class="mt-3 flex items-end gap-1 h-28">
                    @foreach($trendDays as $day)
                        @php
                            $pct = $maxHours > 0 ? ($day['hours'] / $maxHours) : 0;
                            $h = max(2, (int) round($pct * 112));
                        @endphp
                        <div class="flex-1">
                            <div class="w-full rounded-md bg-info-soft" x-data :style="@js(['height' => $h.'px'])"></div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3 flex justify-between text-[10px] text-text-muted">
                    <span>{{ $trendDays[0]['label'] ?? '' }}</span>
                    <span>{{ $trendDays[count($trendDays) - 1]['label'] ?? '' }}</span>
                </div>
            </div>
        </div>
    </x-card>
</x-app-container>
