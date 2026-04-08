@php
    $nav = [
        [
            'title' => 'Overview',
            'items' => [
                ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['route' => 'dashboards.index', 'label' => 'Dashboards', 'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h16'],
                ['route' => 'alerts.index', 'label' => 'Alerts', 'icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
            ],
        ],
        [
            'title' => 'Intelligence',
            'items' => [
                [
                    'label' => 'Intelligence',
                    'icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
                    'children' => [
                        ['route' => 'intelligence.overview', 'label' => 'Overview'],
                        ['route' => 'intelligence.briefing', 'label' => 'Daily Briefing', 'badge' => $urgentCount ?? 0],
                        ['route' => 'intelligence.insights', 'label' => 'AI Insights'],
                        ['route' => 'intelligence.alerts', 'label' => 'Alert Center', 'badge' => $criticalCount ?? 0, 'badge_pulse' => true],
                    ],
                ],
                [
                    'label' => 'SEO & Health',
                    'icon' => 'M11 5h2m-1-1v2m-7 9a7 7 0 1114 0 7 7 0 01-14 0z',
                    'children' => [
                        ['route' => 'seo.index', 'label' => 'SEO', 'can' => 'view-analytics'],
                        ['route' => 'site-health.index', 'label' => 'Site Health', 'can' => 'view-analytics'],
                        ['route' => 'workload.index', 'label' => 'Workload', 'can' => 'view-analytics'],
                        ['route' => 'productivity.index', 'label' => 'Productivity', 'can' => 'view-analytics'],
                    ],
                ],
            ],
        ],
        [
            'title' => 'Sales',
            'items' => [
                [
                    'label' => 'CRM',
                    'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                    'children' => [
                        ['route' => 'pipelines.index', 'label' => 'Pipelines'],
                        ['route' => 'opportunities.create', 'label' => 'New Opportunity'],
                        ['route' => 'leads.index', 'label' => 'Leads'],
                        ['route' => 'leads.create', 'label' => 'New Lead'],
                        ['route' => 'contacts.index', 'label' => 'Contacts'],
                        ['route' => 'contacts.create', 'label' => 'New Contact'],
                    ],
                ],
                [
                    'label' => 'Clients',
                    'icon' => 'M3 7h18M3 12h18M3 17h18',
                    'children' => [
                        ['route' => 'clients.index', 'label' => 'Clients', 'can' => 'manage-organization'],
                        ['route' => 'clients.performance', 'label' => 'Performance', 'can' => 'manage-organization'],
                        ['route' => 'clients.create', 'label' => 'New Client', 'can' => 'manage-organization'],
                    ],
                ],
                [
                    'label' => 'Billing',
                    'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                    'children' => [
                        ['route' => 'proposals.index', 'label' => 'Proposals'],
                        ['route' => 'proposals.create', 'label' => 'New Proposal'],
                        ['route' => 'orders.index', 'label' => 'Orders'],
                        ['route' => 'orders.create', 'label' => 'New Order'],
                        ['route' => 'invoices.index', 'label' => 'Invoices'],
                        ['route' => 'invoices.create', 'label' => 'New Invoice'],
                    ],
                ],
            ],
        ],
        [
            'title' => 'Delivery',
            'items' => [
                [
                    'label' => 'Work',
                    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
                    'children' => [
                        ['route' => 'campaigns.index', 'label' => 'Campaigns'],
                        ['route' => 'campaigns.create', 'label' => 'New Campaign'],
                        ['route' => 'campaigns.wizard', 'label' => 'Campaign Wizard'],
                        ['route' => 'tasks.index', 'label' => 'Tasks'],
                        ['route' => 'tasks.create', 'label' => 'New Task'],
                        ['route' => 'projects.index', 'label' => 'Projects'],
                        ['route' => 'projects.create', 'label' => 'New Project'],
                        ['route' => 'creative-requests.index', 'label' => 'Creative Requests'],
                        ['route' => 'creatives.index', 'label' => 'Creatives Board'],
                    ],
                ],
                [
                    'label' => 'Reports',
                    'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                    'children' => [
                        ['route' => 'reports.index', 'label' => 'Reports'],
                        ['route' => 'deliverables.index', 'label' => 'Deliverables', 'can' => 'view-analytics'],
                        ['route' => 'playbooks.index', 'label' => 'Playbooks', 'can' => 'view-analytics'],
                    ],
                ],
            ],
        ],
        [
            'title' => 'Marketing',
            'items' => [
                [
                    'label' => 'Ads',
                    'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
                    'children' => [
                        ['route' => 'ads.index', 'label' => 'Manager'],
                        ['route' => 'ads.analytics', 'label' => 'Analytics'],
                        ['route' => 'ads.leads', 'label' => 'Leads'],
                    ],
                ],
                [
                    'label' => 'Channels',
                    'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
                    'children' => [
                        ['route' => 'conversations.index', 'label' => 'Conversations'],
                        ['route' => 'social-planner.index', 'label' => 'Social Planner'],
                        ['route' => 'calendars.index', 'label' => 'Calendars'],
                    ],
                ],
                [
                    'label' => 'Assets',
                    'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
                    'children' => [
                        ['route' => 'media.index', 'label' => 'Media'],
                        ['route' => 'forms.index', 'label' => 'Forms'],
                        ['route' => 'forms.create', 'label' => 'New Form'],
                        ['route' => 'analytics.index', 'label' => 'Analytics'],
                        ['route' => 'analytics-management.dashboard', 'label' => 'Analytics Management'],
                    ],
                ],
            ],
        ],
        [
            'title' => 'Automations',
            'items' => [
                [
                    'label' => 'Workflow',
                    'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
                    'children' => [
                        ['route' => 'workflow.index', 'label' => 'Infrastructure'],
                        ['route' => 'workflow.logs', 'label' => 'Workflow Logs'],
                        ['route' => 'automations.index', 'label' => 'Automations'],
                        ['route' => 'automations.create', 'label' => 'New Automation'],
                        ['route' => 'automation.rules', 'label' => 'Rules', 'can' => 'manage-workflow'],
                        ['route' => 'automation.approvals', 'label' => 'Approvals', 'can' => 'manage-workflow'],
                    ],
                ],
                [
                    'label' => 'Time Tracking',
                    'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                    'children' => [
                        ['route' => 'time-tracking.index', 'label' => 'Time Tracking'],
                        ['route' => 'time-tracking.approvals', 'label' => 'Time Approvals', 'can' => 'manage-workflow'],
                    ],
                ],
            ],
        ],
        [
            'title' => 'Admin',
            'items' => [
                [
                    'label' => 'Webhooks',
                    'icon' => 'M7 8h10M7 12h10M7 16h6m6-8l4 4-4 4',
                    'children' => [
                        ['route' => 'webhooks.index', 'label' => 'Overview', 'can' => 'manage-organization'],
                        ['route' => 'webhooks.inbound', 'label' => 'Inbound', 'can' => 'manage-organization'],
                        ['route' => 'webhooks.outbound', 'label' => 'Outbound', 'can' => 'manage-organization'],
                        ['route' => 'webhooks.api', 'label' => 'API', 'can' => 'manage-organization'],
                        ['route' => 'webhooks.mappings.inbound', 'label' => 'Inbound Mappings', 'can' => 'manage-organization'],
                        ['route' => 'webhooks.mappings.outbound', 'label' => 'Outbound Mappings', 'can' => 'manage-organization'],
                    ],
                ],
                [
                    'label' => 'System',
                    'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
                    'children' => [
                        ['route' => 'team.index', 'label' => 'Team'],
                        ['route' => 'users.index', 'label' => 'Users', 'can' => 'manage-organization'],
                        ['route' => 'users.create', 'label' => 'New User', 'can' => 'manage-organization'],
                        ['route' => 'products.index', 'label' => 'Products'],
                        ['route' => 'products.create', 'label' => 'New Product'],
                        ['route' => 'service-packages.index', 'label' => 'Service Packages', 'can' => 'manage-organization'],
                        ['route' => 'feedback.index', 'label' => 'Feedback'],
                        ['route' => 'settings', 'label' => 'Settings', 'can' => 'manage-organization'],
                    ],
                ],
            ],
        ],
    ];

    $isAllowed = function (array $item): bool {
        if (isset($item['can']) && (! auth()->user() || ! auth()->user()->can($item['can']))) {
            return false;
        }

        return true;
    };

    $isActiveRoute = function (string $route): bool {
        return request()->routeIs($route) || request()->routeIs($route.'.*');
    };
