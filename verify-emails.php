<?php

use App\Models\User;

// Mark all users as email verified
$users = User::all();
$verifiedCount = 0;

foreach ($users as $user) {
    if (is_null($user->email_verified_at)) {
        $user->email_verified_at = now();
        $user->save();
        $verifiedCount++;
        echo "✅ Verified: {$user->email}\n";
    } else {
        echo "✓ Already verified: {$user->email}\n";
    }
}

echo "\n📊 Summary:\n";
echo "Total users: " . $users->count() . "\n";
echo "Newly verified: {$verifiedCount}\n";
echo "All users are now verified and can login directly!\n";