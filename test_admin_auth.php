<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing admin authentication setup...\n\n";

// Check users
$users = \App\Models\User::all();
echo "Available users:\n";
foreach ($users as $user) {
    echo "- ID: {$user->id}, Name: {$user->name}, Email: {$user->email}\n";
}

// Test authentication
echo "\nTesting authentication...\n";
$testEmail = 'super_admin@example.test';
$user = \App\Models\User::where('email', $testEmail)->first();

if ($user) {
    echo "Found user: {$user->name} ({$user->email})\n";
    
    // Check if password is correct
    if (\Illuminate\Support\Facades\Hash::check('password', $user->password)) {
        echo "✅ Password 'password' is correct\n";
    } else {
        echo "❌ Password 'password' is incorrect\n";
    }
    
    // Check roles
    if (method_exists($user, 'roles')) {
        $roles = $user->roles()->pluck('name');
        echo "Roles: " . $roles->join(', ') . "\n";
        echo "Has Super Admin role: " . ($user->hasRole('Super Admin') ? 'Yes' : 'No') . "\n";
    }
    
} else {
    echo "❌ User {$testEmail} not found\n";
}

// Check Backpack auth configuration
echo "\nBackpack configuration:\n";
echo "Auth guard: " . config('backpack.base.guard', 'web') . "\n";
echo "Route prefix: " . config('backpack.base.route_prefix', 'admin') . "\n";
echo "Registration open: " . (config('backpack.base.registration_open', false) ? 'Yes' : 'No') . "\n";

echo "\nDone.\n";