<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$email = 'super_admin@example.test';
$password = 'password';

$user = App\Models\User::where('email', $email)->first();

if ($user) {
    echo "✓ User found: {$user->name} ({$user->email})\n";
    echo "✓ Password hash exists: " . (!empty($user->password) ? 'Yes' : 'No') . "\n";
    
    if (Illuminate\Support\Facades\Hash::check($password, $user->password)) {
        echo "✓ Password 'password' is CORRECT\n";
    } else {
        echo "✗ Password 'password' is WRONG\n";
    }
    
    $roles = $user->roles->pluck('name')->toArray();
    echo "✓ Roles: " . implode(', ', $roles) . "\n";
} else {
    echo "✗ User not found with email: {$email}\n";
}

echo "\n--- All Demo Users ---\n";
$demoUsers = App\Models\User::whereIn('email', [
    'super_admin@example.test',
    'agency_owner@example.test',
    'hr@example.test',
    'client@example.test',
    'guard/employee@example.test',
])->get(['email', 'name']);

foreach ($demoUsers as $user) {
    echo "• {$user->email} - {$user->name}\n";
}
