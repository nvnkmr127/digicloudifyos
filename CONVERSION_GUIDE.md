# Next.js/React to Laravel Livewire Conversion Guide

## Project Overview

This document outlines the conversion of the DigiCloudify OS from a **Next.js 15 + React 18** frontend with **NestJS** backend to a **Laravel 11 + Livewire 3** monolithic application.

### Current Stack
- **Frontend:** Next.js 15, React 18, TypeScript, Tailwind CSS
- **Backend:** NestJS (Node.js), PostgreSQL
- **Architecture:** Separate frontend/backend with REST APIs

### Target Stack
- **Framework:** Laravel 11 with Livewire 3
- **Database:** PostgreSQL (same schema)
- **Styling:** Tailwind CSS (preserved)
- **Architecture:** Monolithic Laravel application with server-side rendering

## Project Structure

```
laravel-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/      # Traditional controllers for APIs
│   │   ├── Livewire/         # Livewire components
│   │   └── Middleware/       # Auth, organization context
│   ├── Models/               # Eloquent models
│   ├── Services/             # Business logic services
│   └── Providers/
├── database/
│   ├── migrations/           # Database migrations
│   ├── factories/            # Model factories
│   └── seeders/              # Database seeders
├── resources/
│   ├── views/
│   │   ├── layouts/          # Main app layout
│   │   ├── livewire/         # Livewire component views
│   │   └── components/       # Blade components
│   ├── css/
│   └── js/
├── routes/
│   ├── web.php               # Web routes
│   └── api.php               # API routes (if needed)
└── tests/
```

## Key Conversion Patterns

### 1. React Components → Livewire Components

#### React Component Example (Original)
```typescript
// frontend/components/campaigns-kanban-board.tsx
'use client';

import { useState, useEffect } from 'react';

export function CampaignsKanbanBoard() {
  const [campaigns, setCampaigns] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch('/api/campaigns')
      .then(res => res.json())
      .then(data => {
        setCampaigns(data);
        setLoading(false);
      });
  }, []);

  const handleDrop = async (event, columnKey) => {
    const campaignId = event.dataTransfer.getData('campaignId');
    await fetch(`/api/campaigns/${campaignId}/status`, {
      method: 'PUT',
      body: JSON.stringify({ status: columnKey })
    });
  };

  return (
    <div className="flex gap-4">
      {columns.map(column => (
        <div key={column.key} onDrop={(e) => handleDrop(e, column.key)}>
          {campaigns.filter(c => c.status === column.key).map(campaign => (
            <CampaignCard campaign={campaign} />
          ))}
        </div>
      ))}
    </div>
  );
}
```

#### Livewire Component (Converted)
```php
// app/Http/Livewire/CampaignsKanbanBoard.php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Campaign;
use App\Services\CampaignService;

class CampaignsKanbanBoard extends Component
{
    public $campaigns = [];
    public $columns = [];

    protected $listeners = ['campaignUpdated' => 'refreshCampaigns'];

    public function mount()
    {
        $this->columns = [
            ['key' => 'planning', 'title' => 'Planning'],
            ['key' => 'creative_requested', 'title' => 'Creative Requested'],
            ['key' => 'ready', 'title' => 'Ready'],
            ['key' => 'running', 'title' => 'Running'],
            ['key' => 'optimizing', 'title' => 'Optimizing'],
            ['key' => 'completed', 'title' => 'Completed'],
        ];
        
        $this->refreshCampaigns();
    }

    public function refreshCampaigns()
    {
        $this->campaigns = Campaign::where('organization_id', auth()->user()->organization_id)
            ->with(['client', 'adAccount'])
            ->get()
            ->groupBy('status')
            ->toArray();
    }

    public function updateCampaignStatus($campaignId, $newStatus)
    {
        $campaign = Campaign::findOrFail($campaignId);
        $campaign->update(['status' => $newStatus]);
        
        $this->refreshCampaigns();
        $this->dispatch('campaignUpdated');
    }

    public function render()
    {
        return view('livewire.campaigns-kanban-board');
    }
}
```

