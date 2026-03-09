<div>
     <?php $__env->slot('header', null, []); ?> 
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <?php echo e(__('Clients Directory')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 font-sans">
                    <div
                        class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 space-y-4 md:space-y-0">
                        <div>
                            <h3 class="text-2xl font-extrabold text-gray-900 tracking-tight">Clients Portfolio</h3>
                            <p class="text-sm text-gray-500 mt-1">Manage and track all managed organizations and brands.
                            </p>
                        </div>
                        <button
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl font-bold text-sm shadow-lg shadow-indigo-100 transition-all active:scale-95">
                            New Client Account
                        </button>
                    </div>

                    <!-- Search and Filters -->
                    <div class="mb-6">
                        <div class="relative max-w-md">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                            <input wire:model.live="search" type="text" placeholder="Search by name or email..."
                                class="pl-10 block w-full bg-gray-50 border-gray-100 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                        </div>
                    </div>

                    <div class="overflow-hidden border border-gray-100 rounded-2xl">
                        <table class="w-full text-left text-sm">
                            <thead class="text-xs text-gray-400 uppercase bg-gray-50/50 border-b border-gray-100">
                                <tr>
                                    <th scope="col" class="px-6 py-4 font-bold">Client Identity</th>
                                    <th scope="col" class="px-6 py-4 font-bold">Contact Channel</th>
                                    <th scope="col" class="px-6 py-4 font-bold">Lifecycle Status</th>
                                    <th scope="col" class="px-6 py-4 font-bold text-center">Active Campaigns</th>
                                    <th scope="col" class="px-6 py-4 font-bold text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr class="hover:bg-indigo-50/30 transition-colors">
                                        <td class="px-6 py-5">
                                            <div class="flex items-center">
                                                <div
                                                    class="h-10 w-10 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold mr-3">
                                                    <?php echo e(substr($client->name, 0, 1)); ?>

                                                </div>
                                                <div class="font-bold text-gray-900"><?php echo e($client->name); ?></div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 text-gray-600 font-medium"><?php echo e($client->email); ?></td>
                                        <td class="px-6 py-5">
                                            <?php
                                                $statusColors = [
                                                    'ACTIVE' => 'bg-green-100 text-green-700',
                                                    'INACTIVE' => 'bg-gray-100 text-gray-600',
                                                    'ONBOARDING' => 'bg-blue-100 text-blue-700',
                                                    'ARCHIVED' => 'bg-red-100 text-red-700',
                                                ];
                                                $color = $statusColors[$client->status] ?? 'bg-gray-100 text-gray-600';
                                            ?>
                                            <span
                                                class="<?php echo e($color); ?> px-3 py-1 rounded-full text-[10px] font-extrabold tracking-wider">
                                                <?php echo e(strtoupper($client->status)); ?>

                                            </span>
                                        </td>
                                        <td class="px-6 py-5 text-center font-bold text-gray-700">
                                            <?php echo e($client->campaigns_count); ?></td>
                                        <td class="px-6 py-5 text-right">
                                            <a href="<?php echo e(route('clients.index')); ?>"
                                                class="text-indigo-600 hover:text-indigo-800 font-bold text-xs uppercase tracking-widest transition-colors">
                                                Manage
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-20 text-center">
                                            <div class="flex flex-col items-center">
                                                <svg class="h-12 w-12 text-gray-200 mb-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                </svg>
                                                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest">No
                                                    clients found</h3>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        <?php echo e($clients->links()); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH /Users/naveenadicharla/Documents/DC OS/resources/views/livewire/clients/index.blade.php ENDPATH**/ ?>