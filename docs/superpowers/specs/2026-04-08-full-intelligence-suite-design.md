# Full Intelligence Suite (MVP) — Design

Date: 2026-04-08  
Product: DigiCloudify OS (Laravel 11 + Livewire 3 + Tailwind)  
Audience: Agency owners, strategists, media buyers, account managers

## 1. Problem & goal
Marketing agencies managing many clients across paid + organic + analytics spend too much time:
- hunting for issues in raw dashboards
- deciding what matters today
- turning data into actionable next steps

**Goal:** deliver a centralized Intelligence experience that, for every client, produces:
- daily monitoring of performance + anomalies
- AI-generated, prioritized recommendations
- a morning briefing action plan
- a per-client workspace for fast decisions

## 2. MVP scope (what we will deliver)
MVP is the complete **Intelligence Suite**: 5 pages + navigation integration, using existing DB tables and services already in this codebase where available.

### 2.1 Pages / routes
All routes live under `auth + verified + organization context` middleware.

1) **Overview**
- Route: `GET /intelligence` (`intelligence.overview`)
- Purpose: cross-client snapshot of health + what needs attention now
- Outputs:
  - Client health grid grouped into: Healthy / Needs Attention / Critical
  - Aggregate KPIs (yesterday): spend, leads, avg ROAS, active alerts
  - “Top alerts” list (severity-sorted) with drill-down

2) **Morning Briefing**
- Route: `GET /intelligence/briefing` (`intelligence.briefing`)
- Purpose: today’s focused action plan (what to do next)
- Outputs:
  - Sections: Urgent / Important / Opportunities
  - Completion tracking per item
  - Regenerate / Run Now action (owner/admin only) if pipeline supports it

3) **AI Insights Feed**
- Route: `GET /intelligence/insights` (`intelligence.insights`)
- Purpose: backlog of actionable insights across org
- Outputs:
  - Filters: status (active/completed/dismissed), priority, client, channel
  - Insight card: title, issue, recommended action, impact, effort, urgency
  - Actions: Dismiss / Complete

4) **Alert Center**
- Route: `GET /intelligence/alerts` (`intelligence.alerts`)
- Purpose: anomalies that need attention (pre-AI or alongside AI)
- Outputs:
  - Severity summary (critical/high/medium/low)
  - Unresolved anomaly lists grouped by severity
  - Actions: Resolve, View Client Workspace

5) **Client Workspace**
- Route: `GET /intelligence/clients/{client}/workspace` (`intelligence.client.workspace`)
- Purpose: single cockpit for one client, across channels
- Outputs:
  - Health score (current + trend)
  - Connected accounts list (channels + last sync)
  - Channel KPI cards (ad + organic)
  - Recent snapshots and baseline comparisons
  - Active anomalies + active AI insights

### 2.2 Navigation integration
- Add an “Intelligence” group to the sidebar with links to:
  - Performance Intel (Overview)
  - Morning Briefing (with urgent count badge)
  - AI Insights
  - Alert Center (with critical count badge)
- Provide badge counts via a View Composer with short caching (per org).

## 3. Data model assumptions (MVP)
MVP reads from persisted tables (not live API calls per request). The suite expects these conceptual entities:
- **PerformanceSnapshot**: per client + channel + date aggregated metrics + baselines
- **PerformanceAnomaly**: detected deviations (severity, type, metric, baseline vs current)
- **AiInsight**: AI output that translates anomalies/opportunities into actionable recommendations
- **DailyBriefing** + **BriefingActionItem**: curated morning action plan
- **ClientChannelConnection**: connected external accounts per client (ad/analytics/social)

Where the tables/models already exist in the repository, we use them and align UI queries to them.

## 4. Daily pipeline assumptions (MVP)
MVP is designed around a daily pipeline that can be scheduled and/or triggered manually:
1) Fetch/aggregate yesterday’s metrics into snapshots
2) Compute baselines and detect anomalies
3) Generate AI insights and opportunities
4) Generate a daily briefing
5) (Optional) email the briefing

UI supports “generating → ready” states (e.g., briefing regeneration).

## 5. UX & UI design (modern + stylish)
### 5.1 Visual style principles
- **Clarity first**: readable density; avoid overly “cardy” layouts
- **Strong hierarchy**: large section headers, small meta text, consistent spacing scale
- **Badges**: color-coded severity/priority/urgency pills (consistent across pages)
- **Dark-mode safe**: no hard-coded light-only colors; use Tailwind neutral palette consistently
- **Micro-interactions**: subtle hover/active states, transitions, empty states, skeleton loaders where helpful

### 5.2 Reusable primitives (prefer Blade components)
Follow `UI_COMPONENTS.md`:
- Use `<x-button variant="...">`, `<x-input>`, `<x-modal>` etc.
- Add Intelligence-specific components only when reusable across multiple screens:
  - `health-score-ring`
  - `priority-badge`
  - `channel-icon`
  - `anomaly-badge`

## 6. Access control & multi-tenancy
- All queries are organization-scoped.
- Client workspace uses route model binding + org authorization guard.
- “Run Now / Regenerate” actions restricted to owner/admin (policy or explicit checks).

## 7. Performance & reliability requirements
- Paginate feeds (insights, alerts) and eager-load relationships.
- Cache sidebar badge counts per org (short TTL).
- Avoid N+1 queries on overview grids.
- Fail “softly”: if AI is unavailable, show anomalies and fallback messaging.

## 8. Acceptance criteria (MVP)
### Overview
- Shows grouped client health tiles with counts and a drill-down link to each workspace.
- Shows org-level KPIs and top active alerts.

### Briefing
- Shows today’s (or most recent) briefing; supports generating state.
- Items can be marked done; progress updates in UI.

### Insights Feed
- Paginated insights; filters work; dismiss/complete works and persists.

### Alert Center
- Lists unresolved anomalies grouped by severity; resolve works and persists.

### Client Workspace
- Shows connected channels, health score, snapshots, anomalies, and insights.
- Date-range switching updates metrics without page reload (Livewire).

### Navigation
- Sidebar includes Intelligence group and badges update (cached).

## 9. Out of scope (for this MVP)
- Real-time streaming/near-real-time monitoring (sub-daily) across all clients
- Advanced charting suite (beyond a few essential trend charts)
- Client-facing portal views (external users)
- Billing, invoicing automation, or contract management changes

## 10. Risks & mitigations
- **Data sparsity early-on**: seed/test-data command + robust empty states.
- **Slow pages as data grows**: indexes, pagination, caching, and query profiling.
- **AI variability**: strict JSON prompting + validation + fallback insights.

## 11. Implementation notes (non-binding)
Prefer incremental delivery:
- Wire routes + nav + empty states first
- Then implement queries and interactions per page
- Then polish UI + dark mode
- Then run app checks and fix issues discovered by real usage

