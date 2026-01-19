<?php
/**
 * Fix Storage Permissions for Hostinger
 * Visit: https://yourdomain.com/fix_permissions.php
 */

echo "<!DOCTYPE html><html><head><title>Fix Permissions</title>";
echo "<style>body{font-family:Arial;padding:20px;}pre{background:#f4f4f4;padding:15px;}</style></head><body>";
echo "<h1>📁 Fix Storage Permissions</h1><pre>";

$directories = [
    'storage',
    'storage/app',
    'storage/app/public',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
    'bootstrap/cache'
];

foreach ($directories as $dir) {
    if (is_dir(__DIR__ . '/' . $dir)) {
        chmod(__DIR__ . '/' . $dir, 0755);
        echo "✓ {$dir}\n";
    } else {
        echo "✗ {$dir} (not found)\n";
    }
}

// Create storage link
$target = __DIR__ . '/storage/app/public';
$link = __DIR__ . '/public/storage';

if (file_exists($link)) {
    echo "\n✓ Storage link already exists\n";
} else {
    if (symlink($target, $link)) {
        echo "\n✓ Storage link created\n";
    } else {
        echo "\n✗ Failed to create storage link\n";
    }
}

echo "\n✓ Permissions fixed!\n";
echo "</pre></body></html>";
?>
