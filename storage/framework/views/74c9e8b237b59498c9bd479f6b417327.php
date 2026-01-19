<?php if(count($columns)): ?>
    <table class="table table-striped m-0 p-0">
        <tbody>
        <?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td <?php if($loop->index === 0): ?> class="border-top-0" <?php endif; ?>>
                    <strong><?php echo $column['label']; ?><?php if(!empty($column['label'])): ?>:<?php endif; ?></strong>
                </td>
                <td <?php if($loop->index === 0): ?> class="border-top-0" <?php endif; ?>>
                    <?php
                        // create a list of paths to column blade views
                        // including the configured view_namespaces
                        $columnPaths = array_map(function($item) use ($column) {
                            return $item.'.'.$column['type'];
                        }, \Backpack\CRUD\ViewNamespaces::getFor('columns'));

                        // but always fall back to the stock 'text' column
                        // if a view doesn't exist
                        if (!in_array('crud::columns.text', $columnPaths)) {
                            $columnPaths[] = 'crud::columns.text';
                        }
                    ?>
                    <?php echo $__env->first($columnPaths, array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php if($crud->buttons()->where('stack', 'line')->count() && ($displayActionsColumn ?? true)): ?>
            <tr>
                <td>
                    <strong><?php echo e(trans('backpack::crud.actions')); ?></strong>
                </td>
                <td>
                    <?php echo $__env->make('crud::inc.button_stack', ['stack' => 'line'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php /**PATH /Users/admin/Desktop/laravelbackpack/vendor/backpack/crud/src/resources/views/crud/inc/show_table.blade.php ENDPATH**/ ?>