# 🚀 Complete Setup Guide - Marketing Agency Platform

## Quick Start Guide

This guide will help you set up the complete marketing agency management platform from scratch.

---

## 📋 Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js & NPM
- MySQL/PostgreSQL database
- Redis (for caching and queues)
- Meilisearch (optional, for full-text search)

---

## 🔧 Installation Steps

### 1. Install PHP Dependencies

```bash
cd laravel-app
composer install
```

This will install all required packages including:
- Laravel Framework ^11.0
- Laravel Sanctum ^4.0 (API authentication)
- Livewire ^3.0 (real-time UI)
- Laravel Scout ^10.0 (search)
- Laravel Horizon ^5.21 (queue monitoring)
- Spatie Laravel Permission ^6.0 (roles & permissions)
- Spatie Laravel Activity Log ^4.8 (audit trail)
- Maatwebsite Excel ^3.1 (data export)
- DomPDF ^2.0 (PDF generation)
- Predis ^2.2 (Redis client)

### 2. Install Frontend Dependencies

```bash
npm install
```

### 3. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure Database

Edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=agency_platform
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 5. Configure Cache & Queue

```env
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 6. Configure Broadcasting (Optional)

```env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=your-cluster
```

### 7. Configure Search (Optional)

```env
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://localhost:7700
MEILISEARCH_KEY=your-master-key
```

### 8. Run Migrations

```bash
php artisan migrate
```

This will create all tables:
- ✅ organizations
- ✅ users  
- ✅ employees
- ✅ projects
- ✅ project_assignments
- ✅ time_entries
- ✅ workload_entries
- ✅ invoices
- ✅ invoice_items
- ✅ payments
- ✅ leave_requests
- ✅ clients
- ✅ campaigns
- ✅ leads
- ✅ tasks
- ✅ creative_requests
- ✅ webhooks
- ✅ webhook_deliveries
- ✅ notifications
- ✅ alerts

### 9. Publish Vendor Assets

```bash
# Publish Horizon assets
php artisan vendor:publish --tag=horizon-assets

# Publish Scout config
php artisan vendor:publish --provider="Laravel\Scout\ScoutServiceProvider"

# Publish Permission config
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Publish Activity Log
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider"
```

### 10. Set Up Storage

```bash
php artisan storage:link
```

### 11. Index Search Data (if using Scout)

```bash
php artisan scout:import "App\Models\Campaign"
```

### 12. Compile Assets

```bash
# Development
npm run dev

# Production
npm run build
```

### 13. Start Services

```bash
# Terminal 1: Application server
php artisan serve

# Terminal 2: Queue worker (or Horizon)
php artisan queue:work --queue=metrics,notifications,automation,webhooks

# OR use Horizon
php artisan horizon

# Terminal 3: Asset watcher (development only)
npm run dev
```

---

## 🗄️ Database Seeding

### Create Seeders

```bash
php artisan make:seeder OrganizationSeeder
php artisan make:seeder EmployeeSeeder
php artisan make:seeder ProjectSeeder
php artisan make:seeder ClientSeeder
```

### Sample Seeder Example

```php
// database/seeders/OrganizationSeeder.php
use App\Models\Organization;
use App\Models\User;

public function run(): void
{
    $org = Organization::create([
        'name' => 'DigiCloudify Agency',
        'slug' => 'digicloudify',
    ]);

    User::create([
        'organization_id' => $org->id,
        'name' => 'Admin User',
        'email' => 'admin@digicloudify.com',
        'password' => bcrypt('password'),
    ]);
}
```

### Run Seeders

```bash
php artisan db:seed
```

---

## 🔐 Roles & Permissions Setup

### Create Roles

```bash
php artisan tinker
```

```php
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Create roles
$superAdmin = Role::create(['name' => 'super-admin']);
$admin = Role::create(['name' => 'admin']);
$manager = Role::create(['name' => 'manager']);
$employee = Role::create(['name' => 'employee']);
$client = Role::create(['name' => 'client']);

// Create permissions
$permissions = [
    'view-campaigns',
    'create-campaigns',
    'edit-campaigns',
    'delete-campaigns',
    'view-analytics',
    'manage-employees',
    'manage-projects',
    'manage-invoices',
    'approve-time-entries',
    'manage-workflow',
];

foreach ($permissions as $permission) {
    Permission::create(['name' => $permission]);
}

// Assign permissions to roles
$admin->givePermissionTo(Permission::all());
$manager->givePermissionTo([
    'view-campaigns',
    'create-campaigns',
    'edit-campaigns',
    'view-analytics',
    'manage-projects',
    'approve-time-entries',
]);
```

---

## 📊 Testing the Platform

### 1. Test API Endpoints

```bash
# Get Sanctum token first
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@digicloudify.com","password":"password"}'

# Use the token in subsequent requests
TOKEN="your-token-here"

