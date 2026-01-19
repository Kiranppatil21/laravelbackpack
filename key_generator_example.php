<?php
// One-time key generator for Hostinger deployment
// Upload this to your public_html, run once in browser, then delete

require_once 'vendor/autoload.php';

// Generate secure application key
$key = 'base64:' . base64_encode(random_bytes(32));

echo "<h2>Laravel Application Key Generated</h2>";
echo "<p><strong>Copy this key to your .env file:</strong></p>";
echo "<pre>APP_KEY=" . $key . "</pre>";
echo "<p><strong>⚠️ Important:</strong> Delete this file immediately after copying the key!</p>";

// Also check if Laravel is properly loaded
if (class_exists('Illuminate\Foundation\Application')) {
    echo "<p>✅ Laravel framework loaded successfully</p>";
} else {
    echo "<p>❌ Laravel framework not found - check composer dependencies</p>";
}
?>