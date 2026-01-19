<?php
/**
 * APP_KEY Generator for Hostinger
 * Upload this file to your Hostinger public_html folder
 * Visit: https://yourdomain.com/generate_app_key.php
 * Copy the generated key to your .env file
 * DELETE THIS FILE after use!
 */

echo "<!DOCTYPE html><html><head><title>Generate APP_KEY</title>";
echo "<style>body{font-family:Arial;padding:40px;background:#f5f5f5;}";
echo ".container{background:white;padding:30px;border-radius:8px;max-width:600px;margin:0 auto;box-shadow:0 2px 10px rgba(0,0,0,0.1);}";
echo "h1{color:#333;}code{background:#f4f4f4;padding:10px;display:block;margin:20px 0;border-radius:4px;word-break:break-all;}";
echo ".warning{background:#fff3cd;padding:15px;border-left:4px solid #ffc107;margin-top:20px;}</style></head><body>";
echo "<div class='container'>";
echo "<h1>🔐 APP_KEY Generator</h1>";
$key = 'base64:' . base64_encode(random_bytes(32));
echo "<p>Your generated APP_KEY:</p>";
echo "<code>APP_KEY=" . htmlspecialchars($key) . "</code>";
echo "<p><strong>Instructions:</strong></p>";
echo "<ol>";
echo "<li>Copy the entire line above (including APP_KEY=)</li>";
echo "<li>Add it to your <code>.env</code> file on the server</li>";
echo "<li><strong style='color:red;'>DELETE THIS FILE immediately after use!</strong></li>";
echo "</ol>";
echo "<div class='warning'><strong>⚠️ Security Warning:</strong> This file should not remain on your server. Delete it immediately after copying the key.</div>";
echo "</div></body></html>";
?>
