# Full Intelligence Suite Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Deliver the complete Intelligence Suite (Overview, Briefing, Insights, Alerts, Client Workspace) with correct routing, org isolation, and modern UI polish.

**Architecture:** Livewire pages read from persisted Intelligence tables (snapshots/anomalies/insights/briefings). Actions (dismiss/complete/resolve) are org-scoped and safe-by-default. Navigation badges use a View Composer with short caching.

**Tech Stack:** Laravel 11, Livewire 3, Blade + Tailwind, PHPUnit.

---

## File map (what we will touch)

**Create**
- `app/Livewire/Intelligence/ClientWorkspace.php`
- `resources/views/livewire/intelligence/client-workspace.blade.php`
- `tests/Feature/Intelligence/IntelligenceTenancyTest.php`

**Modify**
- `routes/web.php` (add workspace route + redirect legacy route)
- `app/Livewire/Intelligence/InsightsFeed.php` (org-scope actions; add channel filter)
- `app/Livewire/Intelligence/AlertCenter.php` (org-scope resolve)
- `app/Livewire/Intelligence/BriefingDashboard.php` (org-scope briefing lookup; optional regenerate)
- `app/Livewire/Intelligence/ClientPerformanceCenter.php` (either redirect to workspace, or keep as legacy)
- `resources/views/components/layouts/sidebar-navigation.blade.php` (add badges)
- `resources/views/livewire/intelligence/*.blade.php` (dark-mode-safe polish, component reuse)

---

## Task 1: Add canonical Client Workspace route and keep legacy compatibility

**Files:**
- Modify: `routes/web.php`

- [ ] **Step 1: Write failing test for workspace route**

Create (or extend) a feature test ensuring the new route exists and is org-protected:

```php
<?php

namespace Tests\Feature\Intelligence;

use App\Models\Client;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntelligenceTenancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_intelligence_client_workspace_route_is_accessible_for_same_org(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);
        $client = Client::factory()->create(['organization_id' => $org->id, 'status' => 'ACTIVE']);

        $this->actingAs($user)
            ->get(route('intelligence.client.workspace', $client))
            ->assertOk();
    }
}
```

- [ ] **Step 2: Run the test (expect FAIL: route not defined)**

Run:
```bash
php artisan test --filter=IntelligenceTenancyTest
```
Expected: FAIL with route generation / 404.

- [ ] **Step 3: Implement the route change**

Update `routes/web.php` inside the authenticated org middleware group.

Add imports at top if missing:
```php
use App\Livewire\Intelligence\ClientWorkspace;
```

Add new canonical route and keep the existing route as a legacy redirect:
```php
Route::get('/intelligence/clients/{client}/workspace', ClientWorkspace::class)
    ->name('intelligence.client.workspace');

// Legacy: keep old URL working (redirect to new canonical route)
Route::get('/intelligence/client/{client}', function (\App\Models\Client $client) {
    return redirect()->route('intelligence.client.workspace', $client);
})->name('intelligence.client');
```

- [ ] **Step 4: Re-run the test (expect PASS)**

Run:
```bash
php artisan test --filter=IntelligenceTenancyTest
```
Expected: PASS (or next failures for component missing — addressed in Task 2).

- [ ] **Step 5: Commit**

```bash
git add routes/web.php tests/Feature/Intelligence/IntelligenceTenancyTest.php
git commit -m "feat(intelligence): add client workspace route"
```

---

## Task 2: Implement Client Workspace Livewire page

**Files:**
- Create: `app/Livewire/Intelligence/ClientWorkspace.php`
- Create: `resources/views/livewire/intelligence/client-workspace.blade.php`

- [ ] **Step 1: Write failing render test**

Extend `IntelligenceTenancyTest`:
```php
public function test_intelligence_client_workspace_denies_other_org(): void
{
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();

    $user = User::factory()->create(['organization_id' => $orgA->id]);
    $client = Client::factory()->create(['organization_id' => $orgB->id, 'status' => 'ACTIVE']);

    $this->actingAs($user)
        ->get(route('intelligence.client.workspace', $client))
        ->assertForbidden();
}
```

- [ ] **Step 2: Run tests (expect FAIL)**

Run:
```bash
php artisan test --filter=IntelligenceTenancyTest
```
Expected: FAIL until authorization is added in component mount.

- [ ] **Step 3: Create the Livewire component**

