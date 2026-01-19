# 🔧 Manual Deployment Steps (Without Helper Files)

Since the helper PHP files show 404 errors, follow these manual steps instead:

---

## 1️⃣ Test Database Connection

### Using Hostinger phpMyAdmin (Easiest)

1. **Go to Hostinger hPanel**
2. **Click on "Databases" → "phpMyAdmin"**
3. **Select your database from left sidebar**
4. **If you can see tables or empty database, connection works! ✓**

### Alternative: Create Test File in Public Folder

Create file: `public_html/public/testdb.php`

```php
<?php
// Test Database Connection
try {
    $host = 'localhost';
    $dbname = 'your_database_name';  // Change this
    $username = 'your_database_user'; // Change this
    $password = 'your_database_pass'; // Change this
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    echo "<h1 style='color:green;'>✓ Database Connected Successfully!</h1>";
    echo "<p>Database: $dbname</p>";
    
    // Test query
    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt->fetchAll();
    echo "<p>Tables found: " . count($tables) . "</p>";
    
    echo "<p style='color:red;'>DELETE THIS FILE NOW!</p>";
} catch(PDOException $e) {
    echo "<h1 style='color:red;'>✗ Connection Failed</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
```

**Visit:** `https://yourdomain.com/testdb.php`  
**Then DELETE the file!**

---

## 2️⃣ Run Migrations (Set Up Database Tables)

### Option A: Using SSH (If Available)

```bash
ssh username@yourdomain.com
cd public_html
php artisan migrate --force
php artisan db:seed --force --class=RoleSeeder
```

### Option B: Using Terminal in hPanel

Some Hostinger plans have built-in terminal:
1. hPanel → Advanced → Terminal
2. Run same commands as above

### Option C: Import Database SQL File

**On your local machine:**
```bash
cd /Users/admin/Desktop/laravelbackpack

# Export your local database
php artisan migrate:fresh --seed
mysqldump -u root -p laravelbackpack > database_export.sql
```

**On Hostinger:**
1. Go to **phpMyAdmin**
2. Select your database
3. Click **Import** tab
4. Choose `database_export.sql`
5. Click **Go**

### Option D: Create Migration Runner in Public Folder

Create file: `public_html/public/migrate.php`

```php
<?php
// CRITICAL: DELETE THIS FILE AFTER RUNNING!
ini_set('max_execution_time', 300);
set_time_limit(300);

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<!DOCTYPE html><html><head><title>Migrations</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1a1a1a;color:#0f0;}</style></head><body>";
echo "<h1>🗄️ Running Migrations...</h1><pre>";

try {
    // Run migrations
    echo "Running migrations...\n";
    $status = $kernel->call('migrate', ['--force' => true]);
    echo ($status === 0 ? "✓ Migrations completed\n" : "✗ Migrations failed\n");
    
    // Run seeders
    echo "\nRunning seeders...\n";
    $status = $kernel->call('db:seed', ['--force' => true, '--class' => 'RoleSeeder']);
    echo ($status === 0 ? "✓ Seeders completed\n" : "✗ Seeders failed\n");
    
    echo "\n✓ Database setup complete!\n";
    echo "</pre><p style='color:yellow;font-size:20px;'>⚠️ DELETE THIS FILE NOW!</p></body></html>";
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage();
    echo "</pre></body></html>";
}
?>
```

**Visit:** `https://yourdomain.com/migrate.php`  
**DELETE immediately after running!**

---

## 3️⃣ Fix Permissions (Set Correct Folder Permissions)

### Option A: Using Hostinger File Manager (Easiest)

**Right-click each folder → Permissions → Set to 755:**

1. `storage/` → 755
2. `storage/app/` → 755
3. `storage/app/public/` → 755
4. `storage/framework/` → 755
5. `storage/framework/cache/` → 755
6. `storage/framework/sessions/` → 755
7. `storage/framework/views/` → 755
8. `storage/logs/` → 755
9. `bootstrap/cache/` → 755

**755 means:**
- ✓ Owner: Read, Write, Execute
- ✓ Group: Read, Execute
- ✓ Public: Read, Execute

### Option B: Create Permission Fixer in Public Folder

Create file: `public_html/public/perms.php`

```php
<?php
// Fix Storage Permissions
echo "<!DOCTYPE html><html><head><title>Fix Permissions</title>";
echo "<style>body{font-family:Arial;padding:20px;}</style></head><body>";
echo "<h1>📁 Fixing Permissions...</h1><pre>";

$directories = [
    '../storage',
    '../storage/app',
    '../storage/app/public',
    '../storage/framework',
    '../storage/framework/cache',
    '../storage/framework/sessions',
    '../storage/framework/views',
    '../storage/logs',
    '../bootstrap/cache'
];

foreach ($directories as $dir) {
    $fullPath = __DIR__ . '/' . $dir;
    if (is_dir($fullPath)) {
        if (chmod($fullPath, 0755)) {
            echo "✓ $dir\n";
        } else {
            echo "✗ $dir (failed)\n";
        }
    } else {
        echo "? $dir (not found)\n";
    }
}

// Create storage link
$target = __DIR__ . '/../storage/app/public';
$link = __DIR__ . '/storage';

if (file_exists($link)) {
    echo "\n✓ Storage link exists\n";
} else {
    if (@symlink($target, $link)) {
        echo "\n✓ Storage link created\n";
    } else {
        echo "\n✗ Storage link failed (create manually)\n";
    }
}

echo "\n✓ Permissions fixed!\n";
echo "</pre></body></html>";
?>
```

