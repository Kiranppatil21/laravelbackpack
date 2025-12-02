<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $client = App\Models\Client::first();
    if (! $client) {
        echo "No client found.\n";
        exit(0);
    }

    $companies = App\Models\Company::select('id','name')->get();
    $agencies = App\Models\Agency::select('id','name')->get();
    $designations = App\Models\Designation::select('id','name')->get();

    // Provide an empty ViewErrorBag so Blade `@error` directives and `$errors` checks work
    $errors = new \Illuminate\Support\ViewErrorBag();

    $view = view('admin.client.edit', [
        'client' => $client,
        'companies' => $companies,
        'agencies' => $agencies,
        'designations' => $designations,
        'taxTypes' => App\Models\Client::getTaxTypes(),
        'taxStatuses' => App\Models\Client::getTaxStatuses(),
        'errors' => $errors,
    ]);

    echo $view->render();
} catch (Exception $e) {
    echo "Exception: " . get_class($e) . "\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

