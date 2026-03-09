<div>
     <?php $__env->slot('header', null, []); ?> 
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <?php echo e(__('Creative Requests')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold">Creative Pipeline</h3>
                        <button
                            class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                            New Request
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $statusGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="bg-gray-50 rounded-xl p-4 min-h-[600px] border border-gray-100">
                                <div class="flex items-center justify-between mb-4 border-b pb-2">
                                    <h4 class="font-bold <?php echo e($group['text']); ?>"><?php echo e($group['title']); ?></h4>
                                    <span class="bg-white px-2 py-0.5 rounded-full text-xs font-bold shadow-sm">
                                        <?php echo e(count($requests[$key] ?? [])); ?>

                                    </span>
                                </div>

                                <div class="space-y-4">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $requests[$key] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <div
                                            class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition cursor-pointer group">
                                            <div class="flex justify-between items-start mb-2">
                                                <span
                                                    class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">
                                                    <?php echo e($request['type']); ?>

                                                </span>
                                                <span
                                                    class="text-[10px] font-bold <?php echo e($request['priority'] === 'high' ? 'text-red-600' : 'text-gray-400'); ?>">
                                                    <?php echo e(strtoupper($request['priority'])); ?>

                                                </span>
                                            </div>

                                            <h5
                                                class="font-bold text-sm text-gray-800 group-hover:text-indigo-600 transition truncate">
                                                <?php echo e($request['title']); ?>

                                            </h5>

                                            <p class="text-xs text-gray-500 mt-2 line-clamp-2">
                                                <?php echo e($request['description']); ?>

                                            </p>

                                            <div class="mt-4 pt-3 border-t border-gray-50 flex items-center justify-between">
                                                <div class="flex -space-x-2">
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($request['assignee']): ?>
                                                        <div class="h-6 w-6 rounded-full bg-indigo-100 flex items-center justify-center text-[10px] font-bold text-indigo-700 border-2 border-white"
                                                            title="<?php echo e($request['assignee']['full_name']); ?>">
                                                            <?php echo e(substr($request['assignee']['full_name'], 0, 1)); ?>

                                                        </div>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </div>
                                                <div class="text-[10px] text-gray-400 font-medium">
                                                    <?php echo e($request['deadline'] ? \Carbon\Carbon::parse($request['deadline'])->format('M d') : 'No date'); ?>

                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <div class="py-8 text-center border-2 border-dashed border-gray-200 rounded-xl">
                                            <span class="text-xs text-gray-400 font-medium">No requests</span>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH /Users/naveenadicharla/Documents/DC OS/resources/views/livewire/creatives/requests-board.blade.php ENDPATH**/ ?>