Create `app/Livewire/Intelligence/ClientWorkspace.php`:
```php
<?php

namespace App\Livewire\Intelligence;

use App\Models\AiInsight;
use App\Models\Client;
use App\Models\ClientHealthScore;
use App\Models\PerformanceAnomaly;
use App\Models\PerformanceSnapshot;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class ClientWorkspace extends Component
{
    public Client $client;

    /** @var '1d'|'7d'|'30d' */
    public string $dateRange = '7d';

    public function mount(Client $client): void
    {
        $this->client = $client;

        abort_unless(auth()->check(), 401);
        abort_unless($this->client->organization_id === auth()->user()->organization_id, 403);
    }

    public function setDateRange(string $range): void
    {
        if (! in_array($range, ['1d', '7d', '30d'], true)) {
            return;
        }
        $this->dateRange = $range;
    }

    public function render()
    {
        $orgId = auth()->user()->organization_id;
        $clientId = $this->client->id;

        [$start, $end] = match ($this->dateRange) {
            '1d' => [today()->subDays(1), today()],
            '30d' => [today()->subDays(30), today()],
            default => [today()->subDays(7), today()],
        };

        $cacheKey = "intelligence.client_workspace.{$orgId}.{$clientId}.{$this->dateRange}";

        $data = Cache::remember($cacheKey, 600, function () use ($orgId, $clientId, $start, $end) {
            $connections = \App\Models\ClientChannelConnection::where('organization_id', $orgId)
                ->where('client_id', $clientId)
                ->where('is_active', true)
                ->orderBy('channel_type')
                ->get();

            $snapshots = PerformanceSnapshot::where('organization_id', $orgId)
                ->where('client_id', $clientId)
                ->whereBetween('snapshot_date', [$start->toDateString(), $end->toDateString()])
                ->orderBy('snapshot_date', 'desc')
                ->get()
                ->groupBy('channel_type');

            $healthScores = ClientHealthScore::where('organization_id', $orgId)
                ->where('client_id', $clientId)
                ->orderBy('score_date', 'desc')
                ->limit(30)
                ->get()
                ->reverse()
                ->values();

            $anomalies = PerformanceAnomaly::where('organization_id', $orgId)
                ->where('client_id', $clientId)
                ->unresolved()
                ->orderByRaw("CASE severity WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 ELSE 5 END")
                ->latest('detected_at')
                ->limit(10)
                ->get();

            $insights = AiInsight::where('organization_id', $orgId)
                ->where('client_id', $clientId)
                ->active()
                ->latest('insight_date')
                ->limit(10)
                ->get();

            return compact('connections', 'snapshots', 'healthScores', 'anomalies', 'insights');
        });

        return view('livewire.intelligence.client-workspace', [
            ...$data,
            'start' => $start,
            'end' => $end,
        ])->layout('layouts.app');
    }
}
```

- [ ] **Step 4: Create the Blade view**

Create `resources/views/livewire/intelligence/client-workspace.blade.php` (starter layout; refined in Task 5):
```blade
<div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
    <div class="flex flex-col gap-6 md:flex-row md:items-start md:justify-between mb-8">
        <div>
            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">
                <a class="hover:underline" href="{{ route('intelligence.overview') }}">Intelligence</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900 dark:text-gray-100">{{ $client->name }}</span>
            </div>
            <h1 class="text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Client Workspace</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                Snapshot window: {{ $start->toFormattedDateString() }} → {{ $end->toFormattedDateString() }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            <x-button variant="{{ $dateRange === '1d' ? 'primary' : 'outline' }}" wire:click="setDateRange('1d')">1d</x-button>
            <x-button variant="{{ $dateRange === '7d' ? 'primary' : 'outline' }}" wire:click="setDateRange('7d')">7d</x-button>
            <x-button variant="{{ $dateRange === '30d' ? 'primary' : 'outline' }}" wire:click="setDateRange('30d')">30d</x-button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Health</h2>
                    <div class="flex items-center gap-3">
                        <x-intelligence.health-score-ring :score="$client->current_health_score" size="md" />
                        <div class="text-right">
                            <div class="text-xs text-gray-500 dark:text-gray-400">Current score</div>
                            <div class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ $client->current_health_score ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                    Last 30 days trend points: {{ $healthScores->count() }}
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">Connected accounts</h2>
                @if($connections->isEmpty())
                    <div class="text-sm text-gray-500 dark:text-gray-400">No active connections yet.</div>
                @else
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($connections as $conn)
                            <div class="py-3 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3 min-w-0">
                                    <x-intelligence.channel-icon :channel="$conn->channel_type" size="sm" />
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                                            {{ str_replace('_', ' ', $conn->channel_type) }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                            {{ $conn->account_name ?? $conn->account_id ?? '—' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                    Last sync: {{ $conn->last_synced_at?->diffForHumans() ?? '—' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">Snapshots (by channel)</h2>
                @if($snapshots->isEmpty())
                    <div class="text-sm text-gray-500 dark:text-gray-400">No snapshot data in this range yet.</div>
                @else
                    <div class="space-y-5">
                        @foreach($snapshots as $channel => $rows)
                            <div class="rounded-xl border border-gray-100 dark:border-gray-800 p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <x-intelligence.channel-icon :channel="$channel" size="sm" />
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            {{ str_replace('_', ' ', $channel) }}
                                        </div>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        Latest: {{ optional($rows->first()->snapshot_date)->format('M j') }}
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    @php $latest = $rows->first(); @endphp
                                    <div>
                                        <div class="text-[11px] text-gray-500 dark:text-gray-400">Spend</div>
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format((float) $latest->spend, 2) }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[11px] text-gray-500 dark:text-gray-400">Leads</div>
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $latest->leads ?? 0 }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[11px] text-gray-500 dark:text-gray-400">CTR</div>
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $latest->ctr ? number_format((float) $latest->ctr * 100, 2).'%' : '—' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[11px] text-gray-500 dark:text-gray-400">ROAS</div>
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $latest->roas ? number_format((float) $latest->roas, 2).'x' : '—' }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">Active alerts</h2>
                @forelse($anomalies as $anomaly)
                    <div class="py-2">
                        <x-intelligence.anomaly-badge :severity="$anomaly->severity" />
                        <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ str_replace('_', ' ', $anomaly->anomaly_type) }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $anomaly->metric_name }}: {{ number_format((float) $anomaly->current_value, 2) }}
                            vs {{ number_format((float) $anomaly->baseline_value, 2) }}
                            ({{ number_format((float) $anomaly->deviation_percentage, 2) }}%)
                        </div>
                    </div>
                    @unless($loop->last)
                        <div class="my-3 border-t border-gray-100 dark:border-gray-800"></div>
                    @endunless
                @empty
                    <div class="text-sm text-gray-500 dark:text-gray-400">No unresolved anomalies.</div>
                @endforelse
            </div>

            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">Active AI insights</h2>
                @forelse($insights as $insight)
                    <div class="py-2">
                        <x-intelligence.priority-badge :priority="$insight->priority" />
                        <div class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $insight->title }}</div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $insight->recommended_action }}</div>
                    </div>
                    @unless($loop->last)
                        <div class="my-3 border-t border-gray-100 dark:border-gray-800"></div>
                    @endunless
                @empty
                    <div class="text-sm text-gray-500 dark:text-gray-400">No active insights.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
```