@endphp

@foreach($nav as $section)
    <p class="px-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-5">{{ $section['title'] }}</p>

    <div class="space-y-1">
        @foreach($section['items'] as $item)
            @if(isset($item['route']))
                @continue(!$isAllowed($item))
                @php $active = $isActiveRoute($item['route']); @endphp

                <a href="{{ route($item['route']) }}"
                   wire:navigate
                   {{ $active ? 'aria-current=page' : '' }}
                   class="flex items-center px-3 py-2 rounded-element transition-colors duration-150 {{ $active ? 'bg-primary text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5 mr-3 {{ $active ? 'text-white' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path>
                    </svg>
                    <span class="text-sm font-medium flex-1">{{ $item['label'] }}</span>
                </a>
            @else
                @php
                    $children = array_values(array_filter($item['children'] ?? [], fn ($c) => $isAllowed($c)));
                    if ($children === []) {
                        continue;
                    }
                    $childActive = false;
                    foreach ($children as $c) {
                        if ($isActiveRoute($c['route'])) {
                            $childActive = true;
                            break;
                        }
                    }
                @endphp

                <div x-data="{ open: {{ $childActive ? 'true' : 'false' }} }" class="select-none">
                    <button type="button"
                            @click="open = !open"
                            class="w-full flex items-center px-3 py-2 rounded-element transition-colors duration-150 {{ $childActive ? 'bg-gray-100 text-gray-900' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path>
                        </svg>
                        <span class="text-sm font-semibold flex-1 text-left">{{ $item['label'] }}</span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-150" :class="{ 'rotate-90': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>

                    <div x-show="open" x-cloak class="mt-1 space-y-1">
                        @foreach($children as $child)
                            @php $active = $isActiveRoute($child['route']); @endphp
                            <a href="{{ route($child['route']) }}"
                               wire:navigate
                               {{ $active ? 'aria-current=page' : '' }}
                               class="flex items-center pl-11 pr-3 py-2 rounded-element transition-colors duration-150 {{ $active ? 'bg-primary text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
                                <span class="text-sm font-medium flex-1">{{ $child['label'] }}</span>
                                @if(($child['badge'] ?? 0) > 0)
                                    <span class="ml-auto inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full text-[10px] font-bold bg-red-600 text-white {{ !empty($child['badge_pulse']) ? 'animate-pulse' : '' }}">
                                        {{ $child['badge'] }}
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </div>
@endforeach
