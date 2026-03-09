<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        [x-cloak] {
            display: none !important;
        }

        :root {
            --primary: #4F46E5;
            --primary-hover: #4338CA;
        }

        .bg-primary {
            background-color: var(--primary);
        }

        .text-primary {
            color: var(--primary);
        }

        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #F3F4F6;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #D1D5DB;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #9CA3AF;
        }
    </style>
</head>

<body class="font-sans antialiased bg-[#F9FAFB] text-[#111827]" x-data="{ sidebarOpen: false }">
    <div class="h-screen flex overflow-hidden">
        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-600 bg-opacity-75 z-40 md:hidden"
            @click="sidebarOpen = false" x-cloak></div>

        <!-- Mobile Sidebar Menu -->
        <div x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-300 transform"
            x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="fixed inset-y-0 left-0 flex flex-col w-64 bg-white border-r border-gray-200 z-50 md:hidden" x-cloak>
            <div class="h-16 flex items-center px-6 border-b border-gray-200 justify-between">
                <span class="text-xl font-bold text-primary">{{ config('app.name') }}</span>
                <button @click="sidebarOpen = false" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto px-4 py-6 custom-scrollbar">
                @include('components.layouts.sidebar-navigation')
            </div>
        </div>

        <!-- Desktop Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 hidden md:flex flex-col flex-shrink-0">
            <div class="h-16 flex items-center px-6 border-b border-gray-200">
                <a href="{{ route('dashboard') }}" class="text-xl font-bold text-primary">{{ config('app.name') }}</a>
            </div>
            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto w-full custom-scrollbar">
                @include('components.layouts.sidebar-navigation')
            </nav>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Header -->
            <header
                class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 sticky top-0 z-30">
                <div class="flex items-center">
                    <button @click="sidebarOpen = true" class="md:hidden text-gray-500 hover:text-gray-700 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    @isset($header)
                        <div class="text-lg font-semibold text-gray-800">
                            {{ $header }}
                        </div>
                    @endisset
                </div>

                <div class="flex items-center space-x-4">
                    <!-- Notifications, etc. can go here -->

                    <!-- Settings Dropdown -->
                    <div class="flex items-center sm:ms-6">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                    <div class="flex items-center">
                                        <div
                                            class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center mr-2">
                                            <span
                                                class="text-xs font-medium text-indigo-700">{{ substr(Auth::user()->full_name, 0, 1) }}</span>
                                        </div>
                                        <div class="hidden sm:block">{{ Auth::user()->full_name }}</div>
                                    </div>
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
                                <div class="px-4 py-2 border-b border-gray-100">
                                    <p class="text-xs text-gray-500">Current Role</p>
                                    <p class="text-sm font-semibold text-indigo-600">{{ Auth::user()->role }}</p>
                                </div>
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
                                    <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                        {{ __('Log Out') }}
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-6 overflow-y-auto bg-[#F9FAFB]">
                {{ $slot ?? '' }}
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 py-4 px-6 mt-auto">
                <p class="text-sm text-gray-500 text-center">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights
                    reserved.</p>
            </footer>
        </div>
    </div>

    @livewireScripts
</body>

</html>