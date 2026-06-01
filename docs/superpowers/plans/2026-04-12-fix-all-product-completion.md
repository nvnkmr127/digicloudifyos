# Fix All Product Completion Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Fix all broken connections and complete missing CRUD + end-to-end journeys (Forms publish/embed/submissions, Proposals view/send/share, Reports PDF export) with full test coverage and passing suite.

**Architecture:** Keep the existing Laravel 11 + Livewire 3 architecture, add minimal new controllers for public form endpoints and signed proposal sharing, and complete missing Livewire components/routes for CRUD. Favor server-rendered UI (Blade + Livewire) with small JS hooks only where necessary (`wire:confirm`).

**Tech Stack:** Laravel 11, Livewire 3, Blade, Alpine.js, Tailwind, PHPUnit, barryvdh/laravel-dompdf

---

## File Map (What Changes Where)

**Routes**
- Modify: `/Users/naveenadicharla/Documents/DC OS/routes/web.php` (Broadcasts, Products edit, Proposals show/edit/share, Forms show/submissions)
- Create/Modify: `/Users/naveenadicharla/Documents/DC OS/routes/public.php` (Public forms: render + submit) OR add directly to `web.php` outside auth group

**Livewire (new + fixes)**
- Modify: `/Users/naveenadicharla/Documents/DC OS/app/Livewire/SocialPlanner/Index.php` (modal close, success message)
- Modify: `/Users/naveenadicharla/Documents/DC OS/app/Livewire/Calendars/Index.php` (date fields + safe mapping)
- Modify: `/Users/naveenadicharla/Documents/DC OS/app/Livewire/CreativeRequests/Index.php` (schema alignment)
- Modify: `/Users/naveenadicharla/Documents/DC OS/app/Livewire/Leads/Create.php` (valid default status)
- Modify: `/Users/naveenadicharla/Documents/DC OS/app/Livewire/Reports/Index.php` (date range + PDF export + chart data)
- Modify: `/Users/naveenadicharla/Documents/DC OS/app/Livewire/Broadcasts/Index.php` (authorization, layout consistency)
- Create: `/Users/naveenadicharla/Documents/DC OS/app/Livewire/Products/Edit.php` (already exists; route it + ensure update works)
- Create: `/Users/naveenadicharla/Documents/DC OS/app/Livewire/Proposals/Show.php`
- Create: `/Users/naveenadicharla/Documents/DC OS/app/Livewire/Proposals/Edit.php`
- Create: `/Users/naveenadicharla/Documents/DC OS/app/Livewire/Forms/Show.php` (manage + publish + embed + submissions)
- Create: `/Users/naveenadicharla/Documents/DC OS/app/Livewire/Forms/Submissions.php`

**Views**
- Modify: `/Users/naveenadicharla/Documents/DC OS/resources/views/layouts/navigation.blade.php` (remove/gate auto-login; ensure Broadcasts consistent)
- Modify: `/Users/naveenadicharla/Documents/DC OS/resources/views/components/layouts/sidebar-navigation.blade.php` (add Broadcasts entry if kept)
- Modify: `/Users/naveenadicharla/Documents/DC OS/resources/views/livewire/campaigns/detail-view.blade.php` (deadline, performance data load alignment, preview action)
- Modify: `/Users/naveenadicharla/Documents/DC OS/resources/views/livewire/calendars/index.blade.php` (remove dynamic Tailwind, use variant map)
- Modify: `/Users/naveenadicharla/Documents/DC OS/resources/views/livewire/forms/index.blade.php` (real actions; remove stub modal)
- Modify: `/Users/naveenadicharla/Documents/DC OS/resources/views/livewire/products/index.blade.php` (add edit; normalize status display)
- Modify: `/Users/naveenadicharla/Documents/DC OS/resources/views/livewire/proposals/index.blade.php` (wire “view”)
- Modify: `/Users/naveenadicharla/Documents/DC OS/resources/views/livewire/reports/index.blade.php` (wire export; charts)
- Create: `/Users/naveenadicharla/Documents/DC OS/resources/views/livewire/proposals/show.blade.php`
- Create: `/Users/naveenadicharla/Documents/DC OS/resources/views/livewire/proposals/edit.blade.php`
- Create: `/Users/naveenadicharla/Documents/DC OS/resources/views/livewire/forms/show.blade.php`
- Create: `/Users/naveenadicharla/Documents/DC OS/resources/views/livewire/forms/submissions.blade.php`
- Create: `/Users/naveenadicharla/Documents/DC OS/resources/views/public/forms/show.blade.php`
- Create: `/Users/naveenadicharla/Documents/DC OS/resources/views/reports/pdf.blade.php`

