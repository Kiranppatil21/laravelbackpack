# 🛠️ Manual Hostinger Setup (Without Helper Scripts)

Since helper PHP files show 404, follow these manual alternatives.

---

## ✅ Step 1: Test Database Connection

### Method A: Using Hostinger phpMyAdmin (Recommended)

1. **Go to Hostinger hPanel**
2. **Click "Databases" → "phpMyAdmin"**
3. **Login with your database credentials**
4. **If you can see your database** → Connection works! ✓

### Method B: Create Simple Test File

**In Hostinger File Manager:**
1. Navigate to `public_html/public/`
2. Create file: `db-test.php`
3. Add this code:

```php
<?php
$host = 'localhost';
$db = 'your_database_name';
$user = 'your_database_user';
$pass = 'your_database_password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    echo "✓ Database connection successful!";
} catch (PDOException $e) {
    echo "✗ Connection failed: " . $e->getMessage();
}
?>
```

4. Visit: `https://yourdomain.com/db-test.php`
5. Delete file after testing

---

## ✅ Step 2: Run Database Migrations

### Method A: Using SSH (If Available)

```bash
ssh username@yourdomain.com
cd public_html
php artisan migrate --force
php artisan db:seed --force --class=RoleSeeder
```

### Method B: Import SQL Manually (Without SSH)

**On your local machine:**

```bash
cd /Users/admin/Desktop/laravelbackpack
php artisan migrate:fresh --seed
mysqldump -u root -p laravelbackpack > full_database.sql
```

**Upload to Hostinger:**

1. Go to Hostinger **phpMyAdmin**
2. Select your database
3. Click **Import** tab
4. Choose `full_database.sql`
5. Click **Go**

### Method C: Create Migration Runner File

**In Hostinger File Manager: `public_html/public/migrate.php`**

```php
<?php
set_time_limit(300);
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<pre>";
echo "Running migrations...\n\n";

try {
    $kernel->call('migrate', ['--force' => true]);
    echo "\n✓ Migrations completed!\n\n";
    
    echo "Running Role Seeder...\n\n";
    $kernel->call('db:seed', ['--class' => 'RoleSeeder', '--force' => true]);
    echo "\n✓ Seeder completed!\n";
} catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage();
}

echo "\n\nDELETE THIS FILE NOW!";
echo "</pre>";
?>
```

Visit: `https://yourdomain.com/migrate.php`  
**Delete immediately after use!**

---

## ✅ Step 3: Fix File Permissions

### Method A: Via Hostinger File Manager

**Set these permissions (Right-click → Change Permissions):**

```
storage/                     → 755
storage/app/                 → 755
storage/app/public/          → 755
storage/framework/           → 755
storage/framework/cache/     → 755
storage/framework/sessions/  → 755
storage/framework/views/     → 755
storage/logs/                → 755
bootstrap/cache/             → 755
```

**In File Manager:**
1. Right-click on `storage` folder
2. Click "Change Permissions"
3. Check: Read, Write, Execute for Owner
4. Check: Read, Execute for Group and Public
5. Check "Apply to subdirectories"
6. Click "Change"

Repeat for `bootstrap/cache/`

### Method B: Create Permission Fixer

**File: `public_html/public/perms.php`**

```php
<?php
echo "<pre>";
$dirs = [
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

foreach ($dirs as $dir) {
    if (is_dir(__DIR__.'/'.$dir)) {
        chmod(__DIR__.'/'.$dir, 0755);
        echo "✓ Fixed: $dir\n";
    } else {
        echo "✗ Not found: $dir\n";
    }
}

// Create storage symlink
$target = __DIR__.'/../storage/app/public';
$link = __DIR__.'/storage';

if (file_exists($link)) {
    echo "\n✓ Storage link exists\n";
} else {
    if (symlink($target, $link)) {
        echo "\n✓ Storage link created\n";
    } else {
        echo "\n✗ Failed to create storage link\n";
    }
}

echo "</pre>";
?>
```

Visit: `https://yourdomain.com/perms.php`

---

