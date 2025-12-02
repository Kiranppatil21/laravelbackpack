<?php

use App\Models\User;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$email = $argv[1] ?? null;

if (! $email) {
    fwrite(STDERR, "Usage: php tools/debug_user_password.php <email>\n");
    exit(1);
}

$user = User::where('email', $email)->first();

if (! $user) {
    fwrite(STDERR, "User not found\n");
    exit(1);
}

fwrite(STDOUT, json_encode([
    'email' => $user->email,
    'password' => $user->getAuthPassword(),
    'starts_with' => str_starts_with($user->getAuthPassword(), '$2y$'),
    'updated_at' => (string) $user->updated_at,
]) . "\n");