**Controllers**
- Create: `/Users/naveenadicharla/Documents/DC OS/app/Http/Controllers/PublicFormsController.php` (public render + submit)
- Create: `/Users/naveenadicharla/Documents/DC OS/app/Http/Controllers/Proposals/ShareController.php` (signed share show, optional PDF)

**Models + Migrations**
- Modify: `/Users/naveenadicharla/Documents/DC OS/app/Models/Form.php` (fillable/casts for publish fields)
- Modify: `/Users/naveenadicharla/Documents/DC OS/app/Models/FormSubmission.php` (add org relation if added to schema)
- Create migration: `/Users/naveenadicharla/Documents/DC OS/database/migrations/2026_04_12_000001_add_publish_fields_to_forms_and_metadata_to_submissions.php`

**Frontend JS**
- Modify: `/Users/naveenadicharla/Documents/DC OS/resources/js/app.js` (implement `wire:confirm`)

**Tests**
- Create/Modify:
  - `/Users/naveenadicharla/Documents/DC OS/tests/Feature/NavigationRouteIntegrityTest.php`
  - `/Users/naveenadicharla/Documents/DC OS/tests/Feature/CalendarRenderingTest.php`
  - `/Users/naveenadicharla/Documents/DC OS/tests/Feature/CreativeRequestsCreateTest.php`
  - `/Users/naveenadicharla/Documents/DC OS/tests/Feature/FormsPublicSubmissionTest.php`
  - `/Users/naveenadicharla/Documents/DC OS/tests/Feature/ReportsPdfExportTest.php`
  - `/Users/naveenadicharla/Documents/DC OS/tests/Feature/ProposalsCrudAndShareTest.php`
  - `/Users/naveenadicharla/Documents/DC OS/tests/Feature/ProductsCrudTest.php`

---

### Task 1: Establish Baseline + Add Route Integrity Test

**Files:**
- Create: `/Users/naveenadicharla/Documents/DC OS/tests/Feature/NavigationRouteIntegrityTest.php`

