
<?php echo $__env->renderWhen(!isset($field['wrapper']) || $field['wrapper'] !== false, 'crud::fields.inc.wrapper_start', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1])); ?>
	<?php echo $field['value']; ?>

<?php echo $__env->renderWhen(!isset($field['wrapper']) || $field['wrapper'] !== false, 'crud::fields.inc.wrapper_end', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1])); ?><?php /**PATH /Users/admin/Desktop/laravelbackpack/vendor/backpack/crud/src/resources/views/crud/fields/custom_html.blade.php ENDPATH**/ ?>