Route::get('/test-employee-form', function () {
    $controller = app()->make('\App\Http\Controllers\Admin\EmployeeCrudController');
    $controller->setup();
    
    // Manually set up the CRUD context
    $controller->crud->setOperation('create');
    
    try {
        $controller->setupCreateOperation();
        return "✅ Employee form setup works correctly!";
    } catch (Exception $e) {
        return "❌ Error: " . $e->getMessage() . "\nFile: " . $e->getFile() . ":" . $e->getLine();
    }
});