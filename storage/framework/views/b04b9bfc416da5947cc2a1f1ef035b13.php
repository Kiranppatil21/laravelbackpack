
<header data-bs-theme="<?php echo e($theme ?? 'system'); ?>" class="navbar-expand-lg top">
    <div class="container-fluid">
        
        <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        
        <a class="navbar-brand d-lg-none" href="<?php echo e(url(backpack_theme_config('home_link'))); ?>">
            <?php if(backpack_theme_config('project_logo')): ?>
                <img src="<?php echo e(backpack_theme_config('project_logo')); ?>" class="project-logo" style="height: 32px;" alt="<?php echo e(backpack_theme_config('project_name')); ?>">
            <?php else: ?>
                <i class="la la-shield-alt"></i>
            <?php endif; ?>
        </a>
    </div>
    
    <div class="collapse navbar-collapse" id="navbar-menu">
        <div class="d-print-none navbar navbar-expand-lg navbar-light">
            <div class="container-fluid">
                <ul class="navbar-nav">
                    
                    <?php echo $__env->make(backpack_view('inc.sidebar_content'), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </ul>

                
                <ul class="nav navbar-nav d-flex flex-row flex-shrink-0">
                    
                    <?php echo $__env->renderWhen(backpack_theme_config('options.showColorModeSwitcher'), backpack_view('layouts.partials.switch_theme'), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1])); ?>
                    
                    
                    <?php echo $__env->make(backpack_view('inc.topbar_left_content'), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    
                    
                    <?php echo $__env->make(backpack_view('inc.topbar_right_content'), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    
                    
                    <?php echo $__env->make(backpack_view('inc.menu_user_dropdown'), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </ul>
            </div>
        </div>
    </div>
</header>
<?php /**PATH /Users/admin/Desktop/laravelbackpack/resources/views/vendor/backpack/theme-tabler/layouts/_horizontal/menu_container.blade.php ENDPATH**/ ?>