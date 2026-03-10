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
            class="fixed inset-0 bg-gray-600 bg-opacity-75 z-40 md:hidden" @click="sidebarOpen = false" x-cloak></div>

        <!-- Mobile Sidebar Menu -->
        <aside x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-300 transform"
            x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
            class="fixed inset-y-0 left-0 flex flex-col w-64 bg-white border-r border-gray-200 z-50 md:hidden" x-cloak>
            <div class="h-16 flex items-center px-6 border-b border-gray-200 justify-between">
                <span class="text-xl font-bold text-primary"><?php echo e(config('app.name')); ?></span>
                <button @click="sidebarOpen = false" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto px-4 py-6 custom-scrollbar">
                <?php echo $__env->make('components.layouts.sidebar-navigation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </aside>

        <!-- Desktop Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 hidden md:flex flex-col flex-shrink-0">
            <div class="h-16 flex items-center px-6 border-b border-gray-200">
                <a href="<?php echo e(route('dashboard')); ?>" class="text-xl font-bold text-primary"><?php echo e(config('app.name')); ?></a>
            </div>
            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto w-full custom-scrollbar">
                <?php echo $__env->make('components.layouts.sidebar-navigation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($header)): ?>
                        <div class="text-lg font-semibold text-gray-800">
                            <?php echo e($header); ?>

                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="flex items-center space-x-4">
                    <!-- Settings Dropdown -->
                    <div class="flex items-center sm:ms-6">
                        <?php if (isset($component)) { $__componentOriginaldf8083d4a852c446488d8d384bbc7cbe = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dropdown','data' => ['align' => 'right','width' => '48']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => 'right','width' => '48']); ?>
                             <?php $__env->slot('trigger', null, []); ?> 
                                <button
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                    <div class="flex items-center">
                                        <div
                                            class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center mr-2">
                                            <span
                                                class="text-xs font-medium text-indigo-700"><?php echo e(substr(Auth::user()->full_name, 0, 1)); ?></span>
                                        </div>
                                        <div class="hidden sm:block"><?php echo e(Auth::user()->full_name); ?></div>
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
                             <?php $__env->endSlot(); ?>

                             <?php $__env->slot('content', null, []); ?> 
                                <div class="px-4 py-2 border-b border-gray-100">
                                    <p class="text-xs text-gray-500">Current Role</p>
                                    <p class="text-sm font-semibold text-indigo-600"><?php echo e(Auth::user()->role); ?></p>
                                </div>
                                <?php if (isset($component)) { $__componentOriginal68cb1971a2b92c9735f83359058f7108 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal68cb1971a2b92c9735f83359058f7108 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dropdown-link','data' => ['href' => route('profile.edit')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dropdown-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('profile.edit'))]); ?>
                                    <?php echo e(__('Profile')); ?>

                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal68cb1971a2b92c9735f83359058f7108)): ?>
<?php $attributes = $__attributesOriginal68cb1971a2b92c9735f83359058f7108; ?>
<?php unset($__attributesOriginal68cb1971a2b92c9735f83359058f7108); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal68cb1971a2b92c9735f83359058f7108)): ?>
<?php $component = $__componentOriginal68cb1971a2b92c9735f83359058f7108; ?>
<?php unset($__componentOriginal68cb1971a2b92c9735f83359058f7108); ?>
<?php endif; ?>

                                <div class="border-t border-gray-100"></div>

                                <!-- Role Switcher -->
                                <div class="block px-4 py-2 text-xs text-gray-400">
                                    <?php echo e(__('Switch Role')); ?>

                                </div>

                                <!-- Role Switcher -->
                                <div class="block px-4 py-2 text-xs text-gray-400">
                                    <?php echo e(__('Switch Role')); ?>

                                </div>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['OWNER', 'ADMIN', 'ANALYST', 'OPERATOR', 'VIEWER']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if (isset($component)) { $__componentOriginal68cb1971a2b92c9735f83359058f7108 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal68cb1971a2b92c9735f83359058f7108 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dropdown-link','data' => ['href' => route('auto-login', ['role' => strtolower($role)])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dropdown-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('auto-login', ['role' => strtolower($role)]))]); ?>
                                        <span class="<?php echo e(Auth::user()->role === $role ? 'font-bold text-indigo-600' : ''); ?>">
                                            <?php echo e($role); ?>

                                        </span>
                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal68cb1971a2b92c9735f83359058f7108)): ?>