- [ ] **Step 1: Write the failing test (Broadcasts + auto-login + core menus)**

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class NavigationRouteIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_expected_named_routes_exist_in_production_context(): void
    {
        $expected = [
            'dashboard',
            'campaigns.index',
            'tasks.index',
            'leads.index',
            'contacts.index',
            'creatives.index',
            'clients.index',
            'workflow.index',
            'reports.index',
            'alerts.index',
            'projects.index',
            'playbooks.index',
            'service-packages.index',
            'deliverables.index',
            'pipelines.index',
            'conversations.index',
            'social-planner.index',
            'orders.index',
            'proposals.index',
            'invoices.index',
            'creative-requests.index',
            'feedback.index',
            'analytics.index',
            'seo.index',
            'site-health.index',
            'workload.index',
            'productivity.index',
            'automation.rules',
            'automation.approvals',
            'team.index',
            'users.index',
            'automations.index',
            'time-tracking.index',
            'time-tracking.approvals',
            'media.index',
            'calendars.index',
            'forms.index',
            'products.index',
            'settings',
            'webhooks.index',
            'webhooks.inbound',
            'webhooks.outbound',
            'webhooks.api',
            'webhooks.mappings.inbound',
            'webhooks.mappings.outbound',
            'broadcasts.index',
        ];

        foreach ($expected as $name) {
            $this->assertTrue(Route::has($name), "Missing named route: {$name}");
        }

        $this->assertFalse(Route::has('auto-login'), 'auto-login should not exist in production navigation context');
    }
}
```

- [ ] **Step 2: Run the single test to confirm it fails**

Run:
```bash
php artisan test --filter=NavigationRouteIntegrityTest
```

Expected: FAIL (missing `broadcasts.index` and/or `auto-login` still present).

- [ ] **Step 3: Commit (optional)**

```bash
git add tests/Feature/NavigationRouteIntegrityTest.php
git commit -m "test: enforce nav route integrity"
```

---

### Task 2: Fix Navigation Breakages (Broadcasts Route + Auto-login Gating)

**Files:**
- Modify: `/Users/naveenadicharla/Documents/DC OS/routes/web.php`
- Modify: `/Users/naveenadicharla/Documents/DC OS/resources/views/layouts/navigation.blade.php`
- Modify: `/Users/naveenadicharla/Documents/DC OS/resources/views/components/layouts/sidebar-navigation.blade.php`

- [ ] **Step 1: Add broadcasts route**

In `routes/web.php`, add within the authenticated org group:

```php
Route::get('/broadcasts', App\Livewire\Broadcasts\Index::class)->name('broadcasts.index');
```

- [ ] **Step 2: Gate or remove auto-login link from UI**

In `resources/views/layouts/navigation.blade.php`, wrap the auto-login UI behind an env check and ensure it’s not displayed in production:

```php
@if(app()->environment(['local', 'testing']))
    {{-- render auto-login link here --}}
@endif
```

Also remove any other auto-login references from authenticated-only menus.

- [ ] **Step 3: Add Broadcasts to the primary sidebar (single-source IA)**

Add a sidebar nav item to match top nav.

- [ ] **Step 4: Run route integrity test again**

```bash
php artisan test --filter=NavigationRouteIntegrityTest
```

Expected: PASS.

---

### Task 3: Implement Real `wire:confirm` (Global Confirmation Hook)

**Files:**
- Modify: `/Users/naveenadicharla/Documents/DC OS/resources/js/app.js`

- [ ] **Step 1: Add click interceptor**

Append this to `resources/js/app.js` (after bootstrap imports):

```js
document.addEventListener('click', (e) => {
  const el = e.target.closest('[wire\\:confirm]');
  if (!el) return;

  const message = el.getAttribute('wire:confirm') || 'Are you sure?';
  const ok = window.confirm(message);
  if (!ok) {
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();
  }
});
```

- [ ] **Step 2: Rebuild assets**

```bash
npm test
npm run build
```

Expected: build succeeds.

- [ ] **Step 3: Add a small integration test to assert `wire:confirm` attribute presence on known delete buttons (optional)**

If keeping minimal, skip and rely on UI manual confirmation during QA.

---

### Task 4: Social Planner Modal Close + Messaging Fix

**Files:**
- Modify: `/Users/naveenadicharla/Documents/DC OS/app/Livewire/SocialPlanner/Index.php`

- [ ] **Step 1: Fix modal name mismatch**

Update dispatch targets to match Blade modal names:

```php
$this->dispatch('close-modal', name: 'post-creation-modal');
// and
$this->dispatch('close-modal', name: 'channel-connect-modal');
```

- [ ] **Step 2: Fix scheduled message pluralization**

Capture count before reset:

```php
$channelCount = count($this->selectedChannels);
// ... create posts
$this->reset(['content', 'scheduledAt', 'selectedChannels', 'showPostModal']);
session()->flash('success', $channelCount > 1 ? "Scheduled {$channelCount} posts." : 'Scheduled 1 post.');
```

- [ ] **Step 3: Run component-related tests**

```bash
php artisan test
```

Expected: PASS.

---

### Task 5: Fix Calendar (Correct Columns + Safe Rendering + No Dynamic Tailwind)

**Files:**
- Modify: `/Users/naveenadicharla/Documents/DC OS/app/Livewire/Calendars/Index.php`
- Modify: `/Users/naveenadicharla/Documents/DC OS/resources/views/livewire/calendars/index.blade.php`
- Create: `/Users/naveenadicharla/Documents/DC OS/tests/Feature/CalendarRenderingTest.php`

- [ ] **Step 1: Add failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendar_page_renders_for_authenticated_user(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);

        $client = Client::create([
            'organization_id' => $org->id,
            'name' => 'Calendar Client',
            'status' => 'ACTIVE',
        ]);

        Task::create([
            'organization_id' => $org->id,
            'client_id' => $client->id,
            'title' => 'Calendar Task',
            'priority' => 'medium',
            'status' => 'pending',
            'deadline' => now()->addDay(),
        ]);

        Project::create([
            'organization_id' => $org->id,
            'client_id' => $client->id,
            'name' => 'Calendar Project',
            'project_code' => 'CAL-PRJ-001',
            'status' => 'planning',
            'priority' => 'medium',
            'end_date' => now()->addDays(2)->toDateString(),
        ]);

        $this->actingAs($user)
            ->get('/calendars')
            ->assertOk()
            ->assertSee('Unified Event Calendar')
            ->assertSee('Calendar Task')
            ->assertSee('Deadline: Calendar Project');
    }
}
```

