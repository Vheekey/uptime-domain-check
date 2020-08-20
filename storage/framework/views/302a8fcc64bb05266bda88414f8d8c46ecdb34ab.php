<?php $__env->startComponent('mail::message'); ?>
# Hi,

This is to inform you that <?php echo e($endpoints->uri); ?> has been down <?php echo e($endpoints->status->created_at->diffForHumans()); ?>.

Thanks,<br>
Regards
<?php if (isset($__componentOriginal2dab26517731ed1416679a121374450d5cff5e0d)): ?>
<?php $component = $__componentOriginal2dab26517731ed1416679a121374450d5cff5e0d; ?>
<?php unset($__componentOriginal2dab26517731ed1416679a121374450d5cff5e0d); ?>
<?php endif; ?>
<?php echo $__env->renderComponent(); ?>
<?php /**PATH C:\laragon\www\monitoring\resources\views/emails/downtime.blade.php ENDPATH**/ ?>