## ✅ Step 4: Create Storage Symlink

### Method A: Via File Manager (Manual)

1. Go to `public_html/public/`
2. Check if `storage` folder/link exists
3. If not, use Method B

### Method B: Create Symlink Script

**File: `public_html/public/link.php`**

```php
<?php
$target = __DIR__.'/../storage/app/public';
$link = __DIR__.'/storage';

if (file_exists($link)) {
    echo "Storage link already exists!";
} else {
    if (symlink($target, $link)) {
        echo "✓ Storage symlink created successfully!";
    } else {
        echo "✗ Failed to create symlink. Contact support.";
    }
}
?>
```

Visit: `https://yourdomain.com/link.php`

---

## ✅ Step 5: Cache Configuration (Optimize)

### Method A: Using SSH

```bash
cd public_html
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Method B: Create Optimization Script

**File: `public_html/public/optimize.php`**

```php
<?php
set_time_limit(300);
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<pre>";
echo "Clearing caches...\n";
$kernel->call('config:clear');
$kernel->call('route:clear');
$kernel->call('view:clear');
$kernel->call('cache:clear');
echo "✓ Cleared\n\n";

echo "Building caches...\n";
$kernel->call('config:cache');
$kernel->call('route:cache');
$kernel->call('view:cache');
echo "✓ Cached\n\n";

echo "✓ Optimization complete!";
echo "</pre>";
?>
```

Visit: `https://yourdomain.com/optimize.php`

**Keep this file** - run it after any config changes.

---

## ✅ Step 6: Create Admin User

### Method A: Using SSH

```bash
cd public_html
php artisan tinker
```

Then run:
```php
$user = new App\Models\User();
$user->name = 'Admin';
$user->email = 'admin@example.com';
$user->password = bcrypt('password123');
$user->save();
$user->assignRole('Super Admin');
```

### Method B: Via phpMyAdmin

1. Go to **phpMyAdmin**
2. Select your database
3. Click **users** table
4. Click **Insert** tab
5. Fill in:
   - `name`: Admin
   - `email`: admin@example.com
   - `password`: Use this encrypted version:
     ```
     $2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5PqT6z8VLJqQm
     ```
     (This is: `password`)
6. Click **Go**

Then add role:
1. Go to **model_has_roles** table
2. Insert:
   - `role_id`: 1 (Super Admin)
   - `model_type`: App\Models\User
   - `model_id`: [your user id]

### Method C: Create User Script

**File: `public_html/public/create-admin.php`**

```php
<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

try {
    $user = App\Models\User::firstOrCreate(
        ['email' => 'admin@example.com'],
        [
            'name' => 'Admin',
            'password' => bcrypt('password123'),
            'email_verified_at' => now()
        ]
    );
    
    $user->assignRole('Super Admin');
    
    echo "✓ Admin user created!<br>";
    echo "Email: admin@example.com<br>";
    echo "Password: password123<br>";
    echo "<br><strong>Change password after login!</strong>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
```

Visit: `https://yourdomain.com/create-admin.php`  
**Delete after use!**

---

## ✅ Complete Manual Setup Checklist

### Pre-Setup
- [ ] Database created in Hostinger
- [ ] Files uploaded to `public_html/`
- [ ] `.env` file created with correct credentials
- [ ] `APP_KEY` generated and added to `.env`

### Database Setup
- [ ] Database connection tested (phpMyAdmin or db-test.php)
- [ ] Migrations run (via SSH, SQL import, or migrate.php)
- [ ] Tables visible in phpMyAdmin

### Permissions
- [ ] `storage/` folders set to 755
- [ ] `bootstrap/cache/` set to 755
- [ ] Storage symlink created

### Optimization
- [ ] Config cached (via SSH or optimize.php)
- [ ] Routes cached
- [ ] Views cached

### User Setup
- [ ] Admin user created
- [ ] Can login at `/admin`

### Cleanup
- [ ] db-test.php deleted
- [ ] migrate.php deleted (if used)
- [ ] create-admin.php deleted (if used)
- [ ] Keep optimize.php for future use

