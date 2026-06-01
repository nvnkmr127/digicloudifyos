<x-app-container>
    <x-page-header title="Dashboard">
        <x-button variant="outline" href="{{ route('settings') }}" wire:navigate>
            Settings
        </x-button>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
        <x-stat-card
            label="Revenue"
            value="${{ number_format($revenue_matrix['total_paid'] / 100, 2) }}"
            trend="{{ number_format($revenue_matrix['pending'] / 100, 2) }} pending"
        />

        <x-stat-card
            label="Leads"
            value="{{ $lead_flux['total'] }}"
            trend="+{{ $lead_flux['new_today'] }} today"
        >
            <div class="text-sm text-text-muted">
                {{ $lead_flux['high_intent'] }} high intent
            </div>
        </x-stat-card>

        <x-stat-card
            label="Creative Requests"
            value="{{ $creative_nodes['pending'] }}"
            trend="{{ $creative_nodes['urgent'] }} urgent"
        >
            <x-button variant="outline" size="sm" href="{{ route('creative-requests.index') }}" wire:navigate>
                View Requests
            </x-button>
        </x-stat-card>

        <x-stat-card
            label="Automation Runs"
            value="{{ $automation_pulse['total_runs'] }}"
        />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-4">
            <x-section title="Automation Activity" description="Recent workflow runs across your organization">
                <x-slot name="actions">
                    <x-button variant="outline" size="sm" href="{{ route('workflow.logs') }}" wire:navigate>
                        View Logs
                    </x-button>
                </x-slot>
            </x-section>

            <x-card>
                <div class="space-y-3">
                    @forelse($automation_pulse['recent_runs'] as $run)
                        <div class="flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-text-primary truncate">{{ $run->rule->name }}</div>
                                <div class="text-xs text-text-muted">
                                    {{ $run->created_at->diffForHumans() }}
                                </div>
                            </div>
                            <x-badge
                                :variant="$run->status === 'success' ? 'success' : 'danger'"
                                size="xs"
                            >
                                {{ $run->status }}
                            </x-badge>
                        </div>
                    @empty
                        <x-empty-state title="No automation runs yet" description="Recent workflow activity appears here once automations start running." />
                    @endforelse
                </div>
            </x-card>
        </div>

        <div class="space-y-6">
            <div class="space-y-4">
                <x-section title="Projects" description="Recently updated delivery work">
                    <x-slot name="actions">
                        <x-button variant="outline" size="sm" href="{{ route('projects.index') }}" wire:navigate>
                            View All
                        </x-button>
                    </x-slot>
                </x-section>

                <x-card>
                    <div class="space-y-3">
                        @forelse($recent_projects as $project)
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-text-primary truncate">{{ $project->name }}</div>
                                <div class="text-xs text-text-muted truncate">{{ $project->client->name }}</div>
                            </div>
                        @empty
                            <x-empty-state title="No projects yet" description="Create a project to start tracking delivery work." />
                        @endforelse
                    </div>
                </x-card>
            </div>

            <x-card>
                <x-section title="Funnel" description="Conversion summary from recent submissions" />
                <div class="mt-4">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-text-muted">Submissions</span>
                        <span class="font-semibold text-text-primary">{{ $conversion_funnel['total_submissions'] }}</span>
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Performance Intelligence Hub -->
    <div class="mt-10 mb-12 space-y-4">
        <x-section title="Performance" description="Portfolio health, briefing, and anomalies">
            <x-slot name="actions">
                <x-button variant="outline" size="sm" href="{{ route('intelligence.overview') }}" wire:navigate>
                    Open Intelligence
                </x-button>
            </x-slot>
        </x-section>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Client Health Grid Preview -->
            <x-card>
                <x-section title="Portfolio Health" description="Latest client health scores" />
                <div class="grid grid-cols-2 gap-3 mt-4">
                    @forelse($client_health_grid as $client)
                        <a href="{{ route('intelligence.client', $client->id) }}" wire:navigate class="p-3 bg-gray-50 rounded-element border border-transparent hover:border-gray-200 hover:bg-white transition flex items-center gap-3">
                            <div class="h-8 w-8 flex items-center justify-center rounded-element font-semibold text-xs {{ $client->latestHealthScore?->getScoreBadgeClass() ?? 'bg-gray-200 text-gray-600' }}">
                                {{ $client->latestHealthScore?->overall_score ?? '?' }}
                            </div>
                            <span class="text-xs font-semibold text-text-primary truncate">{{ $client->name }}</span>
                        </a>
                    @empty
                        <x-empty-state title="No health data yet" description="Connect integrations to start collecting health signals." />
                    @endforelse
                </div>
            </x-card>

            <!-- Morning Briefing Highlights -->
            <x-card>
                <x-section title="Daily Briefing" description="Today’s prioritized action items">
                    <x-slot name="actions">
                        <x-button variant="outline" size="sm" href="{{ route('intelligence.briefing') }}" wire:navigate>
                            View Briefing
                        </x-button>
                    </x-slot>
                </x-section>

                <div class="mt-4 space-y-3">
                    @if($morning_briefing_preview)
                        @foreach($morning_briefing_preview->actionItems as $item)
                            <div class="p-3 bg-gray-50 rounded-element border border-gray-100">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="text-xs text-text-muted truncate">{{ $item->client->name }}</div>
                                        <div class="text-sm font-semibold text-text-primary">{{ $item->title }}</div>
                                    </div>
                                    <x-badge :variant="$item->priority_level === 'urgent' ? 'danger' : 'warning'" size="xs">
                                        {{ $item->priority_level }}
                                    </x-badge>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <x-empty-state title="No briefing yet" description="A daily briefing will appear once generated." />
                    @endif
                </div>
            </x-card>

            <!-- Recent Anomalies -->
            <x-card>
                <x-section title="Anomalies" description="Recent performance exceptions">
                    <x-slot name="actions">
                        <x-button variant="outline" size="sm" href="{{ route('intelligence.alerts') }}" wire:navigate>
                            View Alerts
                        </x-button>
                    </x-slot>
                </x-section>

                <div class="space-y-3 mt-4">
                    @forelse($recent_anomalies as $anomaly)
                        <a href="{{ route('intelligence.alerts') }}" wire:navigate class="flex items-center justify-between gap-3 p-3 bg-gray-50 rounded-element border border-gray-100 hover:bg-white transition">
                            <div class="min-w-0">
                                <div class="text-xs text-text-muted truncate">{{ $anomaly->client->name }}</div>
                                <div class="text-sm font-semibold text-text-primary truncate">{{ $anomaly->metric_name }}</div>
                            </div>
                            <x-badge :variant="$anomaly->severity === 'critical' ? 'danger' : 'warning'" size="xs">
                                {{ $anomaly->severity }}
                            </x-badge>
                        </a>
                    @empty
                        <x-empty-state title="No anomalies detected" description="Everything looks stable right now." />
                    @endforelse
                </div>
            </x-card>
        </div>
    </div>
</x-app-container>
