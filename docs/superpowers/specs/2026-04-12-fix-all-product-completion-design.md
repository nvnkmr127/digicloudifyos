# Fix All: Product Completion Design
Date: 2026-04-12

## Goal
Resolve all confirmed broken connections and incomplete user journeys, then complete missing CRUD flows and major product gaps so the application behaves like a production-ready system.

## Scope
This spec covers:
- Fixing broken routes/links, schema mismatches, and runtime breakages.
- Completing CRUD for routed modules that currently lack show/edit/update/delete flows.
- Implementing Forms “publish + public URL + embed + submissions” end-to-end.
- Implementing Reports PDF export and replacing visible placeholders with minimal functional equivalents.
- Making destructive-action confirmations real and consistent.
- Ensuring the test suite passes with added coverage for new flows.

## Non-Goals
- Re-architecting the entire application or introducing new major subsystems beyond the missing journeys already implied by existing UI.
- Adding new third-party frontend frameworks or heavy JS charting libraries.
- Changing design system primitives unless required to support correct behavior/accessibility.

## Current Known Gaps (Baseline)
- Navigation link to Broadcasts exists but no route exposes it.
- `wire:confirm` appears across the UI but likely does nothing.
- Social Planner modal close events use mismatched names.
- Creative Requests creation is misaligned with DB schema (required fields + deadline naming).
- Campaign detail view references a task field that doesn’t exist (`due_date` vs `deadline`).
- Campaign detail performance tab loads one relation but renders another.
- Calendar queries non-existent columns (`due_at`, `deadline` on projects) and uses dynamic Tailwind classes.
- Leads create defaults to invalid enum value (`New` vs `new`).
- Proposals “view” button is a no-op and there’s no show/edit.
- Products have an Edit component but no edit route or UI action.
- Reports PDF export is a no-op and charts section is placeholder.
- Forms have stub/no-op UI actions and no publish/embed/submission loop.

## Approach (Phased)

### Block 0 — Stop-the-Bleeding Fixes (Hard Breakages)
Deliverable: all currently known runtime errors/no-op/404 items removed.

1) Broadcasts routing + nav alignment
- Add `broadcasts.index` route and ensure it uses standard auth + org middleware.
- Add sidebar entry (or remove top-nav entry) to avoid drift.

2) Calendar correctness + styling reliability
- Fix column usage:
  - Tasks: use `deadline` (not `due_at`).
  - Projects: use `end_date` (or `start_date/end_date` range display), not `deadline`.
- Ensure all date fields are null-safe before calling `->format()`.
- Replace dynamic Tailwind class generation (e.g., `bg-{{ $color }}-50`) with a small fixed variant map.

3) Creative Requests creation aligned with schema
- Require/collect: `client_id`, `campaign_id`, `type`.
- Use schema-aligned deadline field (`deadline`) and cast type correctly.
- Ensure validation rules match DB constraints and UX.

4) Campaign detail view consistency
- Replace `$task->due_date` usage with the correct field (`deadline`).
- Align deferred loading to the rendered relations in the performance tab (load what the Blade uses).
- Replace “Preview” no-op with a concrete behavior (modal or external link) based on available data.

5) Leads create default correctness
- Set default status to `'new'` (or ensure UI always sets a valid enum value before save).

### Block 1 — Navigation + Action Integrity
Deliverable: no dead links, no misleading “confirmations”, consistent feedback after actions.

1) Auto-login link behavior
- Remove from production UI or gate it behind a dev-only condition so it cannot 404.
- Ensure the UX doesn’t present unusable controls for authenticated users.

2) Confirmation UX
- Implement a real confirmation mechanism for destructive actions:
  - Option A: a global Livewire/JS hook that reads `wire:confirm` and prompts before invoking.
  - Option B: a standardized confirm modal component and replace `wire:confirm` usage.
- Standardize usage across modules (delete actions in tasks, campaigns, products, proposals, etc.).

3) Flash messaging contract
- Standardize on `success` and `error` flash keys.
- Ensure layouts and key screens render those keys consistently.