---

## 🚀 Quick Setup Script (All-in-One)

**File: `public_html/public/setup.php`**

```php
<?php
set_time_limit(600);
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<!DOCTYPE html><html><head>";
echo "<style>body{font-family:monospace;padding:20px;background:#1e1e1e;color:#0f0;}";
echo "h2{color:#0ff;}.error{color:#f00;}.success{color:#0f0;}</style></head><body>";
echo "<h1>🚀 Laravel Setup for Hostinger</h1>";

// Test Database
echo "<h2>1. Testing Database Connection...</h2>";
try {
    DB::connection()->getPdo();
    echo "<p class='success'>✓ Database connected</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Database failed: " . $e->getMessage() . "</p>";
    exit;
}

// Run Migrations
echo "<h2>2. Running Migrations...</h2>";
try {
    $kernel->call('migrate', ['--force' => true]);
    echo "<p class='success'>✓ Migrations completed</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Migrations failed: " . $e->getMessage() . "</p>";
}

// Seed Roles
echo "<h2>3. Seeding Roles...</h2>";
try {
    $kernel->call('db:seed', ['--class' => 'RoleSeeder', '--force' => true]);
    echo "<p class='success'>✓ Roles seeded</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Seeding failed: " . $e->getMessage() . "</p>";
}

// Fix Permissions
echo "<h2>4. Setting Permissions...</h2>";
$dirs = ['../storage', '../bootstrap/cache'];
foreach ($dirs as $dir) {
    $path = __DIR__.'/'.$dir;
    if (is_dir($path)) {
        chmod($path, 0755);
        echo "<p class='success'>✓ Fixed: $dir</p>";
    }
}

// Create Storage Link
echo "<h2>5. Creating Storage Link...</h2>";
$target = __DIR__.'/../storage/app/public';
$link = __DIR__.'/storage';
if (!file_exists($link)) {
    symlink($target, $link);
    echo "<p class='success'>✓ Storage link created</p>";
} else {
    echo "<p class='success'>✓ Storage link exists</p>";
}

// Cache Config
echo "<h2>6. Caching Configuration...</h2>";
try {
    $kernel->call('config:cache');
    $kernel->call('route:cache');
    $kernel->call('view:cache');
    echo "<p class='success'>✓ Caches built</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Caching failed: " . $e->getMessage() . "</p>";
}

// Create Admin User
echo "<h2>7. Creating Admin User...</h2>";
try {
    $user = App\Models\User::firstOrCreate(
        ['email' => 'admin@example.com'],
        [
            'name' => 'Admin',
            'password' => bcrypt('password123'),
            'email_verified_at' => now()
        ]
    );
    $user->assignRole('Super Admin');
    echo "<p class='success'>✓ Admin user created</p>";
    echo "<p>Email: <strong>admin@example.com</strong></p>";
    echo "<p>Password: <strong>password123</strong></p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ User creation failed: " . $e->getMessage() . "</p>";
}

echo "<h2>✅ Setup Complete!</h2>";
echo "<p style='color:#ff0;'>⚠️ DELETE THIS FILE NOW!</p>";
echo "<p>Visit: <a href='/' style='color:#0ff;'>Your Website</a> | ";
echo "<a href='/admin' style='color:#0ff;'>Admin Panel</a></p>";

echo "</body></html>";
?>
```

**This single file does everything!**

1. Upload to `public_html/public/setup.php`
2. Visit: `https://yourdomain.com/setup.php`
3. Wait for completion
4. **DELETE setup.php immediately**
5. Login at `/admin` with:
   - Email: `admin@example.com`
   - Password: `password123`

---

## 📞 If Nothing Works

1. **Check PHP Version**: Hostinger control panel → Ensure PHP 8.1+
2. **Check Error Logs**: hPanel → Error Logs
3. **Contact Hostinger Support**: Ask them to enable:
   - PHP execution
   - `symlink` function
   - Proper file permissions

---

**Now you can set up your Laravel app on Hostinger without any helper files showing 404!**
