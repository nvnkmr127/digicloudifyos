<div class="overflow-x-auto overflow-y-hidden w-full">
    <table <?php echo e($attributes->merge(['class' => 'min-w-full divide-y divide-gray-200 border-collapse'])); ?>>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($header)): ?>
            <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                <?php echo e($header); ?>

            </thead>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <tbody class="bg-white divide-y divide-gray-200 text-sm">
            <?php echo e($slot); ?>

        </tbody>
    </table>
</div><?php /**PATH /Users/naveenadicharla/Documents/DC OS/resources/views/components/table.blade.php ENDPATH**/ ?>