<?php
  $defaultBreadcrumbs = [
    trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
    $crud->entity_name_plural => url($crud->route),
    trans('backpack::crud.add') => false,
  ];

  // if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
  $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
?>

<?php $__env->startSection('header'); ?>
    <section class="header-operation container-fluid animated fadeIn d-flex mb-2 align-items-baseline d-print-none" bp-section="page-header">
        <h1 class="text-capitalize mb-0" bp-section="page-heading"><?php echo $crud->getHeading() ?? $crud->entity_name_plural; ?></h1>
        <p class="ms-2 ml-2 mb-0" bp-section="page-subheading"><?php echo $crud->getSubheading() ?? trans('backpack::crud.add').' '.$crud->entity_name; ?>.</p>
        <?php if($crud->hasAccess('list')): ?>
            <p class="mb-0 ms-2 ml-2" bp-section="page-subheading-back-button">
                <small>
                    <a href="<?php echo e(url($crud->route)); ?>" class="d-print-none font-sm">
                        <span><i class="la la-angle-double-<?php echo e(config('backpack.base.html_direction') == 'rtl' ? 'right' : 'left'); ?>"></i> <?php echo e(trans('backpack::crud.back_to_all')); ?> <span><?php echo e($crud->entity_name_plural); ?></span></span>
                    </a>
                </small>
            </p>
        <?php endif; ?>
    </section>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="row" bp-section="crud-operation-create">
	<div class="<?php echo e($crud->getCreateContentClass()); ?>">
		

		<?php echo $__env->make('crud::inc.grouped_errors', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

		  <form method="post"
		  		action="<?php echo e(url($crud->route)); ?>"
				<?php if($crud->hasUploadFields('create')): ?>
				enctype="multipart/form-data"
				<?php endif; ?>
		  		>
			  <?php echo csrf_field(); ?>

		      
		      <?php if(view()->exists('vendor.backpack.crud.form_content')): ?>
		      	<?php echo $__env->make('vendor.backpack.crud.form_content', [ 'fields' => $crud->fields(), 'action' => 'create' ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
		      <?php else: ?>
		      	<?php echo $__env->make('crud::form_content', [ 'fields' => $crud->fields(), 'action' => 'create' ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
		      <?php endif; ?>
                
                <div class="d-none" id="parentLoadedAssets"><?php echo e(json_encode(Basset::loaded())); ?></div>
	          <?php echo $__env->make('crud::inc.form_save_buttons', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
		  </form>
	</div>
</div>

<?php $__env->stopSection(); ?>


<?php echo $__env->make(backpack_view('blank'), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/admin/Desktop/laravelbackpack/vendor/backpack/crud/src/resources/views/crud/create.blade.php ENDPATH**/ ?>