### Block 2 — CRUD Completion for Routed Modules
Deliverable: routed modules support complete lifecycle (or explicitly document constraints).

1) Products
- Add `products.show` (optional) and `products.edit` routes.
- Add index actions for View/Edit.
- Ensure Update flow persists and shows success feedback.
- Ensure Delete uses confirmation and respects org scoping.

2) Proposals
- Add `proposals.show` and `proposals.edit` routes.
- Wire the “view” icon to navigate.
- Implement “draft → send/share → status tracking” (see Block 4 if needed).

3) Orders
- Add `orders.edit` route and update flow (line items + status rules).
- Enforce state rules (editable only in draft/pending states).

4) Opportunities
- Add `opportunities.index` (or explicitly treat pipelines as canonical list with clear entry points).
- Add edit/update for stage + core fields; delete/archive rules per business logic.

### Block 3 — Forms: Public URL + Embed + Submissions (End-to-End)
Deliverable: build form → publish → embed/public URL → collect submissions → view submissions in-app.

#### Data Model
- Extend `forms` to support publishing:
  - `slug` (unique per org, human-readable)
  - `is_published` boolean
  - `public_key` (random secret used for embed/submission validation)
  - optional: `thank_you_message`, `redirect_url`
- Ensure `form_submissions` stores:
  - `organization_id`, `form_id`
  - `payload` JSON (submitted fields)
  - metadata: IP, user agent, referer, submitted_at

#### Public Rendering + Submission
- Public page: `GET /f/{slug}`
  - Render minimal layout (fast, responsive, accessible).
  - Render fields from `forms.fields` JSON config.
- Submission: `POST /f/{slug}/submit`
  - Validate `public_key` (or a signed token) to reduce random spam.
  - Validate required fields based on config.
  - Store `form_submissions`.

#### Embed
- Provide an embed snippet in-app:
  - iframe option: `<iframe src=".../f/{slug}?k=...">`
  - script option (optional): embed script that injects iframe

#### In-app Management
- Forms index actions:
  - View (form details)
  - Embed (copy snippet)
  - Submissions (list + export)
- Forms show:
  - publish toggle, slug editor, field editor, preview link
- Submissions viewer:
  - list table with search and date filters
  - view payload detail
  - export CSV

#### Abuse Controls
- Rate limit public submission route.
- Honeypot field and basic bot heuristics.
- Optional reCAPTCHA is out of scope unless already wired.

### Block 4 — Reports Completion
Deliverable: no visible placeholders; export works.

1) PDF Export
- Wire “Export PDF” to generate a PDF using `barryvdh/laravel-dompdf`.
- Output should match the selected date range and include the summary metrics.

2) Charts replacement
- Replace “Charts coming soon” with minimal functional charts:
  - Server-rendered sparkline/bar charts using existing Blade patterns.
  - No new JS libraries unless already present.

## Security / Authorization Requirements
- All org-scoped data must remain tenant-isolated:
  - all CRUD operations must scope by `organization_id`.
  - public forms must never allow cross-org submission.
- Destructive actions must require authorization checks consistent with existing gates/policies.
- Public form submission must be protected against random writes (public_key/token + throttling).

## UX / Accessibility Requirements
- No icon-only action without `aria-label`.
- Loading/disabled states for long-running actions (exports, sends).
- Confirmations must be consistent and non-skippable for destructive actions.

## Testing Plan
Add/extend tests to cover:
- Route reachability for menu items (no dead links in production config).
- Calendar rendering does not throw and produces expected items for seeded data.
- Creative Requests creation succeeds and enforces required fields.
- Leads default status passes validation.
- Products edit route + update + delete.
- Proposals show/edit + delete.
- Forms publish + public render + submit + submissions viewer + CSV export.
- Reports PDF export route/action returns a PDF response and includes key metrics.

## Rollout Notes
- Prefer small, reviewable commits per block (even if executed sequentially).
- Keep UI consistent with existing primitives and tokens.
- Ensure all migrations are backward-safe (non-breaking defaults for new columns).

