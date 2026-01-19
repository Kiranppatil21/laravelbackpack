<?php if(backpack_theme_config('breadcrumbs') && isset($breadcrumbs) && is_array($breadcrumbs) && count($breadcrumbs)): ?>
	<nav aria-label="breadcrumb" class="d-none d-lg-block">
	  <ol class="breadcrumb bg-transparent p-0 mx-3 <?php echo e(backpack_theme_config('html_direction') == 'rtl' ? 'justify-content-start' : 'justify-content-end'); ?>">
	  	<?php $__currentLoopData = $breadcrumbs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
	  		<?php if($link): ?>
			    <li class="breadcrumb-item text-capitalize"><a href="<?php echo e($link); ?>"><?php echo e($label); ?></a></li>
	  		<?php else: ?>
			    <li class="breadcrumb-item text-capitalize active" aria-current="page"><?php echo e($label); ?></li>
	  		<?php endif; ?>
	  	<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
	  </ol>
	</nav>
<?php endif; ?>
<?php /**PATH /Users/admin/Desktop/laravelbackpack/vendor/backpack/theme-tabler/resources/views/inc/breadcrumbs.blade.php ENDPATH**/ ?>