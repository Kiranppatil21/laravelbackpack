<?php
// Quick test script to verify account page accessibility
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Test if the route exists
$request = Illuminate\Http\Request::create('/admin/edit-account-info', 'GET');
$response = $kernel->handle($request);

echo "Route Test Results:\n";
echo "==================\n";
echo "HTTP Status: " . $response->getStatusCode() . "\n";

if ($response->getStatusCode() === 302) {
    echo "Response: Redirect (likely to login) - This is CORRECT behavior\n";
    echo "Location: " . $response->headers->get('Location') . "\n";
    echo "\nThis means you need to LOGIN FIRST at /admin/login\n";
    echo "Then you can access the account page.\n";
} elseif ($response->getStatusCode() === 200) {
    echo "Response: Page loads successfully\n";
} else {
    echo "Response: Unexpected status code\n";
    echo "Headers: " . json_encode($response->headers->all(), JSON_PRETTY_PRINT) . "\n";
}

echo "\n=== SOLUTION ===\n";
echo "1. Go to: http://127.0.0.1:8000/admin/login\n";
echo "2. Login with: super@admin.com / password123\n";
echo "3. Then access: http://127.0.0.1:8000/admin/edit-account-info\n";
echo "================\n";