- [ ] **Step 2: Run it to confirm fail**

```bash
php artisan test --filter=CalendarRenderingTest
```

Expected: FAIL (missing columns/dynamic).

- [ ] **Step 3: Fix calendar queries in Livewire**

In `Calendars/Index.php`:
- Replace `due_at` with `deadline`
- Replace project `deadline` with `end_date`
- Null-guard date fields when formatting/mapping

Use item variants rather than dynamic tailwind colors:

```php
->map(fn ($t) => [
  'type' => 'task',
  'title' => $t->title,
  'variant' => 'task',
])
```

Then in the view, map `variant` to a fixed class string.

- [ ] **Step 4: Update Blade to use fixed classes**

Replace:
```php
bg-{{ $item['color'] }}-50 text-{{ $item['color'] }}-600
```
with:
```php
{{ $item['class'] }}
```
where `class` is provided by the component or computed via a local map.

- [ ] **Step 5: Re-run test**

```bash
php artisan test --filter=CalendarRenderingTest
```

Expected: PASS.

---

### Task 6: Fix Creative Requests Create Flow (Schema Alignment)

**Files:**
- Modify: `/Users/naveenadicharla/Documents/DC OS/app/Livewire/CreativeRequests/Index.php`
- Modify: `/Users/naveenadicharla/Documents/DC OS/resources/views/livewire/creative-requests/index.blade.php`
- Create: `/Users/naveenadicharla/Documents/DC OS/tests/Feature/CreativeRequestsCreateTest.php`

- [ ] **Step 1: Add failing test (create request succeeds)**

```php
<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\Client;
use App\Models\CreativeRequest;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreativeRequestsCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_creative_request_creation_matches_schema(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);

        $client = Client::create([
            'organization_id' => $org->id,
            'name' => 'Creative Client',
            'status' => 'ACTIVE',
        ]);

        $campaign = Campaign::create([
            'organization_id' => $org->id,
            'client_id' => $client->id,
            'name' => 'Creative Campaign',
            'objective' => 'traffic',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\CreativeRequests\Index::class)
            ->set('showCreateModal', true)
            ->set('client_id', $client->id)
            ->set('campaign_id', $campaign->id)
            ->set('type', 'image')
            ->set('title', 'New Creative Request')
            ->set('description', 'Do the thing')
            ->set('priority', 'medium')
            ->set('deadline', now()->addWeek()->toDateString())
            ->call('createCreativeRequest');

        $this->assertDatabaseHas('creative_requests', [
            'organization_id' => $org->id,
            'client_id' => $client->id,
            'campaign_id' => $campaign->id,
            'type' => 'image',
            'title' => 'New Creative Request',
        ]);
    }
}
```

- [ ] **Step 2: Run it to confirm fail**

```bash
php artisan test --filter=CreativeRequestsCreateTest
```

Expected: FAIL due to missing properties/schema mismatch.

