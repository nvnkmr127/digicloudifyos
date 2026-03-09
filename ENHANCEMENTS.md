# 🚀 Laravel Application Enhancements

## Overview
This document outlines the advanced features and enhancements added to the DigiCloudify OS Laravel application to make it more productive, scalable, and enterprise-ready.

## 📋 Table of Contents
1. [Service Layer Architecture](#service-layer-architecture)
2. [Repository Pattern](#repository-pattern)
3. [Event-Driven Architecture](#event-driven-architecture)
4. [Background Job Processing](#background-job-processing)
5. [API Layer with Versioning](#api-layer-with-versioning)
6. [Advanced Authorization](#advanced-authorization)
7. [Notification System](#notification-system)
8. [Console Commands](#console-commands)
9. [Caching Strategy](#caching-strategy)
10. [Real-Time Features](#real-time-features)

---

## 🏗️ Service Layer Architecture

### Location
- `app/Services/CampaignService.php`

### Features
- **Business Logic Separation**: All campaign-related business logic is centralized in the service layer
- **Transaction Management**: Automatic database transaction handling with rollback on errors
- **Event Dispatching**: Fires events for campaign lifecycle changes
- **Cache Management**: Automatic cache invalidation on data changes
- **Error Logging**: Comprehensive error logging for debugging

### Usage Example
```php
use App\Services\CampaignService;

class CampaignController {
    public function __construct(private CampaignService $campaignService) {}
    
    public function store(StoreCampaignRequest $request) {
        $campaign = $this->campaignService->create($request->validated());
        return response()->json(['data' => $campaign]);
    }
}
```

---

## 🗄️ Repository Pattern

### Location
- `app/Repositories/CampaignRepository.php`

### Benefits
- **Data Access Abstraction**: Clean separation between data access and business logic
- **Query Reusability**: Common queries are defined once and reused throughout the app
- **Testability**: Easy to mock repositories for unit testing
- **Flexibility**: Easy to switch data sources if needed

### Available Methods
- `getByOrganization($organizationId, $filters)`
- `find($id)`
- `create($data)`
- `update($campaign, $data)`
- `delete($campaign)`
- `getActive($organizationId)`
- `getRunning($organizationId)`

---

## 🎯 Event-Driven Architecture

### Events Created
1. **CampaignCreated** - Fired when a new campaign is created
2. **CampaignStatusChanged** - Fired when campaign status changes
3. **CampaignDeleted** - Fired when a campaign is deleted

### Location
- `app/Events/`

### Features
- **Real-Time Broadcasting**: Events are broadcast to connected clients via WebSockets
- **Multi-Channel Support**: Events are sent to organization-specific and global channels
- **Asynchronous Processing**: Events can trigger background jobs

### Broadcasting Channels
```javascript
// Listen to organization-specific campaigns
Echo.join('organization.' + organizationId)
    .listen('.campaign.created', (e) => {
        console.log('New campaign:', e);
    });
```

---

## ⚙️ Background Job Processing

### Jobs Created

#### 1. SyncCampaignMetrics
- **Purpose**: Sync campaign performance metrics from external platforms (Facebook, Google, TikTok)
- **Queue**: `metrics`
- **Retry Policy**: 3 attempts with exponential backoff (30s, 60s, 120s)
- **Timeout**: 120 seconds

#### 2. SendCampaignNotification
- **Purpose**: Send notifications to team members about campaign events
- **Queue**: `notifications`
- **Retry Policy**: 3 attempts
- **Timeout**: 60 seconds

#### 3. ProcessWorkflowAutomation
- **Purpose**: Execute workflow automation rules
- **Queue**: `automation`
- **Features**:
  - Condition evaluation
  - Multiple action types (notifications, tasks, status updates, webhooks)
  - Automation logging
  - Error handling

### Usage
```bash
# Start queue worker
php artisan queue:work --queue=metrics,notifications,automation

# Or use Horizon for advanced queue management
php artisan horizon
```

---

## 🌐 API Layer with Versioning

### Location
- `app/Http/Controllers/Api/V1/CampaignController.php`
- `routes/api.php`

### Endpoints

#### Campaign API (v1)
```
GET    /api/v1/campaigns              - List all campaigns
POST   /api/v1/campaigns              - Create new campaign
GET    /api/v1/campaigns/{id}         - Get campaign details
PUT    /api/v1/campaigns/{id}         - Update campaign
DELETE /api/v1/campaigns/{id}         - Delete campaign
GET    /api/v1/campaigns/{id}/metrics - Get campaign metrics
```

### Authentication
All API endpoints require Sanctum token authentication:

```bash
# Generate token
curl -X POST http://your-app.test/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'

# Use token
curl http://your-app.test/api/v1/campaigns \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### API Resources
- `CampaignResource` - Transforms campaign data with related models
- `TaskResource` - Formats task data
- `LeadResource` - Formats lead data
- `CreativeRequestResource` - Formats creative request data
- `AlertResource` - Formats alert data

---

## 🔐 Advanced Authorization

### Policies Created
- `CampaignPolicy` - Campaign access control
- Organization-level isolation
- Role-based permissions
- Action-specific authorization

### Location
- `app/Policies/CampaignPolicy.php`
- `app/Providers/AuthServiceProvider.php`

### Features
- **Multi-Tenant Security**: Ensures users can only access their organization's data
- **Fine-Grained Permissions**: Separate permissions for view, create, edit, delete
- **Business Logic**: Prevents deletion of running campaigns
- **Super Admin Override**: Super admins bypass all checks

### Usage in Code
```php
// In controllers
$this->authorize('update', $campaign);

// In blade templates
@can('update', $campaign)
    <button>Edit Campaign</button>
@endcan
```

---

## 📬 Notification System

### Notification Types
1. **Campaign Created**
2. **Campaign Status Changed**
3. **Budget Alert** (90% budget usage)
4. **Performance Alert** (Low CTR, Low conversion rate)

### Channels
- **Mail**: Email notifications
- **Database**: In-app notifications
- **Broadcast**: Real-time browser notifications

### Location
- `app/Notifications/CampaignNotification.php`

### Usage
```php
use App\Jobs\SendCampaignNotification;

SendCampaignNotification::dispatch($campaign, 'created');
```

---

## 🖥️ Console Commands

### 1. Sync Campaign Metrics
```bash
# Sync all running campaigns
php artisan campaigns:sync-metrics

# Sync specific campaign
php artisan campaigns:sync-metrics --campaign=CAMPAIGN_ID

# Sync for specific date
php artisan campaigns:sync-metrics --date=2024-03-01
```

### 2. Check Campaign Performance
```bash
# Check performance and generate alerts
php artisan campaigns:check-performance
```

### 3. Cleanup Old Data
```bash
# Delete data older than 90 days (default)
php artisan cleanup:old-data

# Custom retention period
php artisan cleanup:old-data --days=30

# Dry run (preview without deleting)
php artisan cleanup:old-data --dry-run
```

### Scheduling
Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('campaigns:sync-metrics')
        ->hourly()
        ->withoutOverlapping();
    
    $schedule->command('campaigns:check-performance')
        ->everyFourHours()
        ->withoutOverlapping();
    
    $schedule->command('cleanup:old-data')
        ->weekly()
        ->sundays()
        ->at('02:00');
}
```

---

## 💾 Caching Strategy

### Implementation
- **Query Caching**: Campaign lists are cached for 5 minutes
- **Cache Tags**: Support for Redis cache tags for easy invalidation
- **Automatic Invalidation**: Cache is cleared when data changes
- **Metrics Caching**: Campaign metrics cached for 5 minutes

### Cache Keys Pattern
```
campaigns.org.{organization_id}.{filters_hash}
campaign.metrics.{campaign_id}
```

### Manual Cache Control
```php
// Clear organization campaigns cache
Cache::tags(['campaigns', "org.{$organizationId}"])->flush();
```

---

## 🔴 Real-Time Features

### Broadcasting Setup
The application includes broadcasting support for real-time updates:

1. **Event Broadcasting**: Campaign events are broadcast in real-time
2. **Presence Channels**: Organization-specific presence channels
3. **Public Channels**: Global campaign updates

### Frontend Integration
```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    forceTLS: true
});

// Listen to campaign updates
Echo.channel('campaigns')
    .listen('.campaign.created', (e) => {
        // Handle new campaign
    })
    .listen('.campaign.status.changed', (e) => {
        // Handle status change
    });
```

---

## 📦 Additional Packages

### Required Packages (Add to composer.json)
```json
{
    "require": {
        "predis/predis": "^2.2",
        "spatie/laravel-permission": "^6.0",
        "spatie/laravel-activitylog": "^4.8",
        "laravel/horizon": "^5.21"
    }
}
```

### Installation
```bash
composer install
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider"
php artisan vendor:publish --tag=horizon-assets
php artisan migrate
```

---

## 🚀 Getting Started

### 1. Install Dependencies
```bash
composer install
npm install
```

### 2. Configure Environment
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Set Up Database
```bash
php artisan migrate
php artisan db:seed
```

### 4. Configure Queue & Broadcasting
```env
QUEUE_CONNECTION=redis
BROADCAST_DRIVER=pusher

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=your-cluster
```

### 5. Start Services
```bash
# Start queue worker
php artisan queue:work

# Or use Horizon
php artisan horizon

# Start development server
php artisan serve

# Compile assets
npm run dev
```

---

## 🧪 Testing

### Run Tests
```bash
php artisan test
```

### Code Quality
```bash
# Run PHP CS Fixer
./vendor/bin/pint

# Run Static Analysis
./vendor/bin/phpstan analyse
```

---

## 📊 Performance Monitoring

### Horizon Dashboard
Access at: `http://your-app.test/horizon`

Features:
- Real-time job monitoring
- Failed job management
- Queue metrics and throughput
- Job retry mechanisms

### Application Insights
- Query logging enabled in development
- Slow query detection
- Error tracking via Sentry (optional)

---

## 🔧 Configuration

### Queue Configuration
Edit `config/queue.php` to customize queue behavior:
- Connection settings
- Queue priorities
- Job retry settings
- Timeout values

### Cache Configuration
Edit `config/cache.php`:
- Default cache driver
- Cache prefix
- TTL settings

---

## 📝 Best Practices

1. **Always use services for business logic** - Keep controllers thin
2. **Use form requests for validation** - Centralize validation rules
3. **Leverage events for side effects** - Keep code decoupled
4. **Queue long-running tasks** - Don't block user requests
5. **Cache expensive queries** - Improve response times
6. **Use API resources** - Consistent API responses
7. **Implement proper authorization** - Security first
8. **Log important events** - Aid debugging and monitoring
9. **Write tests** - Ensure code reliability
10. **Monitor queues** - Use Horizon for insights

---

## 🆘 Troubleshooting

### Queue Jobs Not Processing
```bash
# Check queue worker is running
php artisan queue:work --verbose

# Check failed jobs
php artisan queue:failed
```

### Cache Not Invalidating
```bash
# Clear all cache
php artisan cache:clear

# Clear specific tags (Redis only)
php artisan cache:clear --tags=campaigns
```

### Broadcasting Not Working
```bash
# Check Pusher credentials
php artisan config:clear

# Test broadcasting
php artisan tinker
>>> broadcast(new App\Events\CampaignCreated($campaign));
```

---

## 📈 Future Enhancements

- [ ] Laravel Scout integration for full-text search
- [ ] Advanced analytics dashboard
- [ ] Export functionality (CSV, Excel, PDF)
- [ ] Multi-language support
- [ ] Advanced workflow builder UI
- [ ] Integration with more ad platforms
- [ ] Mobile app API endpoints
- [ ] GraphQL API support
- [ ] Elasticsearch integration
- [ ] Advanced reporting engine

---

## 📞 Support

For issues or questions:
- Check the documentation
- Review the code comments
- Contact the development team

---

**Version**: 2.0.0  
**Last Updated**: March 2026  
**Maintained By**: DigiCloudify Team