```blade
{{-- resources/views/livewire/campaigns-kanban-board.blade.php --}}
<div class="flex gap-4 overflow-x-auto">
    @foreach($columns as $column)
        <div class="flex-shrink-0 w-80">
            <div class="bg-gray-100 rounded-lg p-4">
                <h3 class="font-semibold mb-4">{{ $column['title'] }}</h3>
                
                <div class="space-y-3" 
                     x-data="kanbanColumn('{{ $column['key'] }}')"
                     @drop.prevent="handleDrop($event, '{{ $column['key'] }}')"
                     @dragover.prevent>
                    
                    @foreach($campaigns[$column['key']] ?? [] as $campaign)
                        <div class="bg-white p-4 rounded shadow cursor-move"
                             draggable="true"
                             @dragstart="$event.dataTransfer.setData('campaignId', '{{ $campaign['id'] }}')">
                            
                            <h4 class="font-medium">{{ $campaign['name'] }}</h4>
                            <p class="text-sm text-gray-600">{{ $campaign['client']['name'] }}</p>
                            
                            @if($campaign['daily_budget'])
                                <p class="text-xs text-gray-500 mt-2">
                                    Budget: ${{ number_format($campaign['daily_budget'], 2) }}/day
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>

<script>
function kanbanColumn(columnKey) {
    return {
        handleDrop(event, newStatus) {
            const campaignId = event.dataTransfer.getData('campaignId');
            @this.updateCampaignStatus(campaignId, newStatus);
        }
    }
}
</script>
```

### 2. Next.js Pages → Laravel Routes + Livewire

#### Next.js Page (Original)
```typescript
// frontend/app/(workspace)/campaigns/page.tsx
export default function CampaignsPage() {
  return (
    <div>
      <PageHeader title="Campaigns" />
      <CampaignsKanbanBoard />
    </div>
  );
}
```

#### Laravel Route + Livewire (Converted)
```php
// routes/web.php
Route::middleware(['auth', 'organization'])->group(function () {
    Route::get('/campaigns', function () {
        return view('pages.campaigns');
    })->name('campaigns.index');
    
    Route::get('/campaigns/{campaign}', function (Campaign $campaign) {
        return view('pages.campaigns.show', ['campaign' => $campaign]);
    })->name('campaigns.show');
});
```

```blade
{{-- resources/views/pages/campaigns.blade.php --}}
<x-app-layout>
    <x-page-header title="Campaigns">
        <x-slot:actions>
            <button wire:click="$dispatch('openCampaignModal')" 
                    class="btn btn-primary">
                Create Campaign
            </button>
        </x-slot:actions>
    </x-page-header>

    <div class="py-6">
        @livewire('campaigns-kanban-board')
    </div>
</x-app-layout>
```

### 3. API Routes → Livewire Actions

#### Next.js API Route (Original)
```typescript
// frontend/app/api/campaigns/[id]/status/route.ts
export async function PUT(
  request: Request,
  { params }: { params: { id: string } }
) {
  const { status } = await request.json();
  
  const response = await fetch(`${BACKEND_URL}/campaigns/${params.id}/status`, {
    method: 'PUT',
    body: JSON.stringify({ status })
  });
  
  return Response.json(await response.json());
}
```

#### Livewire Method (Converted)
The API route is no longer needed - replaced by Livewire method shown above:
```php
public function updateCampaignStatus($campaignId, $newStatus)
{
    $campaign = Campaign::findOrFail($campaignId);
    
    $this->authorize('update', $campaign);
    
    $campaign->update(['status' => $newStatus]);
    
    event(new CampaignStatusChanged($campaign));
    
    $this->refreshCampaigns();
    $this->dispatch('campaignUpdated');
    
    session()->flash('message', 'Campaign status updated successfully.');
}
```

### 4. State Management → Livewire Properties

#### React State (Original)
```typescript
const [filters, setFilters] = useState({
  status: 'all',
  client: null,
  dateRange: null
});

const filteredCampaigns = campaigns.filter(campaign => {
  if (filters.status !== 'all' && campaign.status !== filters.status) {
    return false;
  }
  if (filters.client && campaign.clientId !== filters.client) {
    return false;
  }
  return true;
});
```

#### Livewire Properties (Converted)
```php
class CampaignsKanbanBoard extends Component
{
    public $statusFilter = 'all';
    public $clientFilter = null;
    public $dateRange = null;

    public function updatedStatusFilter()
    {
        $this->refreshCampaigns();
    }

    public function updatedClientFilter()
    {
        $this->refreshCampaigns();
    }

    public function refreshCampaigns()
    {
        $query = Campaign::where('organization_id', auth()->user()->organization_id);
        
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }
        
        if ($this->clientFilter) {
            $query->where('client_id', $this->clientFilter);
        }
        
        $this->campaigns = $query->with(['client', 'adAccount'])->get()->groupBy('status')->toArray();
    }

    public function render()
    {
        return view('livewire.campaigns-kanban-board');
    }
}
```

