# Full UI Clean SaaS Reskin Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Reskin every app screen (including Dashboard + Intelligence) into a consistent, token-based “Clean SaaS” UI with complete states and baseline accessibility.

**Architecture:** Standardize a small set of Blade primitives and layout patterns, then refactor screens to use them. Prefer theme tokens from `tailwind.config.js` over ad-hoc palette utilities.

**Tech Stack:** Laravel 11, Livewire 3, Blade components, Alpine.js, TailwindCSS

---

## Scope Map (Grounded)

**Global layout + navigation**
- Layout: `resources/views/layouts/app.blade.php`
- Sidebar: `resources/views/components/layouts/sidebar-navigation.blade.php`
- Primitives: `resources/views/components/*`

**Major “style outlier” screens (highest drift)**
- `resources/views/livewire/campaigns/detail-view.blade.php`
- `resources/views/livewire/dashboard/index.blade.php`
- `resources/views/livewire/ads/analytics.blade.php`
- `resources/views/livewire/ads/leads.blade.php`
- `resources/views/livewire/campaigns/ad-creation-wizard.blade.php`
- `resources/views/livewire/social-planner/index.blade.php`
- `resources/views/livewire/reports/index.blade.php`
- `resources/views/livewire/feedback/index.blade.php`
- `resources/views/livewire/creative-requests/index.blade.php`
- `resources/views/livewire/orders/show.blade.php`
- `resources/views/livewire/clients/integrations.blade.php`

**Token source**
- `tailwind.config.js`

**Notes**
- `npm run build` currently fails locally due to Rollup optional dependency resolution (`@rollup/rollup-darwin-arm64`). This is an environment dependency issue; UI changes should still be verified via `php artisan test`.

---

## Task 1: Lock global layout + primitives (design system foundation)

**Files:**
- Modify: `resources/views/components/button.blade.php`
- Modify: `resources/views/components/card.blade.php`
- Modify: `resources/views/components/table.blade.php`
- Modify: `resources/views/components/page-header.blade.php`
- Modify: `resources/views/components/status-badge.blade.php`
- Create: `resources/views/components/toolbar.blade.php`
- Create: `resources/views/components/section.blade.php`
- Create: `resources/views/components/stat-card.blade.php`
- Create: `resources/views/components/badge.blade.php`
- (Already exists): `resources/views/components/empty-state.blade.php`

- [ ] **Step 1: Standardize `x-table` header semantics and density**

Goal: ensure `x-table` supports both `<x-slot name="header"> <tr>…</tr> </x-slot>` and `<x-slot name="head"> <x-table-header>…</x-table-header> … </x-slot>` while enforcing consistent typography.

Implementation (target structure in `x-table`):

```blade
<div class="overflow-x-auto overflow-y-hidden w-full">
  <table {{ $attributes->merge(['class' => 'min-w-full divide-y divide-gray-200 border-collapse']) }}>
    @if(isset($header) || isset($head))
      <thead class="bg-gray-50 text-xs font-semibold text-text-muted uppercase tracking-wider">
        @if(isset($header))
          {{ $header }}
        @else
          <tr>
            {{ $head }}
          </tr>
        @endif
      </thead>
    @endif
    <tbody class="bg-white divide-y divide-gray-200 text-sm">
      {{ $body ?? $slot }}
    </tbody>
  </table>
</div>
```

- [ ] **Step 2: Create `x-toolbar` (search/filters/actions)**

```blade
@props(['variant' => 'default'])

@php
  $variants = [
    'default' => 'bg-white border border-gray-100 rounded-card p-4',
    'subtle' => 'bg-gray-50 border border-gray-100 rounded-card p-4',
  ];
  $classes = $variants[$variant] ?? $variants['default'];
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
      {{ $left ?? '' }}
    </div>
    <div class="flex items-center gap-3 justify-end">
      {{ $right ?? '' }}
    </div>
  </div>
</div>
```

- [ ] **Step 3: Create `x-section` (consistent section headers)**

```blade
@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'flex items-start justify-between gap-4']) }}>
  <div class="min-w-0">
    <h2 class="text-sm font-semibold text-text-primary">{{ $title }}</h2>
    @if($description)
      <p class="mt-1 text-sm text-text-muted">{{ $description }}</p>
    @endif
  </div>
  @if(isset($actions) && $actions->isNotEmpty())
    <div class="flex items-center gap-3 flex-shrink-0">
      {{ $actions }}
    </div>
  @endif
</div>
```

