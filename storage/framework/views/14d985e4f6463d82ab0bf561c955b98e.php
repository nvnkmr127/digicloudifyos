<div>
     <?php $__env->slot('header', null, []); ?> 
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <?php echo e(__('System Alerts')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-3xl font-black text-gray-900 tracking-tight">System Intelligence</h3>
                    <p class="text-gray-400 font-bold text-sm tracking-widest uppercase mt-1">Real-time anomaly &
                        performance alerts</p>
                </div>
            </div>

            <div class="space-y-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $styles = [
                            'CRITICAL' => 'bg-red-50 border-red-200 text-red-700 icon-bg-red-100',
                            'WARNING' => 'bg-yellow-50 border-yellow-200 text-yellow-700 icon-bg-yellow-100',
                            'INFO' => 'bg-blue-50 border-blue-200 text-blue-700 icon-bg-blue-100',
                        ];
                        $style = $styles[$alert->severity] ?? $styles['INFO'];
                        $parts = explode(' ', $style);
                        $bgColor = $parts[0];
                        $borderColor = $parts[1];
                        $textColor = $parts[2];
                    ?>
                    <div
                        class="<?php echo e($bgColor); ?> <?php echo e($borderColor); ?> border rounded-3xl p-6 transition-all hover:shadow-lg hover:shadow-gray-100 flex items-start group">
                        <div
                            class="h-12 w-12 rounded-2xl bg-white shadow-sm flex items-center justify-center mr-6 flex-shrink-0">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($alert->severity === 'CRITICAL'): ?>
                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            <?php elseif($alert->severity === 'WARNING'): ?>
                                <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            <?php else: ?>
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div class="flex-grow">
                            <div class="flex items-center justify-between mb-1">
                                <h4 class="text-lg font-black tracking-tight <?php echo e($textColor); ?>"><?php echo e($alert->title); ?></h4>
                                <span
                                    class="text-[10px] font-black text-gray-400 uppercase tracking-widest"><?php echo e($alert->triggered_at->diffForHumans()); ?></span>
                            </div>
                            <p class="text-sm font-medium text-gray-600 mb-4"><?php echo e($alert->message); ?></p>
                            <div class="flex items-center space-x-3">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($alert->status === 'OPEN'): ?>
                                    <button wire:click="acknowledge('<?php echo e($alert->id); ?>')"
                                        class="bg-white border <?php echo e($borderColor); ?> px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest <?php echo e($textColor); ?> hover:bg-gray-50 transition shadow-sm">
                                        Acknowledge
                                    </button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <button wire:click="resolve('<?php echo e($alert->id); ?>')"
                                    class="bg-gray-900 text-white px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-black transition shadow-sm opacity-0 group-hover:opacity-100">
                                    Resolve
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="py-20 text-center bg-gray-50 rounded-[40px] border border-dashed border-gray-200">
                        <div class="inline-flex h-16 w-16 items-center justify-center bg-white rounded-3xl shadow-sm mb-6">
                            <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h5 class="text-xl font-black text-gray-900 tracking-tight">All systems nominal</h5>
                        <p class="text-gray-400 font-bold text-sm tracking-widest uppercase mt-2">No active alerts requiring
                            attention.</p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</div><?php /**PATH /Users/naveenadicharla/Documents/DC OS/resources/views/livewire/alerts/index.blade.php ENDPATH**/ ?>