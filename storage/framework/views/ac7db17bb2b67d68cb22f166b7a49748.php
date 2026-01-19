<?php
  $error_number ??= 400;
  $shouldEscape = ! in_array('developer-error-exception', $exception->getHeaders());
?>

<?php $__env->startSection('title'); ?>
  <?php echo e(trans('backpack::base.error_page.'.$error_number)); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('description'); ?>
  <?php echo $exception?->getMessage() && config('app.debug') ? ($shouldEscape ? e($exception->getMessage()) : $exception->getMessage()) : trans('backpack::base.error_page.message_4xx', [
    'href_back' => 'href="javascript:history.back()"',
    'href_homepage' => 'href="'.url('').'"',
  ]); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make(backpack_view('errors.layout'), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/admin/Desktop/laravelbackpack/vendor/backpack/crud/src/resources/views/ui/errors/4xx.blade.php ENDPATH**/ ?>