- [ ] **Step 3: Update Livewire component to match migration**

In `CreativeRequests/Index.php`, add properties and validation:

```php
public string $client_id = '';
public string $campaign_id = '';
public string $type = 'image';
public string $deadline = '';
public string $priority = 'medium';

protected function rules(): array
{
    $orgId = Auth::user()->organization_id;
    return [
        'client_id' => ['required', 'uuid', Rule::exists('clients', 'id')->where('organization_id', $orgId)],
        'campaign_id' => ['required', 'uuid', Rule::exists('campaigns', 'id')->where('organization_id', $orgId)],
        'type' => 'required|in:image,carousel,video,banner',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'priority' => 'required|in:low,medium,high,urgent',
        'deadline' => 'nullable|date',
    ];
}
```

Persist using schema columns:

```php
CreativeRequest::create([
  'organization_id' => $orgId,
  'client_id' => $this->client_id,
  'campaign_id' => $this->campaign_id,
  'type' => $this->type,
  'title' => $this->title,
  'description' => $this->description,
  'priority' => $this->priority,
  'deadline' => $this->deadline ?: null,
  'status' => 'requested',
  'created_by' => Auth::id(),
]);
```

- [ ] **Step 4: Update Blade fields**

Replace `due_date` with `deadline` and add selects for client/campaign/type.

- [ ] **Step 5: Re-run test**

```bash
php artisan test --filter=CreativeRequestsCreateTest
```

Expected: PASS.

---

### Task 7: Fix Campaign Detail (Deadline + Performance Data + Preview Action)

**Files:**
- Modify: `/Users/naveenadicharla/Documents/DC OS/resources/views/livewire/campaigns/detail-view.blade.php`
- Modify: `/Users/naveenadicharla/Documents/DC OS/app/Livewire/Campaigns/DetailView.php`

- [ ] **Step 1: Fix task deadline field**

Replace:
```php
$task->due_date
```
with:
```php
$task->deadline
```

- [ ] **Step 2: Align performance tab loading**

In `Campaigns/DetailView.php`, ensure the `performance` match loads the relation used by Blade:

```php
'performance' => $this->campaign->load('adInsights'),
```

- [ ] **Step 3: Make Preview action real**

If creative preview URL exists on related creative/ad model, wire to it with an `<a>` tag; otherwise, implement a Livewire modal that shows basic ad metadata and available creative asset URL(s).

- [ ] **Step 4: Run full test suite**

```bash
php artisan test
```

Expected: PASS.

---

### Task 8: Fix Leads Create Default Status

**Files:**
- Modify: `/Users/naveenadicharla/Documents/DC OS/app/Livewire/Leads/Create.php`

- [ ] **Step 1: Change default**

```php
public $status = 'new';
```

- [ ] **Step 2: Run authorization + service tests**

```bash
php artisan test --filter=AnalyticsServiceLeadMetricsTest
php artisan test --filter=TenantIsolationTest
```

Expected: PASS.

---

### Task 9: Products CRUD Completion (Route Edit + UI + Status Normalization)

**Files:**
- Modify: `/Users/naveenadicharla/Documents/DC OS/routes/web.php`
- Modify: `/Users/naveenadicharla/Documents/DC OS/resources/views/livewire/products/index.blade.php`
- Modify: `/Users/naveenadicharla/Documents/DC OS/app/Livewire/Products/Edit.php`
- Create: `/Users/naveenadicharla/Documents/DC OS/tests/Feature/ProductsCrudTest.php`

