
<?php echo $__env->make('crud::fields.inc.wrapper_start', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <label><?php echo $field['label']; ?></label>
    <?php echo $__env->make('crud::fields.inc.translatable_icon', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <textarea
    	name="<?php echo e($field['name']); ?>"
        <?php echo $__env->make('crud::fields.inc.attributes', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    	><?php echo e(old_empty_or_null($field['name'], '') ??  $field['value'] ?? $field['default'] ?? ''); ?></textarea>

    
    <?php if(isset($field['hint'])): ?>
        <p class="help-block"><?php echo $field['hint']; ?></p>
    <?php endif; ?>
<?php echo $__env->make('crud::fields.inc.wrapper_end', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH /Users/admin/Desktop/laravelbackpack/vendor/backpack/crud/src/resources/views/crud/fields/textarea.blade.php ENDPATH**/ ?>