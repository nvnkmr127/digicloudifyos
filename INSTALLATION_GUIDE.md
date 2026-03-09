# 📦 Installation Guide - Laravel Livewire Edition

## Prerequisites

Before you begin, ensure you have the following installed:

- **PHP 8.2 or higher** - [Download PHP](https://www.php.net/downloads)
- **Composer** - [Install Composer](https://getcomposer.org/download/)
- **Node.js 20.x or higher** - [Download Node.js](https://nodejs.org/)
- **PostgreSQL 14+** - [Download PostgreSQL](https://www.postgresql.org/download/)
- **Git** (optional) - [Download Git](https://git-scm.com/downloads)

### Verify Prerequisites

```bash
php -v        # Should show PHP 8.2+
composer -V   # Should show Composer 2.x
node -v       # Should show v20.x+
npm -v        # Should show npm 10.x+
psql --version # Should show PostgreSQL 14+
```

## Installation Methods

### Method 1: Automated Setup (Recommended)

We've created an automated setup script that handles most of the installation:

```bash
cd laravel-app
./setup.sh
```

The script will:
1. Check prerequisites
2. Install PHP dependencies via Composer
3. Install Node dependencies via npm
4. Create .env file
5. Generate application key
6. Install Laravel Breeze for authentication

After the script completes, follow the "Post-Installation" steps below.

### Method 2: Manual Installation

If you prefer manual installation or the script fails, follow these steps:

#### Step 1: Install PHP Dependencies

```bash
cd laravel-app
composer install
```

**Common Issues:**
- If you see memory errors, run: `php -d memory_limit=-1 $(which composer) install`
- If extensions are missing, install: `php-pgsql`, `php-mbstring`, `php-xml`, `php-curl`

#### Step 2: Install Node Dependencies

```bash
npm install
```

**Common Issues:**
- If you see permission errors on Mac/Linux, don't use sudo. Fix npm permissions instead.
- Clear npm cache if needed: `npm cache clean --force`

#### Step 3: Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

#### Step 4: Configure Database

Edit `.env` file:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=digicloudify_os
DB_USERNAME=postgres
DB_PASSWORD=your_password_here
```

#### Step 5: Create Database

```bash
# Connect to PostgreSQL
psql -U postgres

# Create database
CREATE DATABASE digicloudify_os;

# Exit psql
\q
```

#### Step 6: Run Migrations

```bash
php artisan migrate
```

You should see:
```
Migration table created successfully.
Migrating: 2024_01_01_000001_create_organizations_table
Migrated:  2024_01_01_000001_create_organizations_table (XX.XXms)
...
(20 migrations total)
```

#### Step 7: Install Laravel Breeze (Authentication)

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
php artisan migrate
```

#### Step 8: Build Frontend Assets

**For Development:**
```bash
npm run dev
```
Keep this running in a separate terminal.

**For Production:**
```bash
npm run build
```

#### Step 9: Start Development Server

Open a new terminal:
```bash
php artisan serve
```

You should see:
```
Starting Laravel development server: http://127.0.0.1:8000
```

## Post-Installation

### 1. Create Your First User

Visit http://localhost:8000/register and create an account.

**Important:** The first user should create an organization. You'll need to manually insert an organization record first:

```bash
php artisan tinker
```

Then in the tinker console:
```php
$org = new App\Models\Organization();
$org->name = 'My Organization';
$org->slug = 'my-org';
$org->timezone = 'UTC';
$org->save();

// Get the organization ID
echo $org->id;
```

Now when you register, manually update the user's organization_id in the database.

### 2. Seed Demo Data (Optional)

Create a seeder to populate demo data:

```bash
php artisan make:seeder DemoDataSeeder
```

### 3. Verify Installation

Visit these URLs to verify everything works:

- http://localhost:8000 - Should redirect to dashboard
- http://localhost:8000/dashboard - Dashboard with stats
- http://localhost:8000/campaigns - Campaigns Kanban board
- http://localhost:8000/tasks - Tasks Kanban board
- http://localhost:8000/leads - Leads Kanban board

## Troubleshooting

### Issue: "Class 'Livewire\Component' not found"

**Solution:**
```bash
composer require livewire/livewire
php artisan livewire:publish --config
php artisan livewire:publish --assets
```

### Issue: "Vite manifest not found"

**Solution:**
```bash
npm run build
# OR keep development server running:
npm run dev
```

### Issue: "SQLSTATE[08006] Could not connect to server"

**Solution:**
- Check PostgreSQL is running: `brew services list` (Mac) or `sudo systemctl status postgresql` (Linux)
- Verify database credentials in `.env`
- Ensure database exists: `psql -U postgres -l`

### Issue: "419 Page Expired" on Login

**Solution:**
- Clear browser cache and cookies
- Run: `php artisan config:clear`
- Ensure APP_KEY is set in `.env`

### Issue: Tailwind CSS not working

**Solution:**
```bash
npm run build
php artisan view:clear
php artisan config:clear
```

### Issue: "Class 'App\Models\Campaign' not found"

**Solution:**
```bash
composer dump-autoload
php artisan optimize:clear
```

### Issue: Drag-and-drop not working

**Solution:**
- Ensure Alpine.js is loaded: Check browser console for errors
- Rebuild assets: `npm run build`
- Hard refresh browser: Ctrl+Shift+R (or Cmd+Shift+R on Mac)

## Development Workflow

### Running the Application

You need **two terminal windows**:

**Terminal 1 - Backend:**
```bash
php artisan serve
```

**Terminal 2 - Frontend:**
```bash
npm run dev
```

### Making Changes

**After changing Blade files:**
- Just refresh browser (Vite hot reload)

**After changing Livewire components:**
- Refresh browser
- If issues, run: `php artisan view:clear`

**After changing Tailwind classes:**
- Vite will auto-rebuild
- Just refresh browser

**After changing routes:**
- Run: `php artisan route:clear`

**After adding new Composer packages:**
- Run: `composer dump-autoload`

## Production Deployment

### 1. Optimize Application

```bash
composer install --optimize-autoloader --no-dev
npm run build

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 2. Set Environment

Edit `.env`:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
```

### 3. Set Permissions

```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 4. Configure Web Server

**Nginx Example:**
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/laravel-app/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 5. Setup Queue Worker (Optional)

```bash
php artisan queue:work --daemon
```

Or use Supervisor to keep it running.

### 6. Setup Scheduler (Optional)

Add to crontab:
```bash
* * * * * cd /path/to/laravel-app && php artisan schedule:run >> /dev/null 2>&1
```

## Environment Variables Reference

### Required Variables

```env
APP_NAME="DigiCloudify OS"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=digicloudify_os
DB_USERNAME=postgres
DB_PASSWORD=
```

### Optional Variables

```env
# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Queue
QUEUE_CONNECTION=database

# Cache
CACHE_DRIVER=database

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null

# Redis (optional)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## Next Steps

After installation:

1. ✅ Read [README.md](README.md) for feature overview
2. ✅ Review [CONVERSION_GUIDE.md](CONVERSION_GUIDE.md) for development patterns
3. ✅ Check [FINAL_STATUS.md](FINAL_STATUS.md) for implementation details
4. ✅ Start customizing for your needs!

## Getting Help

If you encounter issues:

1. Check this guide's Troubleshooting section
2. Review Laravel logs: `storage/logs/laravel.log`
3. Check browser console for JavaScript errors
4. Review [FINAL_STATUS.md](FINAL_STATUS.md) for known limitations

## Useful Commands

```bash
# Clear all caches
php artisan optimize:clear

# Recreate database (WARNING: destroys data)
php artisan migrate:fresh

# Run migrations with seed data
php artisan migrate:fresh --seed

# Check routes
php artisan route:list

# Check Livewire components
php artisan livewire:list

# Interactive shell
php artisan tinker

# View logs
tail -f storage/logs/laravel.log
```

---

**Installation complete! 🎉**

Your Laravel Livewire application is now ready to use. Visit http://localhost:8000 to get started!
