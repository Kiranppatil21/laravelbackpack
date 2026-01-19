<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo e(trans('backpack::base.error_page.title', ['error' => $error_number])); ?></title>

        <?php echo $__env->make(backpack_view('inc.theme_styles'), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make(backpack_view('inc.styles'), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </head>
    <body>
      <?php echo $__env->yieldContent('content'); ?>
    </body>
</html>
<?php /**PATH /Users/admin/Desktop/laravelbackpack/vendor/backpack/theme-tabler/resources/views/errors/blank.blade.php ENDPATH**/ ?>