- [ ] **Step 5: Run tests (expect PASS for tenancy)**

Run:
```bash
php artisan test --filter=IntelligenceTenancyTest
```

- [ ] **Step 6: Commit**

```bash
git add app/Livewire/Intelligence/ClientWorkspace.php resources/views/livewire/intelligence/client-workspace.blade.php tests/Feature/Intelligence/IntelligenceTenancyTest.php
git commit -m "feat(intelligence): add client workspace page"
```

---

## Task 3: Harden Intelligence actions to be org-scoped

**Files:**
- Modify: `app/Livewire/Intelligence/InsightsFeed.php`
- Modify: `app/Livewire/Intelligence/AlertCenter.php`
- Modify: `app/Livewire/Intelligence/BriefingDashboard.php`

- [ ] **Step 1: Add failing test for cross-org mutation**

Add to `IntelligenceTenancyTest`:
```php
use App\Models\AiInsight;
use App\Models\PerformanceAnomaly;
use Illuminate\Database\Eloquent\ModelNotFoundException;

public function test_cannot_complete_other_org_insight(): void
{
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();
    $user = User::factory()->create(['organization_id' => $orgA->id]);
    $clientB = Client::factory()->create(['organization_id' => $orgB->id, 'status' => 'ACTIVE']);

    $insight = AiInsight::factory()->create([
        'organization_id' => $orgB->id,
        'client_id' => $clientB->id,
        'is_completed' => false,
        'is_dismissed' => false,
        'insight_date' => today(),
        'priority' => 'high',
        'category' => 'ad_performance',
        'title' => 'Test',
        'issue_description' => 'Test',
        'recommended_action' => 'Test',
        'expected_impact' => 'medium',
        'effort_level' => 'low',
        'urgency' => 'this_week',
    ]);

    $this->actingAs($user);

    $component = new \App\Livewire\Intelligence\InsightsFeed();

    try {
        $component->complete($insight->id);
        $this->fail('Expected ModelNotFoundException when completing insight from another organization.');
    } catch (ModelNotFoundException $e) {
        $this->assertTrue(true);
    }

    $this->assertDatabaseHas('ai_insights', [
        'id' => $insight->id,
        'is_completed' => 0,
    ]);
}
```

- [ ] **Step 2: Fix InsightsFeed actions**

Update `app/Livewire/Intelligence/InsightsFeed.php`:
```php
public function dismiss($id)
{
    $insight = AiInsight::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
    $insight->dismiss(auth()->id());
}

public function complete($id)
{
    $insight = AiInsight::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
    $insight->complete(auth()->id());
}
```

- [ ] **Step 3: Fix AlertCenter resolve**

