# 🚀 Laravel Backpack Project Transfer Guide

## Quick Transfer Instructions

### Option 1: Git Clone (Recommended)
1. Clone repository on new laptop:
   ```bash
   git clone https://github.com/Kiranppatil21/laravelbackpack.git
   cd laravelbackpack
   git checkout ci/run-rbac-rerun
   ```

2. Run setup script:
   ```bash
   ./transfer-setup.sh
   ```

3. Start development server:
   ```bash
   php artisan serve
   ```

### Option 2: Manual Transfer
1. Copy project folder (excluding `vendor/` and `node_modules/`)
2. Copy `database/database.sqlite` file
3. Run setup script: `./transfer-setup.sh`

## 🔧 System Requirements
- PHP 8.1+
- Composer
- Node.js & NPM
- Git
- SQLite support

## 🔑 Default Credentials
- **Admin Panel**: http://localhost:8000/admin
- **Username**: admin@example.com
- **Password**: password123

## 📊 Current Data Status
- ✅ 3 Master attendance records
- ✅ 62 Individual attendance entries  
- ✅ 19 Client locations
- ✅ 15 Employee records

## 🎯 Key Features Available
- ✅ Complete bulk attendance management
- ✅ Employee-client assignment system
- ✅ Shift tracking (S1, S2, S3) with OT
- ✅ Calendar-based interface
- ✅ Edit and delete functionality
- ✅ Multi-tenant architecture

## 🐛 Troubleshooting

### Database Issues
```bash
# Reset database
rm database/database.sqlite
touch database/database.sqlite
php artisan migrate --force
php artisan db:seed --force
```

### Permission Issues (Unix/Linux/macOS)
```bash
sudo chmod -R 755 storage/
sudo chmod -R 755 bootstrap/cache/
```

### Cache Issues
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Asset Issues
```bash
npm run build
# or for development
npm run dev
```

## 📁 Project Structure
```
laravelbackpack/
├── app/                    # Laravel application code
├── database/              
│   ├── database.sqlite    # SQLite database file
│   └── migrations/        # Database migrations
├── resources/views/       # Blade templates
├── routes/               # Application routes
├── .env                  # Environment configuration
└── transfer-setup.sh     # Setup script for new laptop
```

## 🔄 Development Commands
```bash
# Start development server
php artisan serve

# Watch for file changes (frontend)
npm run dev

# Build for production
npm run build

# Access database via tinker
php artisan tinker

# View logs
tail -f storage/logs/laravel.log
```

---
**Repository**: https://github.com/Kiranppatil21/laravelbackpack  
**Branch**: ci/run-rbac-rerun  
**Last Updated**: November 2025