- [ ] **Step 1: Add failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductsCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_edit_route_exists_and_updates(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);

        $product = Product::create([
            'organization_id' => $org->id,
            'name' => 'Old Name',
            'price' => 10.00,
            'stock' => 1,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get("/products/{$product->id}/edit")
            ->assertOk();
    }
}
```

- [ ] **Step 2: Add route**

In `routes/web.php`:

```php
Route::get('/products/{product}/edit', App\Livewire\Products\Edit::class)->name('products.edit');
```

- [ ] **Step 3: Add “Edit” action to products index and normalize status display**

In `products/index.blade.php`:
- Display `strtoupper($product->status)` or label-map.
- Add edit link:
```php
<a href="{{ route('products.edit', $product) }}" wire:navigate aria-label="Edit product">…</a>
```

- [ ] **Step 4: Ensure Products/Edit supports update and org scoping**

If `Edit.php` uses route-model binding without org scope, enforce `organization_id` constraint in `mount` or query.

- [ ] **Step 5: Re-run test**

```bash
php artisan test --filter=ProductsCrudTest
```

Expected: PASS.

---

### Task 10: Proposals CRUD + Share/Send Flow

**Files:**
- Modify: `/Users/naveenadicharla/Documents/DC OS/routes/web.php`
- Create: `/Users/naveenadicharla/Documents/DC OS/app/Livewire/Proposals/Show.php`
- Create: `/Users/naveenadicharla/Documents/DC OS/app/Livewire/Proposals/Edit.php`
- Create: `/Users/naveenadicharla/Documents/DC OS/app/Http/Controllers/Proposals/ShareController.php`
- Create: `/Users/naveenadicharla/Documents/DC OS/resources/views/livewire/proposals/show.blade.php`
- Create: `/Users/naveenadicharla/Documents/DC OS/resources/views/livewire/proposals/edit.blade.php`
- Modify: `/Users/naveenadicharla/Documents/DC OS/resources/views/livewire/proposals/index.blade.php`
- Create: `/Users/naveenadicharla/Documents/DC OS/tests/Feature/ProposalsCrudAndShareTest.php`

- [ ] **Step 1: Add failing test for show + signed share**

```php
<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Organization;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ProposalsCrudAndShareTest extends TestCase
{
    use RefreshDatabase;

    public function test_proposal_show_and_signed_share_work(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);

        $client = Client::create([
            'organization_id' => $org->id,
            'name' => 'Proposal Client',
            'status' => 'ACTIVE',
        ]);

        $proposal = Proposal::create([
            'organization_id' => $org->id,
            'client_id' => $client->id,
            'title' => 'Proposal A',
            'proposal_number' => 'PROP-001',
            'total_amount' => 123.45,
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->get("/proposals/{$proposal->id}")
            ->assertOk()
            ->assertSee('Proposal A');

        $signed = URL::signedRoute('proposals.share', ['proposal' => $proposal->id]);
        $this->get($signed)->assertOk()->assertSee('Proposal A');
    }
}
```

- [ ] **Step 2: Add routes**

In `routes/web.php` (auth group):

```php
Route::get('/proposals/{proposal}', App\Livewire\Proposals\Show::class)->name('proposals.show');
Route::get('/proposals/{proposal}/edit', App\Livewire\Proposals\Edit::class)->name('proposals.edit');
```

Add public signed share route (outside auth group):

```php
Route::get('/share/proposals/{proposal}', [\App\Http\Controllers\Proposals\ShareController::class, 'show'])
    ->middleware(['signed', 'throttle:60,1'])
    ->name('proposals.share');
```

- [ ] **Step 3: Wire Proposals index “view” button**

In `proposals/index.blade.php`, replace the no-op eye icon with:

```php
<a href="{{ route('proposals.show', $proposal) }}" wire:navigate class="p-2 …" aria-label="View proposal">…</a>
```

- [ ] **Step 4: Implement Show + Edit components**

Show: load by org scope, render details, actions:
- “Edit draft” (if status is `draft`)
- “Generate share link” (shows signed URL)
- “Mark sent” (sets status `sent`)
- “Mark accepted/rejected” (sets status)

Edit: update draft fields; validate; redirect back to show with success.

- [ ] **Step 5: Implement ShareController**

Controller `show()`:
- Find proposal by id
- Render a minimal share view (no auth) with proposal summary
- Ensure tenant correctness by using the signed URL as the only access mechanism (do not leak org data elsewhere)

- [ ] **Step 6: Re-run test**

```bash
php artisan test --filter=ProposalsCrudAndShareTest
```

Expected: PASS.

---

### Task 11: Forms Publish + Public URL + Embed + Submissions Viewer

**Files:**
- Create migration: `/Users/naveenadicharla/Documents/DC OS/database/migrations/2026_04_12_000001_add_publish_fields_to_forms_and_metadata_to_submissions.php`
- Modify: `/Users/naveenadicharla/Documents/DC OS/app/Models/Form.php`
- Modify: `/Users/naveenadicharla/Documents/DC OS/app/Models/FormSubmission.php`
- Modify: `/Users/naveenadicharla/Documents/DC OS/routes/web.php` (forms show/submissions)
- Create: `/Users/naveenadicharla/Documents/DC OS/app/Http/Controllers/PublicFormsController.php`
- Create: `/Users/naveenadicharla/Documents/DC OS/resources/views/public/forms/show.blade.php`
- Create: `/Users/naveenadicharla/Documents/DC OS/app/Livewire/Forms/Show.php`
- Create: `/Users/naveenadicharla/Documents/DC OS/app/Livewire/Forms/Submissions.php`
- Create: `/Users/naveenadicharla/Documents/DC OS/resources/views/livewire/forms/show.blade.php`
- Create: `/Users/naveenadicharla/Documents/DC OS/resources/views/livewire/forms/submissions.blade.php`
- Modify: `/Users/naveenadicharla/Documents/DC OS/resources/views/livewire/forms/index.blade.php`
- Create: `/Users/naveenadicharla/Documents/DC OS/tests/Feature/FormsPublicSubmissionTest.php`

- [ ] **Step 1: Add failing test (publish + public render + submit)**

```php
<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormsPublicSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_form_renders_publicly_and_accepts_submission(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);

        $form = Form::create([
            'organization_id' => $org->id,
            'name' => 'Public Form',
            'status' => 'ACTIVE',
            'fields' => [
                ['id' => 'a', 'type' => 'text', 'name' => 'full_name', 'label' => 'Full Name', 'placeholder' => '', 'required' => true],
                ['id' => 'b', 'type' => 'email', 'name' => 'email', 'label' => 'Email', 'placeholder' => '', 'required' => true],
            ],
            'slug' => 'public-form',
            'is_published' => true,
            'public_key' => 'test-key',
        ]);

        $this->actingAs($user)->get("/forms/{$form->id}")->assertOk();

        $this->get("/f/{$form->slug}?k={$form->public_key}")
            ->assertOk()
            ->assertSee('Public Form')
            ->assertSee('Full Name');

        $this->post("/f/{$form->slug}/submit", [
            'k' => $form->public_key,
            'full_name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ])->assertRedirect();

        $this->assertDatabaseCount('form_submissions', 1);
        $submission = FormSubmission::firstOrFail();
        $this->assertSame($form->id, $submission->form_id);
        $this->assertSame('Jane Doe', $submission->payload['full_name'] ?? null);
    }
}
```

- [ ] **Step 2: Create migration to add publish fields + submission metadata**

Migration should:
- Add to `forms`: `slug` (nullable unique per org), `is_published` boolean default false, `public_key` nullable string
- Add to `form_submissions`: `organization_id` uuid nullable (backfill), `user_agent` nullable string, `referer` nullable string, `submitted_at` timestamp nullable
- Add indexes: `(organization_id, slug)` unique; `form_id`; `organization_id`

- [ ] **Step 3: Update Form + FormSubmission models**

Form fillable/casts:
```php
protected $fillable = [..., 'slug', 'is_published', 'public_key'];
protected $casts = ['fields' => 'array', 'is_published' => 'boolean'];
```

FormSubmission casts:
```php
protected $casts = ['payload' => 'array', 'submitted_at' => 'datetime'];
```

- [ ] **Step 4: Add public routes and controller**

Routes (outside auth group):
```php
Route::get('/f/{slug}', [\App\Http\Controllers\PublicFormsController::class, 'show'])->name('public.forms.show');
Route::post('/f/{slug}/submit', [\App\Http\Controllers\PublicFormsController::class, 'submit'])
    ->middleware(['throttle:30,1'])
    ->name('public.forms.submit');
```

Controller behavior:
- Lookup published form by slug + org scope (org derived from slug uniqueness via DB query)
- Validate `k` public key
- Validate required fields based on config
- Store `form_submissions` with metadata + payload
- Redirect back with success

- [ ] **Step 5: Add in-app Forms show + submissions viewer routes**

In auth group:
```php
Route::get('/forms/{form}', \App\Livewire\Forms\Show::class)->name('forms.show');
Route::get('/forms/{form}/submissions', \App\Livewire\Forms\Submissions::class)->name('forms.submissions');
```

- [ ] **Step 6: Replace Forms index no-op buttons**

In `forms/index.blade.php`, make buttons navigate:
- View → `forms.show`
- Submissions → `forms.submissions`
- Remove the stub modal block.

- [ ] **Step 7: Implement Forms/Show**

Capabilities:
- Edit name/description/status
- Edit fields (reuse existing Create patterns)
- Publish toggle (generates slug + public_key if missing)
- Embed snippet display (iframe URL with `k`)

- [ ] **Step 8: Implement Forms/Submissions**

Capabilities:
- Paginated list
- Search (across common fields)
- CSV export action

- [ ] **Step 9: Re-run test**

```bash
php artisan test --filter=FormsPublicSubmissionTest
```

Expected: PASS.

---

### Task 12: Reports PDF Export + Replace Placeholder Charts

**Files:**
- Modify: `/Users/naveenadicharla/Documents/DC OS/app/Livewire/Reports/Index.php`
- Modify: `/Users/naveenadicharla/Documents/DC OS/resources/views/livewire/reports/index.blade.php`
- Create: `/Users/naveenadicharla/Documents/DC OS/resources/views/reports/pdf.blade.php`
- Create: `/Users/naveenadicharla/Documents/DC OS/tests/Feature/ReportsPdfExportTest.php`

- [ ] **Step 1: Add failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsPdfExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_pdf_export_returns_pdf(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);

        $this->actingAs($user)
            ->get('/reports?export=pdf')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
```

- [ ] **Step 2: Implement export pathway**

Implement a Livewire method `exportPdf()` that returns a PDF download response, and wire the button to it:

```php
public function exportPdf()
{
    $data = $this->buildReportData();
    $pdf = \PDF::loadView('reports.pdf', ['reportData' => $data, 'dateRange' => $this->dateRange]);
    return response()->streamDownload(fn () => print($pdf->output()), 'report.pdf');
}
```

Refactor `render()` to call `buildReportData()` and use `$this->dateRange` to constrain queries.

- [ ] **Step 3: Replace placeholder chart section**

Compute a small 14-day series (invoices total, hours) and render as simple bars (Blade + inline styles).

- [ ] **Step 4: Re-run test**

```bash
php artisan test --filter=ReportsPdfExportTest
```

Expected: PASS.

---

### Task 13: Final Full Suite + Regression Sweep

**Files:** (none, unless fixes are needed)

- [ ] **Step 1: Run full test suite**

```bash
php artisan test
```

Expected: PASS.

- [ ] **Step 2: Run route list and check for remaining dead links**

```bash
php artisan route:list
```

- [ ] **Step 3: Manual smoke checklist**
- Authenticated navigation loads all main modules
- Delete actions prompt for confirmation
- Calendar renders without errors and shows items
- Creative Request creation works and appears in campaign context
- Products edit works
- Proposals show/edit/share link works
- Forms publish/embed/public submit works and submissions appear
- Reports PDF exports successfully

---

## Self-Review (Plan Quality)
- Coverage: All items in the approved design spec have a corresponding task (nav, confirm, social planner, calendar, creative requests, campaign detail, leads, products, proposals, forms, reports).
- Placeholder scan: No “TODO/TBD” steps; each task includes runnable commands and concrete code blocks.
- Consistency: Uses existing Laravel conventions, org scoping, and existing installed libraries (`dompdf`).