# Test Analytics Dashboard
curl http://localhost:8000/api/v1/analytics/dashboard?period=30days \
  -H "Authorization: Bearer $TOKEN"

# Test Workload Analysis
curl http://localhost:8000/api/v1/workload/analysis?period=current_week \
  -H "Authorization: Bearer $TOKEN"

# Test Agency Dashboard
curl http://localhost:8000/api/v1/agency/dashboard \
  -H "Authorization: Bearer $TOKEN"

# Test Campaign Export
curl -X POST http://localhost:8000/api/v1/exports/campaigns \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"format":"xlsx","status":"running"}'
```

### 2. Access Horizon Dashboard

Navigate to: `http://localhost:8000/horizon`

Monitor:
- Queue throughput
- Failed jobs
- Job metrics
- Recent jobs

### 3. Test Search (if Scout is configured)

```php
use App\Models\Campaign;

// Search campaigns
Campaign::search('marketing')->where('organization_id', $orgId)->get();
```

---

## 🔄 Scheduled Tasks

### Configure Scheduler

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    // Sync campaign metrics hourly
    $schedule->command('campaigns:sync-metrics')
        ->hourly()
        ->withoutOverlapping();
    
    // Check campaign performance every 4 hours
    $schedule->command('campaigns:check-performance')
        ->everyFourHours()
        ->withoutOverlapping();
    
    // Cleanup old data weekly
    $schedule->command('cleanup:old-data')
        ->weekly()
        ->sundays()
        ->at('02:00');
    
    // Clear expired cache daily
    $schedule->command('cache:prune-stale-tags')
        ->daily();
}
```

### Run Scheduler (Production)

Add to crontab:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🐛 Troubleshooting

### Common Issues

#### 1. Queue Jobs Not Processing

```bash
# Check if queue worker is running
ps aux | grep queue:work

# Restart queue
php artisan queue:restart

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

#### 2. Cache Issues

```bash
# Clear all cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild cache
php artisan config:cache
php artisan route:cache
```

#### 3. Search Not Working

```bash
# Flush and reimport
php artisan scout:flush "App\Models\Campaign"
php artisan scout:import "App\Models\Campaign"
```

#### 4. Permission Issues

```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## 🚀 Production Deployment

### 1. Optimize for Production

```bash
# Install production dependencies
composer install --optimize-autoloader --no-dev

# Build assets
npm run build

# Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimize
php artisan optimize
```

### 2. Configure Supervisor (for Queue Workers)

Create `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path-to-project/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/path-to-project/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

### 3. Use Horizon (Recommended)

```bash
# Install as a service
php artisan horizon:install

# Configure supervisor for Horizon
[program:horizon]
process_name=%(program_name)s
command=php /path-to-project/artisan horizon
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/path-to-project/storage/logs/horizon.log
stopwaitsecs=3600
```

---

## 📝 Environment Variables Reference

```env
# Application
APP_NAME="Marketing Agency Platform"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=agency_platform
DB_USERNAME=root
DB_PASSWORD=

# Cache & Queue
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Broadcasting
BROADCAST_DRIVER=pusher

# Search
SCOUT_DRIVER=meilisearch
SCOUT_QUEUE=true
MEILISEARCH_HOST=http://localhost:7700

# Horizon
HORIZON_DOMAIN=your-domain.com
HORIZON_PATH=horizon
```

---

## ✅ Post-Installation Checklist

- [ ] Database migrations run successfully
- [ ] Vendor assets published
- [ ] Storage linked
- [ ] Roles and permissions created
- [ ] Sample data seeded
- [ ] Queue worker running (or Horizon)
- [ ] Scheduler configured
- [ ] Search indexed (if using Scout)
- [ ] API endpoints tested
- [ ] Horizon dashboard accessible
- [ ] Cache configured and working
- [ ] Broadcasting configured (if needed)
- [ ] Webhooks tested
- [ ] Email sending configured
- [ ] Backups configured
- [ ] Monitoring set up
- [ ] SSL certificate installed (production)

---

## 📚 Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Livewire Documentation](https://livewire.laravel.com)
- [Laravel Scout Documentation](https://laravel.com/docs/scout)
- [Laravel Horizon Documentation](https://laravel.com/docs/horizon)
- [Spatie Permission Documentation](https://spatie.be/docs/laravel-permission)

---

## 🎉 You're Ready!

Your complete marketing agency management platform is now set up and ready to use!

**Key Features Available:**
✅ Employee Management
✅ Workload Analysis  
✅ Time Tracking
✅ Project Management
✅ Invoice & Billing
✅ Campaign Management
✅ Lead Tracking
✅ Client Portal
✅ Workflow Automation
✅ Real-time Analytics
✅ Data Export (Excel/CSV/PDF)
✅ Webhook Integrations
✅ Advanced Search
✅ Role-based Permissions

**Happy managing! 🚀**
