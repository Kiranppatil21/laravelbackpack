<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

try {
    // Create role if missing
    $roleModel = App\Models\Role::firstOrCreate(
        ['name' => 'Super Admin'],
        ['guard_name' => config('auth.defaults.guard', 'web')]
    );

    // Create user
    $email = 'super_admin@example.test';
    $password = 'password';

    $user = App\Models\User::firstOrCreate(
        ['email' => $email],
        ['name' => 'Super Admin', 'password' => $password]
    );

    // Assign role
    if (! $user->hasRole('Super Admin')) {
        $user->assignRole('Super Admin');
    }

    // Log in the user into the session
    Auth::guard(config('auth.defaults.guard'))->login($user);

    // Regenerate and persist session
    session()->regenerate();
    session()->save();

    $cookieName = config('session.cookie');
    $sessionId = session()->getId();

    echo "SESSION_COOKIE_NAME={$cookieName}\n";
    echo "SESSION_ID={$sessionId}\n";

    // Also show the session record location for debugging (if using database driver)
    $driver = config('session.driver');
    echo "SESSION_DRIVER={$driver}\n";
    if ($driver === 'database') {
        $conn = config('session.connection') ?: config('database.default');
        echo "Session saved. Look in table: " . config('session.table') . "\n";
    }

    echo "OK\n";
} catch (Exception $e) {
    echo "Exception: " . get_class($e) . "\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