```blade
<div class="mb-6 flex gap-4">
    <select wire:model.live="statusFilter" class="form-select">
        <option value="all">All Statuses</option>
        <option value="planning">Planning</option>
        <option value="running">Running</option>
        <option value="completed">Completed</option>
    </select>

    <select wire:model.live="clientFilter" class="form-select">
        <option value="">All Clients</option>
        @foreach($clients as $client)
            <option value="{{ $client->id }}">{{ $client->name }}</option>
        @endforeach
    </select>
</div>
```

## Database Models

### Example: Campaign Model
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Campaign extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'client_id',
        'ad_account_id',
        'name',
        'external_campaign_id',
        'objective',
        'status',
        'start_date',
        'end_date',
        'daily_budget',
        'lifetime_budget',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'daily_budget' => 'decimal:4',
        'lifetime_budget' => 'decimal:4',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function adAccount(): BelongsTo
    {
        return $this->belongsTo(AdAccount::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function creativeRequests(): HasMany
    {
        return $this->hasMany(CreativeRequest::class);
    }

    public function dailyMetrics(): HasMany
    {
        return $this->hasMany(DailyMetric::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }
}
```

## Installation & Setup

### 1. Install Dependencies
```bash
cd laravel-app
composer install
npm install
```

### 2. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and configure your PostgreSQL database:
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=digicloudify_os
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### 3. Run Migrations
```bash
php artisan migrate
```

### 4. Install Livewire
```bash
composer require livewire/livewire
php artisan livewire:publish --config
php artisan livewire:publish --assets
```

### 5. Setup Tailwind CSS
```bash
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p
```

### 6. Build Assets
```bash
npm run dev
```

### 7. Serve Application
```bash
php artisan serve
```

## Key Differences & Considerations

### 1. **Rendering**
- **Next.js:** Client-side rendering with hydration
- **Livewire:** Server-side rendering with AJAX updates

### 2. **State Management**
- **React:** useState, useEffect, Context API
- **Livewire:** Public properties, computed properties

### 3. **Real-time Updates**
- **React:** WebSocket connections, manual polling
- **Livewire:** Built-in polling, Laravel Echo for broadcasting

### 4. **Form Handling**
- **React:** Controlled components, form libraries
- **Livewire:** wire:model, automatic validation

### 5. **Routing**
- **Next.js:** File-based routing
- **Laravel:** Route definitions in routes/web.php

### 6. **Authentication**
- **Next.js:** Custom JWT/session handling
- **Laravel:** Built-in Auth, Sanctum, Middleware

## Performance Optimizations

### 1. Lazy Loading
```php
use Livewire\Attributes\Lazy;

#[Lazy]
class CampaignsList extends Component
{
    public function render()
    {
        return view('livewire.campaigns-list');
    }

    public function placeholder()
    {
        return view('livewire.placeholders.loading');
    }
}
```

### 2. Query Optimization
```php
public function mount()
{
    $this->campaigns = Campaign::with(['client', 'adAccount'])
        ->select('id', 'name', 'status', 'client_id', 'ad_account_id')
        ->forOrganization(auth()->user()->organization_id)
        ->get();
}
```

### 3. Caching
```php
use Illuminate\Support\Facades\Cache;

public function getDashboardStats()
{
    return Cache::remember(
        "dashboard_stats_{$this->organizationId}",
        now()->addMinutes(5),
        fn() => $this->calculateStats()
    );
}
```

## Testing

### Livewire Component Test
```php
use Livewire\Livewire;
use Tests\TestCase;

class CampaignsKanbanBoardTest extends TestCase
{
    public function test_can_update_campaign_status()
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create([
            'organization_id' => $user->organization_id,
            'status' => 'planning'
        ]);

        Livewire::actingAs($user)
            ->test(CampaignsKanbanBoard::class)
            ->call('updateCampaignStatus', $campaign->id, 'running')
            ->assertDispatched('campaignUpdated');

        $this->assertEquals('running', $campaign->fresh()->status);
    }
}
```

## Next Steps

1. Complete all database migrations
2. Create all Eloquent models
3. Convert remaining React components to Livewire
4. Implement authentication system
5. Set up middleware for organization context
6. Create blade components for UI elements
7. Implement real-time notifications with Laravel Echo
8. Write comprehensive tests
9. Deploy to production

## Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Livewire Documentation](https://livewire.laravel.com/docs)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [AlpineJS](https://alpinejs.dev/) (for client-side interactivity)
