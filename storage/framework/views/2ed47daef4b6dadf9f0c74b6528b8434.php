<div>
     <?php $__env->slot('header', null, []); ?> 
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <?php echo e(__('Workflow Monitoring')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Automation Rules -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                        <div class="p-8">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-xl font-bold text-gray-900 tracking-tight">Active Automation Rules</h3>
                                <button
                                    class="text-indigo-600 font-bold text-xs uppercase tracking-widest hover:text-indigo-800 transition">Create
                                    Rule</button>
                            </div>

                            <div class="space-y-4">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <div
                                        class="flex items-center p-4 bg-gray-50 rounded-2xl border border-gray-100 group hover:border-indigo-100 hover:bg-white transition-all">
                                        <div
                                            class="h-12 w-12 rounded-xl bg-white shadow-sm flex items-center justify-center text-indigo-600 mr-4">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-bold text-gray-900"><?php echo e($rule->name); ?></div>
                                            <div class="text-xs text-gray-500 mt-0.5">Trigger: <?php echo e($rule->trigger_event); ?>

                                            </div>
                                        </div>
                                        <div class="text-right px-4">
                                            <div class="text-sm font-bold text-gray-900"><?php echo e($rule->logs_count); ?></div>
                                            <div class="text-[10px] text-gray-400 font-medium uppercase tracking-tighter">
                                                Executions</div>
                                        </div>
                                        <div class="ps-4">
                                            <div
                                                class="h-2.5 w-2.5 rounded-full <?php echo e($rule->is_active ? 'bg-green-500' : 'bg-gray-300'); ?>">
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <div
                                        class="py-12 text-center bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                                        <p class="text-sm text-gray-400 font-medium">No automation rules configured</p>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Workflow Timeline -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                        <div class="p-8">
                            <h3 class="text-xl font-bold text-gray-900 tracking-tight mb-6">Execution Timeline</h3>
                            <div class="flow-root">
                                <ul class="-mb-8">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recentLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <li>
                                            <div class="relative pb-8">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$loop->last): ?>
                                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-100"
                                                        aria-hidden="true"></span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <div class="relative flex space-x-3">
                                                    <div>
                                                        <span
                                                            class="h-8 w-8 rounded-full <?php echo e($log->status === 'success' ? 'bg-indigo-100 text-indigo-600' : 'bg-red-100 text-red-600'); ?> flex items-center justify-center ring-8 ring-white">
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->status === 'success'): ?>
                                                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                                    <path fill-rule="evenodd"
                                                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                                        clip-rule="evenodd" />
                                                                </svg>
                                                            <?php else: ?>
                                                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                                    <path fill-rule="evenodd"
                                                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                                        clip-rule="evenodd" />
                                                                </svg>
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        </span>
                                                    </div>
                                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                        <div>
                                                            <p class="text-sm text-gray-500 font-medium">
                                                                <span
                                                                    class="font-bold text-gray-900"><?php echo e($log->rule->name ?? 'System Event'); ?></span>
                                                                executed successfully.
                                                            </p>
                                                        </div>
                                                        <div
                                                            class="text-right text-xs whitespace-nowrap text-gray-400 font-bold">
                                                            <?php echo e($log->created_at->diffForHumans()); ?>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <div
                                            class="py-8 text-center bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                                            <p class="text-sm text-gray-400 font-medium">No recent execution logs</p>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Health Stats -->
                <div class="space-y-6">
                    <div class="bg-indigo-600 rounded-2xl p-8 text-white shadow-xl shadow-indigo-100">
                        <div class="text-xs font-bold uppercase tracking-widest opacity-70 mb-2">Automation Health</div>
                        <div class="text-3xl font-black mb-6">99.8%</div>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center text-xs border-t border-indigo-500 pt-4">
                                <span class="opacity-70">Rules Active</span>
                                <span class="font-black"><?php echo e($rules->where('is_active', true)->count()); ?></span>
                            </div>
                            <div class="flex justify-between items-center text-xs border-t border-indigo-500 pt-4">
                                <span class="opacity-70">Total Executions</span>
                                <span class="font-black"><?php echo e($rules->sum('logs_count')); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH /Users/naveenadicharla/Documents/DC OS/resources/views/livewire/workflow-monitoring/dashboard.blade.php ENDPATH**/ ?>