- [ ] **Step 4: Create `x-stat-card` (dash metric tiles)**

```blade
@props(['label', 'value', 'trend' => null])

<x-card {{ $attributes->merge(['class' => 'p-6']) }}>
  <p class="text-xs font-semibold text-text-muted uppercase tracking-wider">{{ $label }}</p>
  <div class="mt-2 flex items-baseline justify-between gap-3">
    <p class="text-2xl font-semibold text-text-primary">{{ $value }}</p>
    @if($trend)
      <p class="text-xs font-semibold text-text-muted">{{ $trend }}</p>
    @endif
  </div>
  @if(isset($slot) && $slot->isNotEmpty())
    <div class="mt-4">
      {{ $slot }}
    </div>
  @endif
</x-card>
```

- [ ] **Step 5: Create `x-badge` (non-status pills)**

```blade
@props(['variant' => 'neutral', 'size' => 'sm'])

@php
  $sizes = [
    'sm' => 'px-2.5 py-1 text-xs',
    'xs' => 'px-2 py-0.5 text-[10px]',
  ];
  $variants = [
    'neutral' => 'bg-gray-100 text-gray-700',
    'primary' => 'bg-primary-soft text-primary',
    'success' => 'bg-success-soft text-success',
    'warning' => 'bg-warning-soft text-warning',
    'danger' => 'bg-danger-soft text-danger',
    'info' => 'bg-info-soft text-info',
  ];
  $classes = ($sizes[$size] ?? $sizes['sm']).' '.($variants[$variant] ?? $variants['neutral']);
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full font-semibold $classes"]) }}>
  {{ $slot }}
</span>
```

- [ ] **Step 6: Run tests**

Run: `php artisan test`  
Expected: PASS (deprecation noise allowed)

---

## Task 2: Refactor Dashboard to Clean SaaS

**Files:**
- Modify: `resources/views/livewire/dashboard/index.blade.php`

- [ ] **Step 1: Replace hero/novelty tiles with `x-stat-card`**

Example transformation:

```blade
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
  <x-stat-card label="Revenue" :value="'$'.number_format($revenue_matrix['total_paid'] / 100, 2)" :trend="number_format($revenue_matrix['pending'] / 100, 2).' pending'" />
  <x-stat-card label="Leads" :value="$lead_flux['total']" :trend="'+'.$lead_flux['new_today'].' today'" />
  <x-stat-card label="Creative Requests" :value="$creative_nodes['pending']" :trend="$creative_nodes['urgent'].' urgent'" />
  <x-stat-card label="Automation Runs" :value="$automation_pulse['total_runs']" />
</div>
```

- [ ] **Step 2: Normalize section headers using `x-section` and remove excess uppercase/animations**

Example:

```blade
<x-section title="Automation Activity" description="Recent workflow runs across your organization">
  <x-slot name="actions">
    <x-button variant="outline" href="{{ route('workflow.logs') }}" wire:navigate>View Logs</x-button>
  </x-slot>
</x-section>
```

- [ ] **Step 3: Ensure empty states exist for each dynamic list section**

Use `x-empty-state` for:
- Recent runs
- Recent projects
- Morning briefing preview (already has fallback)
- Anomalies (already has fallback)

- [ ] **Step 4: Run tests**

Run: `php artisan test`  
Expected: PASS

---

## Task 3: Normalize list screens (search/filter/toolbars, tables, empty/loading)

**Files (initial pass):**
- Modify: `resources/views/livewire/clients/index.blade.php`
- Modify: `resources/views/livewire/orders/index.blade.php`
- Modify: `resources/views/livewire/contacts/index.blade.php`
- Modify: `resources/views/livewire/workflow-monitoring/logs.blade.php`
- Modify: `resources/views/livewire/ads/leads.blade.php` (convert to `x-table`)

- [ ] **Step 1: Introduce `x-toolbar` on each list**

Example:

```blade
<x-toolbar class="mb-6">
  <x-slot name="left">
    <x-input wire:model.live="search" type="search" placeholder="Search…" aria-label="Search" class="w-full sm:w-80" />
    <x-select wire:model.live="status" class="w-full sm:w-56">…</x-select>
  </x-slot>
  <x-slot name="right">
    <x-button href="{{ route('orders.create') }}" wire:navigate>New Order</x-button>
  </x-slot>
</x-toolbar>
```