<?php $attributes = $__attributesOriginal68cb1971a2b92c9735f83359058f7108; ?>
<?php unset($__attributesOriginal68cb1971a2b92c9735f83359058f7108); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal68cb1971a2b92c9735f83359058f7108)): ?>
<?php $component = $__componentOriginal68cb1971a2b92c9735f83359058f7108; ?>
<?php unset($__componentOriginal68cb1971a2b92c9735f83359058f7108); ?>
<?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                <div class="border-t border-gray-100"></div>

                                <!-- Authentication -->
                                <form method="POST" action="<?php echo e(route('logout')); ?>">
                                    <?php echo csrf_field(); ?>
                                    <?php if (isset($component)) { $__componentOriginal68cb1971a2b92c9735f83359058f7108 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal68cb1971a2b92c9735f83359058f7108 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dropdown-link','data' => ['href' => route('logout'),'onclick' => 'event.preventDefault(); this.closest(\'form\').submit();']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dropdown-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('logout')),'onclick' => 'event.preventDefault(); this.closest(\'form\').submit();']); ?>
                                        <?php echo e(__('Log Out')); ?>

                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal68cb1971a2b92c9735f83359058f7108)): ?>
<?php $attributes = $__attributesOriginal68cb1971a2b92c9735f83359058f7108; ?>
<?php unset($__attributesOriginal68cb1971a2b92c9735f83359058f7108); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal68cb1971a2b92c9735f83359058f7108)): ?>
<?php $component = $__componentOriginal68cb1971a2b92c9735f83359058f7108; ?>
<?php unset($__componentOriginal68cb1971a2b92c9735f83359058f7108); ?>
<?php endif; ?>
                                </form>
                             <?php $__env->endSlot(); ?>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe)): ?>
<?php $attributes = $__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe; ?>
<?php unset($__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf8083d4a852c446488d8d384bbc7cbe)): ?>
<?php $component = $__componentOriginaldf8083d4a852c446488d8d384bbc7cbe; ?>
<?php unset($__componentOriginaldf8083d4a852c446488d8d384bbc7cbe); ?>
<?php endif; ?>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-6 overflow-y-auto bg-[#F9FAFB]">
                <?php echo e($slot ?? ''); ?>

                <?php echo $__env->yieldContent('content'); ?>
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 py-4 px-6 mt-auto">
                <p class="text-sm text-gray-500 text-center">&copy; <?php echo e(date('Y')); ?> <?php echo e(config('app.name')); ?>. All rights
                    reserved.</p>
            </footer>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div x-data="{ 
            messages: [],
            remove(mid) {
                this.messages = this.messages.filter(m => m.id !== mid)
            },
            add(msg, type = 'success') {
                const id = Date.now()
                this.messages.push({ id, msg, type })
                setTimeout(() => this.remove(id), 5000)
            }
        }" x-on:notify.window="add($event.detail.message, $event.detail.type)"
        class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 max-w-sm w-full">

        <template x-for="message in messages" :key="message.id">
            <div x-show="true" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-y-2 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-2 opacity-0"
                :class="{
                    'bg-green-600': message.type === 'success',
                    'bg-red-600': message.type === 'error',
                    'bg-blue-600': message.type === 'info',
                    'bg-yellow-600': message.type === 'warning'
                }"
                class="text-white px-4 py-3 rounded-lg shadow-lg flex items-center justify-between pointer-events-auto">
                <span x-text="message.msg" class="text-sm font-medium"></span>
                <button @click="remove(message.id)" class="ml-4 text-white/80 hover:text-white">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </template>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
            <div x-init="add('<?php echo e(session('success')); ?>', 'success')"></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
            <div x-init="add('<?php echo e(session('error')); ?>', 'error')"></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</body>

</html><?php /**PATH /Users/naveenadicharla/Documents/DC OS/resources/views/layouts/app.blade.php ENDPATH**/ ?>