Update `app/Livewire/Intelligence/AlertCenter.php`:
```php
public function resolve($anomalyId)
{
    $anomaly = PerformanceAnomaly::where('organization_id', auth()->user()->organization_id)->findOrFail($anomalyId);
    $anomaly->resolve();
}
```

- [ ] **Step 4: Fix BriefingDashboard org scoping**

Update `app/Livewire/Intelligence/BriefingDashboard.php` to ensure `$id` lookup is org-protected:
```php
public function mount($id = null)
{
    $orgId = auth()->user()->organization_id;

    if ($id) {
        $this->briefing = DailyBriefing::where('organization_id', $orgId)->findOrFail($id);
        return;
    }

    $this->briefing = DailyBriefing::where('organization_id', $orgId)
        ->latest('briefing_date')
        ->first();
}
```

- [ ] **Step 5: Run test suite**

Run:
```bash
php artisan test
```

- [ ] **Step 6: Commit**

```bash
git add app/Livewire/Intelligence/InsightsFeed.php app/Livewire/Intelligence/AlertCenter.php app/Livewire/Intelligence/BriefingDashboard.php tests/Feature/Intelligence/IntelligenceTenancyTest.php
git commit -m "fix(intelligence): enforce org scoping on mutations"
```

---

## Task 4: Add Intelligence nav badges (urgent + critical)

**Files:**
- Modify: `resources/views/components/layouts/sidebar-navigation.blade.php`

- [ ] **Step 1: Update nav children labels to show badges**

In the Intelligence children list, change labels to include counts:
```php
['route' => 'intelligence.briefing', 'label' => 'Daily Briefing', 'badge' => $urgentCount ?? 0],
['route' => 'intelligence.alerts', 'label' => 'Alert Center', 'badge' => $criticalCount ?? 0],
```

Then, in the child link rendering, add:
```blade
@if(($child['badge'] ?? 0) > 0)
    <span class="ml-auto inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full text-[10px] font-bold
        bg-red-600 text-white">
        {{ $child['badge'] }}
    </span>
@endif
```

- [ ] **Step 2: Manual check**

Run app and verify:
- badges appear only when counts > 0
- no layout shift in sidebar

- [ ] **Step 3: Commit**

```bash
git add resources/views/components/layouts/sidebar-navigation.blade.php
git commit -m "feat(nav): show intelligence badges"
```

---

## Task 5: UI polish & dark-mode safety for Intelligence suite

**Files:**
- Modify: `resources/views/livewire/intelligence/overview.blade.php`
- Modify: `resources/views/livewire/intelligence/briefing-dashboard.blade.php`
- Modify: `resources/views/livewire/intelligence/insights-feed.blade.php`
- Modify: `resources/views/livewire/intelligence/alert-center.blade.php`
- Modify: `resources/views/livewire/intelligence/client-performance-center.blade.php` (either keep but link to workspace)

- [ ] **Step 1: Replace hard-coded slate palette with gray + dark: variants where missing**
Examples:
- `text-slate-900` → `text-gray-900 dark:text-gray-100`
- `bg-white` → `bg-white dark:bg-gray-900`
- borders add `dark:border-gray-800`

- [ ] **Step 2: Reuse Intelligence Blade components**
Replace ad-hoc badges with:
- `<x-intelligence.priority-badge />`
- `<x-intelligence.anomaly-badge />`
- `<x-intelligence.channel-icon />`

- [ ] **Step 3: Update “View client” links to canonical workspace route**
Anywhere linking to `intelligence.client`, update to:
```blade
route('intelligence.client.workspace', $clientId)
```
(legacy route remains but canonical used everywhere)

- [ ] **Step 4: Run UI smoke test**
Run:
```bash
php artisan serve
npm run dev
```
Click through: Overview → Workspace → Alerts → Insights → Briefing.

- [ ] **Step 5: Commit**
```bash
git add resources/views/livewire/intelligence/*.blade.php
git commit -m "style(intelligence): modern polish and dark mode safety"
```

---

## Task 6: Final validation

**Files:**
- Modify as needed based on failing tests

- [ ] **Step 1: Run formatting**
```bash
./vendor/bin/pint
```

- [ ] **Step 2: Run full test suite**
```bash
php artisan test
```

- [ ] **Step 3: Commit fixes**
```bash
git add .
git commit -m "chore(intelligence): stabilize and validate suite"
```

---

## Spec coverage self-review (mapping)
- Overview page ✅ (existing; will be polished + canonical links)
- Briefing page ✅ (existing; org-scope + optional regenerate later)
- Insights feed ✅ (existing; org-scope actions + optional channel filter later)
- Alert center ✅ (existing; org-scope resolve)
- Client workspace ✅ (new; replaces/augments ClientPerformanceCenter)
- Navigation badges ✅ (composer exists; sidebar rendering update)
