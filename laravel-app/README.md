# DigiCloudify OS - Laravel Livewire Edition

This is the Laravel Livewire conversion of the DigiCloudify OS marketing operations platform, originally built with Next.js + React + NestJS.

## 🚀 Quick Start

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js & NPM
- PostgreSQL 14+
- Redis (optional, for caching and queues)

### Installation

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your database in .env file
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=digicloudify_os
DB_USERNAME=postgres
DB_PASSWORD=your_password

# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed

# Build frontend assets
npm run dev

# Start development server
php artisan serve
```

Visit `http://localhost:8000` in your browser.

## 📁 Project Structure

```
laravel-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/       # Traditional controllers
│   │   ├── Livewire/          # Livewire components (main UI logic)
│   │   │   ├── Campaigns/
│   │   │   ├── Tasks/
│   │   │   ├── Leads/
│   │   │   ├── Creatives/
│   │   │   └── Dashboard/
│   │   └── Middleware/
│   ├── Models/                # Eloquent models
│   ├── Services/              # Business logic layer
│   └── Policies/              # Authorization policies
├── database/
│   ├── migrations/            # Database schema
│   ├── factories/             # Model factories for testing
│   └── seeders/               # Database seeders
├── resources/
│   ├── views/
│   │   ├── layouts/           # App layout (header, sidebar, etc.)
│   │   ├── livewire/          # Livewire component views
│   │   ├── components/        # Reusable Blade components
│   │   └── pages/             # Full page views
│   ├── css/
│   │   └── app.css           # Tailwind CSS
│   └── js/
│       └── app.js            # Alpine.js & frontend logic
├── routes/
│   ├── web.php               # Web routes
│   └── api.php               # API routes (if needed)
└── tests/
    ├── Feature/              # Feature tests
    └── Unit/                 # Unit tests
```

## 🎯 Key Features

### ✅ Campaign Management
- Kanban board for campaign workflow
- Campaign creation and editing
- Status tracking (Planning → Running → Completed)
- Performance metrics dashboard

### ✅ Task Management
- Kanban board with drag-and-drop
- Task assignment and status updates
- Comments and attachments
- Deadline tracking

### ✅ Lead Management
- Lead capture and tracking
- Kanban board by status
- Assignment to team members
- Source tracking

### ✅ Creative Production
- Creative request workflow
- Asset uploads and versioning
- Feedback and approval system
- Integration with campaigns

### ✅ Workflow Automation
- Event-driven automation
- Rule-based actions
- Notification triggers
- Audit logging

### ✅ Client Portal
- Client-specific dashboards
- Campaign performance views
- Limited access for clients

### ✅ Reporting & Analytics
- Performance dashboards
- Custom date ranges
- Export capabilities
- Alert system

## 🔧 Technology Stack

- **Framework:** Laravel 11
- **UI:** Livewire 3 + Alpine.js
- **Styling:** Tailwind CSS
- **Database:** PostgreSQL
- **Queue:** Laravel Queue (Database/Redis)
- **Authentication:** Laravel Sanctum
- **Testing:** PHPUnit + Pest

## 📚 Key Livewire Components

### Campaigns Kanban Board
```php
@livewire('campaigns.kanban-board')
```
Interactive Kanban board with drag-and-drop to manage campaign workflow.

### Tasks Kanban Board
```php
@livewire('tasks.kanban-board')
```
Task management with status columns, assignment, and real-time updates.

### Leads Kanban Board
```php
@livewire('leads.kanban-board')
```
Lead pipeline management with status tracking.

### Creative Requests
```php
@livewire('creatives.requests-board')
```
Creative production workflow with approval process.

### Dashboard
```php
@livewire('dashboard.stats')
```
Overview dashboard with key metrics and recent activities.

## 🎨 Tailwind Configuration

The project uses Tailwind CSS with a custom configuration matching the original design system:

```javascript
// tailwind.config.js
export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './app/Http/Livewire/**/*.php',
  ],
  theme: {
    extend: {
      colors: {
        primary: '#3B82F6',
        secondary: '#10B981',
        danger: '#EF4444',
      },
    },
  },
}
```

## 🔐 Authentication

The application uses Laravel's built-in authentication with multi-tenancy support:

- Users belong to Organizations
- Organization context is set via middleware
- Role-based access control (OWNER, ADMIN, ANALYST, OPERATOR, VIEWER)

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --filter=CampaignsTest

# Run with coverage
php artisan test --coverage
```

## 📦 Database Schema

The database schema includes:

- **organizations** - Multi-tenant organization data
- **users** - User accounts with roles
- **clients** - Client/customer records
- **ad_accounts** - Ad platform accounts
- **campaigns** - Marketing campaigns
- **campaign_refs** - Campaign references with workflow status
- **tasks** - Task management
- **creative_requests** - Creative production requests
- **creative_assets** - Asset files and versions
- **leads** - Lead management
- **notifications** - System notifications
- **workflow_rules** - Automation rules
- **workflow_events** - Event tracking
- **automation_logs** - Automation execution logs
- **alerts** - System alerts
- **recommendations** - AI-powered recommendations
- **daily_metrics** - Campaign performance metrics (partitioned)

## 🚀 Deployment

### Production Optimization

```bash
# Optimize configuration
php artisan config:cache

# Optimize routes
php artisan route:cache

# Optimize views
php artisan view:cache

# Build production assets
npm run build
```

### Environment Variables

Key environment variables for production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=pgsql
DB_HOST=your-db-host
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
```

## 📖 Migration from Next.js/React

See [CONVERSION_GUIDE.md](CONVERSION_GUIDE.md) for detailed information about:

- React component → Livewire component patterns
- State management conversion
- API route → Livewire action patterns
- Testing strategies
- Performance optimization

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Write tests
5. Submit a pull request

## 📄 License

MIT License

## 🆘 Support

For issues and questions:
- Check the [CONVERSION_GUIDE.md](CONVERSION_GUIDE.md)
- Review Laravel documentation: https://laravel.com/docs
- Review Livewire documentation: https://livewire.laravel.com/docs

## 🎓 Learning Resources

- **Laravel:** https://laracasts.com
- **Livewire:** https://livewire.laravel.com
- **Tailwind CSS:** https://tailwindcss.com
- **Alpine.js:** https://alpinejs.dev
