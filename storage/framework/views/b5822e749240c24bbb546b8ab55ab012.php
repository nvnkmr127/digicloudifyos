<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name', 'Laravel')); ?></title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

</head>

<body class="font-sans antialiased bg-[#F9FAFB] text-[#111827]">
    <div class="min-h-screen flex">
        <!-- Sidebar Navigation -->
        <aside class="w-64 bg-white border-r border-gray-200 hidden md:flex flex-col">
            <div class="h-16 flex items-center px-6 border-b border-gray-200">
                <span class="text-xl font-bold text-primary"><?php echo e(config('app.name', 'Laravel')); ?></span>
            </div>
            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto w-full">
                <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-4">Core</p>
                <a href="<?php echo e(route('dashboard')); ?>"
                    class="flex items-center px-3 py-2 rounded-md <?php echo e(request()->routeIs('dashboard') ? 'bg-primary text-white' : 'text-text-muted hover:bg-gray-100'); ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                        </path>
                    </svg>
                    Dashboard
                </a>

                <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-4">CRM & Sales</p>
                <a href="<?php echo e(route('pipelines.index') ?? '#'); ?>"
                    class="flex items-center px-3 py-2 rounded-md <?php echo e(request()->routeIs('pipelines.*') ? 'bg-primary text-white' : 'text-text-muted hover:bg-gray-100'); ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                        </path>
                    </svg>
                    Pipelines
                </a>
                <a href="<?php echo e(route('clients.index') ?? '#'); ?>"
                    class="flex items-center px-3 py-2 rounded-md <?php echo e(request()->routeIs('clients.*') ? 'bg-primary text-white' : 'text-text-muted hover:bg-gray-100'); ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                    Contacts
                </a>
                <a href="<?php echo e(route('proposals.index') ?? '#'); ?>"
                    class="flex items-center px-3 py-2 rounded-md <?php echo e(request()->routeIs('proposals.*') ? 'bg-primary text-white' : 'text-text-muted hover:bg-gray-100'); ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Proposals & Docs
                </a>
                <a href="<?php echo e(route('invoices.index') ?? '#'); ?>"
                    class="flex items-center px-3 py-2 rounded-md <?php echo e(request()->routeIs('invoices.*') ? 'bg-primary text-white' : 'text-text-muted hover:bg-gray-100'); ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                        </path>
                    </svg>
                    Billing / Payments
                </a>

                <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-4">Execution</p>
                <a href="<?php echo e(route('projects.index') ?? '#'); ?>"
                    class="flex items-center px-3 py-2 rounded-md <?php echo e(request()->routeIs('projects.*') ? 'bg-primary text-white' : 'text-text-muted hover:bg-gray-100'); ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                        </path>
                    </svg>
                    Projects
                </a>
                <a href="<?php echo e(route('team.index') ?? '#'); ?>"
                    class="flex items-center px-3 py-2 rounded-md <?php echo e(request()->routeIs('team.*') ? 'bg-primary text-white' : 'text-text-muted hover:bg-gray-100'); ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                    Team
                </a>
                <a href="<?php echo e(route('time-tracking.index') ?? '#'); ?>"
                    class="flex items-center px-3 py-2 rounded-md <?php echo e(request()->routeIs('time-tracking.*') ? 'bg-primary text-white' : 'text-text-muted hover:bg-gray-100'); ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Time Tracking
                </a>

                <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-4">Growth &
                    Marketing</p>
                <a href="<?php echo e(route('conversations.index') ?? '#'); ?>"
                    class="flex items-center px-3 py-2 rounded-md <?php echo e(request()->routeIs('conversations.*') ? 'bg-primary text-white' : 'text-text-muted hover:bg-gray-100'); ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                        </path>
                    </svg>
                    Conversations
                </a>
                <a href="<?php echo e(route('calendars.index') ?? '#'); ?>"
                    class="flex items-center px-3 py-2 rounded-md <?php echo e(request()->routeIs('calendars.*') ? 'bg-primary text-white' : 'text-text-muted hover:bg-gray-100'); ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    Calendars
                </a>
                <a href="<?php echo e(route('automations.index') ?? '#'); ?>"
                    class="flex items-center px-3 py-2 rounded-md <?php echo e(request()->routeIs('automations.*') ? 'bg-primary text-white' : 'text-text-muted hover:bg-gray-100'); ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z">
                        </path>
                    </svg>
                    Automations
                </a>
                <a href="<?php echo e(route('social-planner.index') ?? '#'); ?>"
                    class="flex items-center px-3 py-2 rounded-md <?php echo e(request()->routeIs('social-planner.*') ? 'bg-primary text-white' : 'text-text-muted hover:bg-gray-100'); ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z">
                        </path>
                    </svg>
                    Social Planner
                </a>
                <a href="<?php echo e(route('forms.index') ?? '#'); ?>"
                    class="flex items-center px-3 py-2 rounded-md <?php echo e(request()->routeIs('forms.*') ? 'bg-primary text-white' : 'text-text-muted hover:bg-gray-100'); ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                    </svg>
                    Forms & Surveys
                </a>
                <a href="<?php echo e(route('media.index') ?? '#'); ?>"
                    class="flex items-center px-3 py-2 rounded-md <?php echo e(request()->routeIs('media.*') ? 'bg-primary text-white' : 'text-text-muted hover:bg-gray-100'); ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    Media
                </a>
                <a href="<?php echo e(route('analytics.index') ?? '#'); ?>"
                    class="flex items-center px-3 py-2 rounded-md <?php echo e(request()->routeIs('analytics.*') ? 'bg-primary text-white' : 'text-text-muted hover:bg-gray-100'); ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    Analytics
                </a>

                <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-4">Setup</p>
                <a href="<?php echo e(route('settings')); ?>"
                    class="flex items-center px-3 py-2 rounded-md <?php echo e(request()->routeIs('settings') ? 'bg-primary text-white' : 'text-text-muted hover:bg-gray-100'); ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                        </path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Settings
                </a>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Header -->
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6">
                <div class="flex items-center md:hidden">
                    <span class="text-xl font-bold text-primary"><?php echo e(config('app.name')); ?></span>
                </div>
                <div>
                    <!-- Optional header right side actions -->
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto">
                <!-- Livewire full-page components are injected into $slot. Traditional extend into 'content' -->
                <?php echo e($slot ?? ''); ?>

                <?php echo $__env->yieldContent('content'); ?>
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 py-4 px-6 mt-auto">
                <p class="text-sm text-text-muted text-center">&copy; <?php echo e(date('Y')); ?> <?php echo e(config('app.name')); ?>. All
                    rights reserved.</p>
            </footer>
        </div>
    </div>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

</body>

</html><?php /**PATH /Users/naveenadicharla/Documents/DC OS/resources/views/components/layouts/app.blade.php ENDPATH**/ ?>