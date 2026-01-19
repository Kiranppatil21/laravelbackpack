<?php
// Clean deployment reset script
// This will help you start fresh if there are issues

echo "<h2>🔄 Clean Deployment Reset</h2>";

echo "<h3>Current Directory Contents:</h3>";
$files = scandir('.');
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        echo "<p>📁 {$file}</p>";
    }
}

echo "<hr>";
echo "<h3>🧹 Cleanup Instructions:</h3>";
echo "<p>1. Delete ALL files in public_html except this debug file</p>";
echo "<p>2. Re-upload and extract your secureserve_hostinger_deployment.zip</p>";
echo "<p>3. Follow the deployment steps again from the beginning</p>";
echo "<p>4. Make sure to rename .env.hostinger to .env</p>";
echo "<p>5. Generate APP_KEY using the key generator script</p>";

echo "<hr>";
echo "<p><strong>⚠️ Delete this file after cleanup!</strong></p>";
?>