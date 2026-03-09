<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('campaigns.index')" :active="request()->routeIs('campaigns.*')">
                        {{ __('Campaigns') }}
                    </x-nav-link>
                    <x-nav-link :href="route('tasks.index')" :active="request()->routeIs('tasks.*')">
                        {{ __('Tasks') }}
                    </x-nav-link>
                    <x-nav-link :href="route('leads.index')" :active="request()->routeIs('leads.*')">
                        {{ __('Leads') }}
                    </x-nav-link>
                    <x-nav-link :href="route('creatives.index')" :active="request()->routeIs('creatives.*')">
                        {{ __('Creatives') }}
                    </x-nav-link>
                    <x-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')">
                        {{ __('Clients') }}
                    </x-nav-link>
                    <x-nav-link :href="route('workflow.index')" :active="request()->routeIs('workflow.*')">
                        {{ __('Monitoring') }}
                    </x-nav-link>
                    <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                        {{ __('Reports') }}
                    </x-nav-link>
                    <x-nav-link :href="route('alerts.index')" :active="request()->routeIs('alerts.*')">
                        {{ __('Alerts') }}
                    </x-nav-link>

                    <!-- Modules Dropdown -->
                    <div class="inline-flex items-center pt-1">
                        <x-dropdown align="left" width="64">
                            <x-slot name="trigger">
                                <button
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                    <div>{{ __('Apps') }}</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <div class="grid grid-cols-2 w-64">
                                    <div>
                                        <x-dropdown-link
                                            :href="route('projects.index')">{{ __('Projects') }}</x-dropdown-link>
                                        <x-dropdown-link
                                            :href="route('pipelines.index')">{{ __('Pipelines') }}</x-dropdown-link>
                                        <x-dropdown-link
                                            :href="route('conversations.index')">{{ __('Conversations') }}</x-dropdown-link>
                                        <x-dropdown-link
                                            :href="route('social-planner.index')">{{ __('Social') }}</x-dropdown-link>
                                        <x-dropdown-link
                                            :href="route('orders.index')">{{ __('Orders') }}</x-dropdown-link>
                                        <x-dropdown-link
                                            :href="route('proposals.index')">{{ __('Proposals') }}</x-dropdown-link>
                                        <x-dropdown-link
                                            :href="route('invoices.index')">{{ __('Invoices') }}</x-dropdown-link>
                                    </div>
                                    <div>
                                        <x-dropdown-link
                                            :href="route('analytics.index')">{{ __('Analytics') }}</x-dropdown-link>
                                        <x-dropdown-link :href="route('team.index')">{{ __('Team') }}</x-dropdown-link>
                                        <x-dropdown-link
                                            :href="route('users.index')">{{ __('Users') }}</x-dropdown-link>
                                        <x-dropdown-link
                                            :href="route('automations.index')">{{ __('Automations') }}</x-dropdown-link>
                                        <x-dropdown-link
                                            :href="route('time-tracking.index')">{{ __('Time') }}</x-dropdown-link>
                                        <x-dropdown-link
                                            :href="route('media.index')">{{ __('Media') }}</x-dropdown-link>
                                        <x-dropdown-link
                                            :href="route('calendars.index')">{{ __('Calendars') }}</x-dropdown-link>
                                        <x-dropdown-link
                                            :href="route('forms.index')">{{ __('Forms') }}</x-dropdown-link>
                                        <x-dropdown-link
                                            :href="route('products.index')">{{ __('Products') }}</x-dropdown-link>
                                        <x-dropdown-link
                                            :href="route('settings')">{{ __('Settings') }}</x-dropdown-link>
                                    </div>
                                </div>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->full_name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <div class="border-t border-gray-100"></div>

                        <!-- Role Switcher -->
                        <div class="block px-4 py-2 text-xs text-gray-400">
                            {{ __('Switch Role') }}
                        </div>

                        @foreach(['OWNER', 'ADMIN', 'ANALYST', 'OPERATOR', 'VIEWER'] as $role)
                            <x-dropdown-link :href="route('auto-login', ['role' => strtolower($role)])">
                                <span class="{{ Auth::user()->role === $role ? 'font-bold text-indigo-600' : '' }}">
                                    {{ $role }}
                                </span>
                            </x-dropdown-link>
                        @endforeach

                        <div class="border-t border-gray-100"></div>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('campaigns.index')" :active="request()->routeIs('campaigns.*')">
                {{ __('Campaigns') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('tasks.index')" :active="request()->routeIs('tasks.*')">
                {{ __('Tasks') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('leads.index')" :active="request()->routeIs('leads.*')">
                {{ __('Leads') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('creatives.index')" :active="request()->routeIs('creatives.*')">
                {{ __('Creatives') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')">
                {{ __('Clients') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('workflow.index')" :active="request()->routeIs('workflow.*')">
                {{ __('Monitoring') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                {{ __('Reports') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('alerts.index')" :active="request()->routeIs('alerts.*')">
                {{ __('Alerts') }}
            </x-responsive-nav-link>
            <div class="border-t border-gray-200"></div>
            <div class="px-4 py-2 text-xs text-gray-400 capitalize">{{ __('All Modules') }}</div>
            <x-responsive-nav-link :href="route('projects.index')">{{ __('Projects') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('pipelines.index')">{{ __('Pipelines') }}</x-responsive-nav-link>
            <x-responsive-nav-link
                :href="route('conversations.index')">{{ __('Conversations') }}</x-responsive-nav-link>
            <x-responsive-nav-link
                :href="route('social-planner.index')">{{ __('Social Planner') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('orders.index')">{{ __('Orders') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('proposals.index')">{{ __('Proposals') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('invoices.index')">{{ __('Invoices') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('analytics.index')">{{ __('Analytics') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('team.index')">{{ __('Team') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('users.index')">{{ __('Users') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('automations.index')">{{ __('Automations') }}</x-responsive-nav-link>
            <x-responsive-nav-link
                :href="route('time-tracking.index')">{{ __('Time Tracking') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('media.index')">{{ __('Media') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('calendars.index')">{{ __('Calendars') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('forms.index')">{{ __('Forms') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('products.index')">{{ __('Products') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('settings')">{{ __('Settings') }}</x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->full_name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <div class="border-t border-gray-200"></div>
                <div class="block px-4 py-2 text-xs text-gray-400">
                    {{ __('Switch Role') }}
                </div>
                @foreach(['OWNER', 'ADMIN', 'ANALYST', 'OPERATOR', 'VIEWER'] as $role)
                    <x-responsive-nav-link :href="route('auto-login', ['role' => strtolower($role)])">
                        {{ $role }}
                    </x-responsive-nav-link>
                @endforeach
                <div class="border-t border-gray-200"></div>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>