<?php
// One-time permission fixer for Hostinger shared hosting
// Run once in browser, then delete

echo "<h2>Setting File Permissions</h2>";

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
        $success = chmod($dir, 0755);
        if ($success) {
            echo "<p>✅ Set permissions for: {$dir}</p>";
        } else {
            echo "<p>❌ Failed to set permissions for: {$dir}</p>";
        }
        
        // Also set permissions for files in the directory
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                chmod($file->getPathname(), 0644);
            } elseif ($file->isDir()) {
                chmod($file->getPathname(), 0755);
            }
        }
    } else {
        echo "<p>⚠️ Directory not found: {$dir}</p>";
    }
}

echo "<h3>✅ Permission setup complete!</h3>";
echo "<p><strong>⚠️ Important:</strong> Delete this file now!</p>";
?>