- [ ] **Step 2: Ensure consistent table headers and empty row colspans**

Use `x-empty-state` inside the empty `<tr>` and ensure `colspan` equals header count.

- [ ] **Step 3: Add lightweight loading indicator per list**

```blade
<div wire:loading class="mb-4 text-sm text-text-muted">Loading…</div>
```

- [ ] **Step 4: Run tests**

Run: `php artisan test`  
Expected: PASS

---

## Task 4: Normalize kanban boards (Campaigns/Leads/Tasks/Creatives)

**Files:**
- Modify: `resources/views/livewire/tasks/kanban-board.blade.php`
- Modify: `resources/views/livewire/leads/kanban-board.blade.php`
- Modify: `resources/views/livewire/campaigns/kanban-board.blade.php`
- Modify: `resources/views/livewire/creatives/requests-board.blade.php`

- [ ] **Step 1: Replace “Empty Stage” and similar copy with `x-empty-state`**

Example:

```blade
<x-empty-state class="py-10" title="No items" description="Create a new item or drag one into this stage." />
```

- [ ] **Step 2: Ensure drag/drop uses `this.$wire.*` (not `@this.*`)**

Example:

```js
this.$wire.updateTaskStatus(this.draggedTaskId, newStatus)
```

- [ ] **Step 3: Reduce overly aggressive hover effects (scale, heavy shadows)**

Goal: keep subtle hover (shadow-sm → shadow-md) and avoid constant “neon” behaviors.

- [ ] **Step 4: Run tests**

Run: `php artisan test`  
Expected: PASS

---

## Task 5: Reskin high-drift detail + wizard flows

**Files (prioritized):**
- Modify: `resources/views/livewire/campaigns/detail-view.blade.php`
- Modify: `resources/views/livewire/campaigns/ad-creation-wizard.blade.php`
- Modify: `resources/views/livewire/orders/show.blade.php`
- Modify: `resources/views/livewire/projects/detail-view.blade.php`
- Modify: `resources/views/livewire/tasks/detail-view.blade.php`

- [ ] **Step 1: Convert page layout to predictable grid + cards**
- [ ] **Step 2: Replace ad-hoc pills with `x-badge` and statuses with `x-status-badge`**
- [ ] **Step 3: Ensure primary/secondary actions use `x-button` consistently**
- [ ] **Step 4: Ensure error/success notices use `x-alert`**
- [ ] **Step 5: Run tests**

Run: `php artisan test`  
Expected: PASS

---

## Task 6: Reskin Intelligence + Ads Analytics + Reports + Social Planner

**Files:**
- Modify: `resources/views/livewire/intelligence/*.blade.php`
- Modify: `resources/views/livewire/ads/analytics.blade.php`
- Modify: `resources/views/livewire/reports/index.blade.php`
- Modify: `resources/views/livewire/reports/dashboard.blade.php`
- Modify: `resources/views/livewire/social-planner/index.blade.php`
- Modify: `resources/views/livewire/analytics/index.blade.php`
- Modify: `resources/views/livewire/analytics-management/dashboard.blade.php`

- [ ] **Step 1: Normalize typography and remove “command center” gimmicks**
  - Reduce `font-black` to `font-semibold` except key metrics.
  - Reduce pervasive uppercase and wide tracking.
  - Replace gradients/animated pulses with token-based accents.
- [ ] **Step 2: Ensure each analytics/report section has empty/loading**
- [ ] **Step 3: Run tests**

Run: `php artisan test`  
Expected: PASS

---

## Task 7: A11y sweep + drift sweep

**Files:**
- Modify: targeted Blade templates across `resources/views/**/*.blade.php`

- [ ] **Step 1: Icon-only buttons all have `aria-label`**
- [ ] **Step 2: Search inputs all have `aria-label` (or visible `<label>`)**
- [ ] **Step 3: Clickable rows are links**
- [ ] **Step 4: Replace raw alert divs with `x-alert`**
- [ ] **Step 5: Run tests**

Run: `php artisan test`  
Expected: PASS

---

## Execution Notes
- Do not commit unless explicitly requested.
- Prefer small, verifiable steps (change a component, refactor 1–3 screens, re-run `php artisan test`).
- Frontend build validation is blocked by local npm optional dependency issue; keep changes compatible with Tailwind/Vite but rely on PHP tests for verification here.

