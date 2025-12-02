<?php
// Set proper permissions for Laravel on Hostinger
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
    if (is_dir($dir)) {
        chmod($dir, 0755);
        echo "Set permissions for: $dir\n";
    }
}

// Set file permissions
$files = glob('storage/logs/*.log');
foreach ($files as $file) {
    if (is_file($file)) {
        chmod($file, 0644);
    }
}

echo "Permissions set successfully!\n";
?>