**Visit:** `https://yourdomain.com/perms.php`

### Option C: Using SSH

```bash
ssh username@yourdomain.com
cd public_html
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
php artisan storage:link
```

---

## 4️⃣ Create Storage Symlink

### Option A: Create Symlink File in Public Folder

Create file: `public_html/public/link.php`

```php
<?php
// Create Storage Symlink
$target = __DIR__ . '/../storage/app/public';
$link = __DIR__ . '/storage';

echo "<!DOCTYPE html><html><body style='padding:40px;font-family:Arial;'>";
echo "<h1>🔗 Storage Symlink</h1>";

if (file_exists($link)) {
    echo "<p style='color:orange;'>⚠️ Symlink already exists</p>";
} else {
    if (@symlink($target, $link)) {
        echo "<p style='color:green;'>✓ Symlink created successfully!</p>";
    } else {
        echo "<p style='color:red;'>✗ Failed to create symlink</p>";
        echo "<p>Manual steps:</p>";
        echo "<ol>";
        echo "<li>Create folder: <code>public_html/public/storage</code></li>";
        echo "<li>Contact Hostinger support to enable symlink or use storage workaround</li>";
        echo "</ol>";
    }
}

echo "</body></html>";
?>
```

**Visit:** `https://yourdomain.com/link.php`

### Option B: Using SSH

```bash
cd public_html
php artisan storage:link
```

### Option C: If Symlink Fails (Shared Hosting Limitation)

Update `.env` to use local disk instead:
```env
FILESYSTEM_DISK=public
```

Then update `config/filesystems.php`:
```php
'default' => env('FILESYSTEM_DISK', 'public'),
```

---

## 5️⃣ Cache Configurations (Optimize)

### Option A: Create Optimizer in Public Folder

Create file: `public_html/public/optimize.php`

```php
<?php
// Production Optimization
ini_set('max_execution_time', 300);

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<!DOCTYPE html><html><head><title>Optimize</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#f8f9fa;}</style></head><body>";
echo "<h1>⚡ Optimizing Application...</h1><pre>";

try {
    echo "Clearing old caches...\n";
    $kernel->call('config:clear');
    $kernel->call('route:clear');
    $kernel->call('view:clear');
    $kernel->call('cache:clear');
    echo "✓ Old caches cleared\n\n";
    
    echo "Building production caches...\n";
    $kernel->call('config:cache');
    $kernel->call('route:cache');
    $kernel->call('view:cache');
    echo "✓ Production caches built\n\n";
    
    echo "✓ Optimization complete!\n";
    echo "</pre><p>You can keep this file for future updates.</p></body></html>";
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage();
    echo "</pre></body></html>";
}
?>
```

**Visit:** `https://yourdomain.com/optimize.php`  
**(Keep this file for future use)**

### Option B: Using SSH

```bash
cd public_html
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📋 Quick Summary

All files should be created in: `public_html/public/` folder

| Task | File Name | URL | Keep/Delete |
|------|-----------|-----|-------------|
| Test DB | `testdb.php` | `/testdb.php` | DELETE after |
| Migrations | `migrate.php` | `/migrate.php` | DELETE after |
| Permissions | `perms.php` | `/perms.php` | Can keep |
| Storage Link | `link.php` | `/link.php` | DELETE after |
| Optimize | `optimize.php` | `/optimize.php` | KEEP for updates |

---

## ✅ Complete Setup Checklist

1. [ ] Generated APP_KEY and added to `.env`
2. [ ] Tested database connection
3. [ ] Ran migrations (tables created)
4. [ ] Fixed folder permissions (755)
5. [ ] Created storage symlink
6. [ ] Cached configurations
7. [ ] Deleted security-sensitive files
8. [ ] Tested website: `https://yourdomain.com`
9. [ ] Tested admin: `https://yourdomain.com/admin`

---

## 🆘 If You Have SSH Access

All these steps become much simpler:

```bash
# Connect to server
ssh username@yourdomain.com

# Navigate to project
cd public_html

# Run everything
php artisan migrate --force
php artisan db:seed --force
chmod -R 755 storage bootstrap/cache
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🔒 Important Security Notes

**Files to DELETE after use:**
- ❌ `testdb.php` - Contains DB credentials
- ❌ `migrate.php` - Can modify database
- ❌ `link.php` - No longer needed

**Files you can KEEP:**
- ✓ `optimize.php` - Safe, useful for updates
- ✓ `perms.php` - Safe, useful if issues occur

**Always:**
- Set `APP_DEBUG=false` in production
- Never expose `.env` file
- Keep backups before making changes

---

Need help with any specific step? Let me know which method you'd like to use!
