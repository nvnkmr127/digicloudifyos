# 🧩 DC OS — Performance Intelligence System
## Complete Microtask Registry

> **Stack**: Laravel 11 · Livewire 3 · Tailwind CSS · Alpine.js · Horizon · Guzzle · Spatie Permissions
> **AI**: Gemini API (primary) · OpenAI GPT-4 (fallback)
> **Pattern**: All new models use `HasUuids` + `OrganizationScoped` trait — same as existing codebase

---

## Legend

| Symbol | Meaning |
|--------|---------|
| `[ ]` | Not started |
| `[x]` | Complete |
| `🔴` | Blocker — must be done before anything in phase can proceed |
| `🟡` | Depends on another task in this phase |
| `🟢` | Independent — can be done in any order |
| `⚡` | Quick win (< 30 min) |
| `🤖` | Involves AI API call |

---

## PHASE 1 — Foundation: Models & Migrations
> **Goal**: All new tables exist in the DB and all new models are wired up.
> **Estimated time**: 1 day

---

### 1.0 — Pre-flight Checks

- [x] `🔴⚡` **[TASK-001]** Add new environment variables to `.env` and `.env.example`:
  ```env
  AI_PROVIDER=gemini
  GEMINI_API_KEY=
  OPENAI_API_KEY=
  PERF_CTR_DROP_THRESHOLD=20
  PERF_CPC_SPIKE_THRESHOLD=30
  PERF_ROAS_MIN_THRESHOLD=1.5
  PERF_ENGAGEMENT_DROP_THRESHOLD=25
  BRIEFING_EMAIL_ENABLED=true
  BRIEFING_SEND_TIME=07:00
  ```

- [x] `🔴⚡` **[TASK-002]** Create `config/intelligence.php` config file:
  ```php
  return [
      'ai_provider'   => env('AI_PROVIDER', 'gemini'),
      'gemini_key'    => env('GEMINI_API_KEY'),
      'openai_key'    => env('OPENAI_API_KEY'),
      'thresholds'    => [
          'ctr_drop'         => env('PERF_CTR_DROP_THRESHOLD', 20),
          'cpc_spike'        => env('PERF_CPC_SPIKE_THRESHOLD', 30),
          'roas_min'         => env('PERF_ROAS_MIN_THRESHOLD', 1.5),
          'engagement_drop'  => env('PERF_ENGAGEMENT_DROP_THRESHOLD', 25),
          'lead_drop'        => 30,
          'budget_overrun'   => 10,
          'budget_underpace' => 40,
      ],
      'briefing_email_enabled' => env('BRIEFING_EMAIL_ENABLED', true),
      'briefing_send_time'     => env('BRIEFING_SEND_TIME', '07:00'),
  ];
  ```

---

### 1.1 — Database Migrations (7 migrations)

- [x] `🔴` **[TASK-003]** Create migration: `create_client_channel_connections_table`
  ```
  php artisan make:migration create_client_channel_connections_table
  ```
  Columns:
  - `id` UUID primary key
  - `organization_id` UUID (foreign → organizations)
  - `client_id` UUID (foreign → clients)
  - `channel_type` ENUM: `meta_ads`, `google_ads`, `linkedin_ads`, `ga4`, `instagram`, `facebook_organic`, `inbound_webhook`
  - `account_id` VARCHAR(255) nullable — external platform ID
  - `account_name` VARCHAR(255) nullable
  - `is_active` BOOLEAN default true
  - `connected_at` TIMESTAMP nullable
  - `last_synced_at` TIMESTAMP nullable
  - `metadata` JSON nullable
  - `timestamps()`
  - Index: `[organization_id, client_id, channel_type]`
  - Index: `[organization_id, is_active]`

- [x] `🔴` **[TASK-004]** Create migration: `create_performance_snapshots_table`
  ```
  php artisan make:migration create_performance_snapshots_table
  ```
  Columns:
  - `id` UUID primary key
  - `organization_id` UUID
  - `client_id` UUID
  - `channel_type` VARCHAR(100)
  - `snapshot_date` DATE
  - `impressions` BIGINT default 0
  - `clicks` BIGINT default 0
  - `spend` DECIMAL(14,4) default 0
  - `conversions` INTEGER default 0
  - `revenue` DECIMAL(14,4) default 0
  - `ctr` DECIMAL(10,6) nullable
  - `cpc` DECIMAL(10,4) nullable
  - `cpm` DECIMAL(10,4) nullable
  - `roas` DECIMAL(10,4) nullable
  - `reach` BIGINT default 0
  - `engagement_rate` DECIMAL(10,6) nullable
  - `leads` INTEGER default 0
  - `cost_per_lead` DECIMAL(10,4) nullable
  - `baseline_ctr` DECIMAL(10,6) nullable — 7-day rolling avg
  - `baseline_cpc` DECIMAL(10,4) nullable — 7-day rolling avg
  - `baseline_roas` DECIMAL(10,4) nullable — 7-day rolling avg
  - `baseline_leads` DECIMAL(10,2) nullable — 7-day rolling avg
  - `anomaly_flags` JSON nullable — array of anomaly type strings
  - `raw_data` JSON nullable
  - `timestamps()`
  - Unique: `[organization_id, client_id, channel_type, snapshot_date]`
  - Index: `[client_id, snapshot_date]`

- [x] `🔴` **[TASK-005]** Create migration: `create_performance_anomalies_table`
  ```
  php artisan make:migration create_performance_anomalies_table
  ```
  Columns:
  - `id` UUID primary key
  - `organization_id` UUID
  - `client_id` UUID
  - `snapshot_id` UUID nullable (foreign → performance_snapshots)
  - `anomaly_type` VARCHAR(100) — e.g. `ctr_drop`, `cpc_spike`, `roas_decline`
  - `channel_type` VARCHAR(100)
  - `metric_name` VARCHAR(100)
  - `current_value` DECIMAL(14,6)
  - `baseline_value` DECIMAL(14,6)
  - `deviation_percentage` DECIMAL(8,2)
  - `severity` ENUM: `critical`, `high`, `medium`, `low`
  - `detected_at` TIMESTAMP
  - `resolved_at` TIMESTAMP nullable
  - `context` JSON nullable — extra data for AI prompt
  - `timestamps()`
  - Index: `[organization_id, client_id, detected_at]`
  - Index: `[organization_id, severity, resolved_at]`

- [x] `🔴` **[TASK-006]** Create migration: `create_client_health_scores_table`
  ```
  php artisan make:migration create_client_health_scores_table
  ```
  Columns:
  - `id` UUID primary key
  - `organization_id` UUID
  - `client_id` UUID
  - `score_date` DATE
  - `overall_score` TINYINT UNSIGNED — 0 to 100
  - `ad_performance_score` TINYINT UNSIGNED nullable
  - `organic_score` TINYINT UNSIGNED nullable
  - `conversion_score` TINYINT UNSIGNED nullable
  - `budget_efficiency_score` TINYINT UNSIGNED nullable
  - `score_breakdown` JSON nullable — full calculation context
  - `trend` ENUM: `improving`, `stable`, `declining`
  - `timestamps()`
  - Unique: `[client_id, score_date]`
  - Index: `[organization_id, score_date]`

- [x] `🔴` **[TASK-007]** Create migration: `create_ai_insights_table`
  ```
  php artisan make:migration create_ai_insights_table
  ```
  Columns:
  - `id` UUID primary key
  - `organization_id` UUID
  - `client_id` UUID
  - `anomaly_id` UUID nullable (foreign → performance_anomalies)
  - `channel_type` VARCHAR(100) nullable
  - `insight_date` DATE
  - `priority` ENUM: `critical`, `high`, `medium`, `low`, `opportunity`
  - `category` ENUM: `ad_performance`, `budget`, `organic`, `conversion`, `opportunity`
  - `title` VARCHAR(255)
  - `issue_description` TEXT
  - `root_cause` TEXT nullable
  - `recommended_action` TEXT
  - `expected_impact` TEXT nullable
  - `effort_level` ENUM: `low`, `medium`, `high`
  - `urgency` ENUM: `today`, `this_week`, `next_week`
  - `is_dismissed` BOOLEAN default false
  - `dismissed_at` TIMESTAMP nullable
  - `dismissed_by` UUID nullable
  - `is_completed` BOOLEAN default false
  - `completed_at` TIMESTAMP nullable
  - `completed_by` UUID nullable
  - `raw_ai_response` JSON nullable
  - `timestamps()`
  - Index: `[organization_id, client_id, insight_date]`
  - Index: `[organization_id, is_dismissed, is_completed, priority]`

- [x] `🔴` **[TASK-008]** Create migration: `create_daily_briefings_table`
  ```
  php artisan make:migration create_daily_briefings_table
  ```
  Columns:
  - `id` UUID primary key
  - `organization_id` UUID
  - `briefing_date` DATE
  - `status` ENUM: `generating`, `ready`, `sent` — default `generating`
  - `total_clients_analyzed` INTEGER default 0
  - `critical_alerts_count` INTEGER default 0
  - `opportunities_count` INTEGER default 0
  - `summary` JSON nullable — pre-rendered summary object
  - `generated_at` TIMESTAMP nullable
  - `sent_at` TIMESTAMP nullable
  - `timestamps()`
  - Unique: `[organization_id, briefing_date]`

- [x] `🔴` **[TASK-009]** Create migration: `create_briefing_action_items_table`
  ```
  php artisan make:migration create_briefing_action_items_table
  ```
  Columns:
  - `id` UUID primary key
  - `briefing_id` UUID (foreign → daily_briefings)
  - `client_id` UUID
  - `ai_insight_id` UUID nullable (foreign → ai_insights)
  - `sort_order` INTEGER default 0
  - `priority_level` ENUM: `urgent`, `important`, `opportunity`
  - `title` VARCHAR(255)
  - `description` TEXT nullable
  - `action` TEXT
  - `expected_impact` TEXT nullable
  - `effort` VARCHAR(50) nullable — `low`, `medium`, `high`
  - `is_completed` BOOLEAN default false
  - `completed_at` TIMESTAMP nullable
  - `completed_by` UUID nullable
  - `timestamps()`
  - Index: `[briefing_id, sort_order]`
  - Index: `[briefing_id, is_completed]`

- [x] `🟡` **[TASK-010]** Run all new migrations
  ```bash
  php artisan migrate
  ```
  Verify all 7 tables created with correct columns.

---

### 1.2 — New Models (7 models)

All models follow the existing pattern: `HasUuids` + `OrganizationScoped` trait.

- [x] `🟡` **[TASK-011]** Create `app/Models/ClientChannelConnection.php`
  - `HasUuids`, `OrganizationScoped`
  - `$fillable`: all columns
  - `$casts`: `is_active` → boolean, `connected_at` → datetime, `last_synced_at` → datetime, `metadata` → array
  - Relations: `belongsTo(Client::class)`, `belongsTo(Organization::class)`
  - Scopes: `scopeActive()`, `scopeForChannel($type)`, `scopeForClient($clientId)`

- [x] `🟡` **[TASK-012]** Create `app/Models/PerformanceSnapshot.php`
  - `HasUuids`, `OrganizationScoped`
  - `$fillable`: all columns
  - `$casts`: `snapshot_date` → date, all metric columns → decimal, `anomaly_flags` → array, `raw_data` → array
  - Relations: `belongsTo(Client::class)`, `belongsTo(Organization::class)`, `hasMany(PerformanceAnomaly::class)`
  - Scopes: `scopeForDateRange($start, $end)`, `scopeForChannel($type)`, `scopeForClient($clientId)`
  - Method: `getCtrChangePercent()` — compares `ctr` vs `baseline_ctr`
  - Method: `getCpcChangePercent()` — compares `cpc` vs `baseline_cpc`
  - Method: `getRoasChangePercent()` — compares `roas` vs `baseline_roas`
  - Method: `hasAnomalies()` — returns `!empty($this->anomaly_flags)`

- [x] `🟡` **[TASK-013]** Create `app/Models/PerformanceAnomaly.php`
  - `HasUuids`, `OrganizationScoped`
  - `$fillable`: all columns
  - `$casts`: `detected_at` → datetime, `resolved_at` → datetime, `context` → array, `deviation_percentage` → decimal
  - Relations: `belongsTo(Client::class)`, `belongsTo(PerformanceSnapshot::class, 'snapshot_id')`, `hasOne(AiInsight::class, 'anomaly_id')`
  - Scopes: `scopeUnresolved()`, `scopeBySeverity($severity)`, `scopeForClient($clientId)`
  - Method: `isResolved()`, `resolve()` — sets `resolved_at = now()`
  - Method: `getDeviationDescription()` — human-readable deviation string

- [x] `🟡` **[TASK-014]** Create `app/Models/ClientHealthScore.php`
  - `HasUuids`, `OrganizationScoped`
  - `$fillable`: all columns
  - `$casts`: `score_date` → date, all score columns → integer, `score_breakdown` → array
  - Relations: `belongsTo(Client::class)`
  - Method: `getScoreColor()` — returns `green`/`yellow`/`red` based on overall_score
  - Method: `getScoreLabel()` — returns `Healthy`/`Needs Attention`/`Critical`
  - Method: `getScoreBadgeClass()` — Tailwind CSS class string for badge color

- [x] `🟡` **[TASK-015]** Create `app/Models/AiInsight.php`
  - `HasUuids`, `OrganizationScoped`
  - `$fillable`: all columns
  - `$casts`: `insight_date` → date, all boolean flags, `dismissed_at`/`completed_at` → datetime, `raw_ai_response` → array
  - Relations: `belongsTo(Client::class)`, `belongsTo(PerformanceAnomaly::class, 'anomaly_id')`, `hasMany(BriefingActionItem::class, 'ai_insight_id')`
  - Scopes: `scopeActive()` — not dismissed + not completed, `scopeByPriority()`, `scopeForToday()`, `scopeOpportunities()`
  - Method: `dismiss($userId)`, `complete($userId)`, `getPriorityColor()`, `getPriorityIcon()`

- [x] `🟡` **[TASK-016]** Create `app/Models/DailyBriefing.php`
  - `HasUuids`, `OrganizationScoped`
  - `$fillable`: all columns
  - `$casts`: `briefing_date` → date, `summary` → array, `generated_at`/`sent_at` → datetime
  - Relations: `belongsTo(Organization::class)`, `hasMany(BriefingActionItem::class, 'briefing_id')`
  - Scopes: `scopeForDate($date)`, `scopeReady()`
  - Method: `isReady()`, `markSent()`, `getUrgentItems()`, `getImportantItems()`, `getOpportunities()`

- [x] `🟡` **[TASK-017]** Create `app/Models/BriefingActionItem.php`
  - `HasUuids`
  - `$fillable`: all columns
  - `$casts`: `is_completed` → boolean, `completed_at` → datetime
  - Relations: `belongsTo(DailyBriefing::class, 'briefing_id')`, `belongsTo(Client::class)`, `belongsTo(AiInsight::class, 'ai_insight_id')`
  - Scopes: `scopePending()`, `scopeByPriority()`
  - Method: `complete($userId)`, `getPriorityBadgeClass()`

---

### 1.3 — Update Existing Client Model

- [x] `🟡` **[TASK-018]** Add new relationships and methods to `app/Models/Client.php`:
  ```php
  // New relationships
  public function channelConnections(): HasMany
  public function performanceSnapshots(): HasMany
  public function performanceAnomalies(): HasMany
  public function healthScores(): HasMany
  public function latestHealthScore(): HasOne  // ordered by score_date desc
  public function aiInsights(): HasMany

  // New computed attribute
  public function getCurrentHealthScoreAttribute(): ?int
  // returns latestHealthScore->overall_score or null

  // New scope
  public function scopeWithHealthScores($query)  // eager loads latestHealthScore
  ```

---

## PHASE 2 — Monitoring Engine: Services & Jobs
> **Goal**: The pipeline runs nightly and populates snapshot + anomaly + health score tables.
> **Estimated time**: 2 days
> **Prerequisite**: Phase 1 complete (TASK-001 to TASK-018 done)

---

### 2.1 — Services Directory Setup

- [x] `🔴⚡` **[TASK-019]** Create directory `app/Services/Intelligence/`
  ```bash
  mkdir -p app/Services/Intelligence
  ```

---

### 2.2 — ChannelDataAggregatorService

- [x] `🔴` **[TASK-020]** Create `app/Services/Intelligence/ChannelDataAggregatorService.php`

  This service reads **existing tables only** — no new API calls. One public method per channel type.

  Methods to implement:
  
  - `aggregateMetaAds(string $clientId, string $orgId, string $date): array`
    - Query `ad_insights` JOIN `campaigns` WHERE `client_id` = $clientId AND `date` = $date
    - Aggregate: SUM spend, impressions, clicks, conversions, revenue
    - Calculate: weighted avg CTR, CPC, CPM, ROAS
    - Return: standardized array matching `performance_snapshots` columns

  - `aggregateGoogleAds(string $clientId, string $orgId, string $date): array`
    - Query `campaign_refs` + `daily_metrics` for Google-type campaigns
    - Same aggregation pattern as Meta

  - `aggregateSocialOrganic(string $clientId, string $orgId, string $date): array`
    - Query `social_posts` WHERE `client_id` = $clientId AND `published_at` date = $date
    - Aggregate: SUM reach, engagement, impressions
    - Calculate: avg engagement_rate

  - `aggregateLeads(string $clientId, string $orgId, string $date): array`
    - Query `leads` WHERE `client_id` = $clientId AND DATE(`created_at`) = $date
    - Count total, high-intent (score >= 80), source breakdown

  - `aggregateConversions(string $clientId, string $orgId, string $date): array`
    - Query `conversion_events` + `funnel_metrics` for client
    - Return conversion rates and funnel drop-off data

  - `aggregateAll(string $clientId, string $orgId, string $date): array`
    - Calls all channel methods above
    - Returns keyed array: `['meta_ads' => [...], 'google_ads' => [...], ...]`
    - Skips channels with no data (returns empty array for that key)

---

### 2.3 — AnomalyDetectionService

- [x] `🔴` **[TASK-021]** Create `app/Services/Intelligence/AnomalyDetectionService.php`

  Constructor injects `config('intelligence.thresholds')`.

  Methods to implement:

  - `detect(array $snapshot, array $baselines, string $clientId, string $orgId): array`
    - Main entry point — calls all individual detectors
    - Returns array of `PerformanceAnomaly`-ready data arrays

  - `detectCtrDrop(array $snapshot, array $baselines): ?array`
    - `if (baseline_ctr > 0 && current_ctr < baseline_ctr * (1 - threshold/100))`
    - Returns anomaly data array or null

  - `detectCpcSpike(array $snapshot, array $baselines): ?array`
    - `if (baseline_cpc > 0 && current_cpc > baseline_cpc * (1 + threshold/100))`

  - `detectRoasDecline(array $snapshot, array $baselines): ?array`
    - `if (current_roas < config roas_min_threshold)`

  - `detectBudgetOverpace(array $snapshot, string $clientId): ?array`
    - Compare daily spend vs expected daily budget from `ad_accounts`

  - `detectBudgetUnderpace(array $snapshot, string $clientId): ?array`
    - Flag if spend < 60% of daily budget

  - `detectEngagementDrop(array $snapshot, array $baselines): ?array`
    - Compare `engagement_rate` vs 7-day baseline

  - `detectLeadDrop(array $snapshot, array $baselines): ?array`
    - Compare `leads` count vs 7-day avg

  - `calculateSeverity(float $deviationPct): string`
    - > 50% → `critical`, > 30% → `high`, > 20% → `medium`, else → `low`

---

### 2.4 — PerformanceMonitorService

- [x] `🔴` **[TASK-022]** Create `app/Services/Intelligence/PerformanceMonitorService.php`

  Injects `ChannelDataAggregatorService` and `AnomalyDetectionService`.

  Methods to implement:

  - `runForOrganization(string $orgId, string $date = null): void`
    - Gets all active clients for org
    - Calls `runForClient()` for each
    - Logs total processed count

  - `runForClient(string $clientId, string $orgId, string $date): void`
    - Calls `aggregateAll()` for each channel
    - For each channel result:
      - Calculates 7-day baselines (avg from last 7 `performance_snapshots`)
      - Creates/updates `PerformanceSnapshot` record
      - Runs `AnomalyDetectionService::detect()`
      - Creates `PerformanceAnomaly` records for detected issues
    - Calls `calculateHealthScore()` for client
    - Catches exceptions per client — logs error and continues

  - `calculateBaselines(string $clientId, string $channel, string $date): array`
    - Queries last 7 days of `performance_snapshots` for same client+channel
    - Returns avg values for ctr, cpc, roas, leads, engagement_rate

  - `calculateHealthScore(string $clientId, string $orgId, string $date): void`
    - Reads today's snapshots + anomalies for client
    - Calculates 4 dimension scores (ad_perf, organic, conversion, budget_efficiency)
    - Weighted average → overall_score
    - Determines trend by comparing to last 7 days of scores
    - Creates `ClientHealthScore` record

  - `getHealthScoreWeights(): array`
    - Returns: `['ad_performance' => 0.40, 'conversion' => 0.30, 'organic' => 0.20, 'budget' => 0.10]`

---

### 2.5 — Background Jobs (5 jobs)

- [x] `🟡` **[TASK-023]** Create `app/Jobs/Intelligence/` directory
  ```bash
  mkdir -p app/Jobs/Intelligence
  ```

- [x] `🟡` **[TASK-024]** Create `app/Jobs/Intelligence/FetchClientPerformanceData.php`
  - Implements `ShouldQueue`
  - `$queue = 'intelligence'`
  - `$tries = 3`
  - `handle()`: instantiates `ChannelDataAggregatorService` + `PerformanceMonitorService`
  - Calls `PerformanceMonitorService::runForOrganization()` for each org in system
  - Logs start/end with timing

- [x] `🟡` **[TASK-025]** Create `app/Jobs/Intelligence/RunAnomalyDetection.php`
  - Reads today's `performance_snapshots` that have no anomaly records yet
  - Runs `AnomalyDetectionService` for each unprocessed snapshot
  - Updates `anomaly_flags` on snapshot

- [x] `🟡` **[TASK-026]** Create `app/Jobs/Intelligence/GenerateAiInsights.php`
  - Reads today's unprocessed `PerformanceAnomaly` records (no `AiInsight` linked)
  - Dispatches `AiInsightsService::generateForAnomaly()` per anomaly
  - Groups by client to reduce API calls (batch anomalies per client into one prompt)

- [x] `🟡` **[TASK-027]** Create `app/Jobs/Intelligence/GenerateDailyBriefing.php`
  - Reads today's `AiInsight` records ordered by priority
  - Creates `DailyBriefing` record per org with `status: generating`
  - Creates `BriefingActionItem` records sorted by urgency + priority
  - Updates briefing `status` to `ready` and sets `generated_at`

- [x] `🟡` **[TASK-028]** Create `app/Jobs/Intelligence/SendDailyBriefingEmail.php`
  - Reads today's `DailyBriefing` records with `status: ready`
  - If `config('intelligence.briefing_email_enabled')` is true:
    - Gets org owner + admin emails
    - Sends `DailyBriefingMail` mailable (created in Phase 3)
  - Updates briefing `status` to `sent` and `sent_at`

---

### 2.6 — Scheduler Registration

- [x] `🟡` **[TASK-029]** Update `routes/console.php` — add the intelligence pipeline schedule:
  ```php
  use App\Jobs\Intelligence\FetchClientPerformanceData;
  use App\Jobs\Intelligence\RunAnomalyDetection;
  use App\Jobs\Intelligence\GenerateAiInsights;
  use App\Jobs\Intelligence\GenerateDailyBriefing;
  use App\Jobs\Intelligence\SendDailyBriefingEmail;

  Schedule::job(new FetchClientPerformanceData)->dailyAt('02:00')->name('intelligence:fetch')->withoutOverlapping();
  Schedule::job(new RunAnomalyDetection)->dailyAt('04:00')->name('intelligence:anomalies')->withoutOverlapping();
  Schedule::job(new GenerateAiInsights)->dailyAt('05:00')->name('intelligence:ai-insights')->withoutOverlapping();
  Schedule::job(new GenerateDailyBriefing)->dailyAt('06:00')->name('intelligence:briefing')->withoutOverlapping();
  Schedule::job(new SendDailyBriefingEmail)->dailyAt('07:00')->name('intelligence:email')->withoutOverlapping();
  ```

- [x] `🟡⚡` **[TASK-030]** Add `intelligence` queue to Horizon config (`config/horizon.php`):
  ```php
  'intelligence' => [
      'connection' => 'redis',
      'queue'      => ['intelligence'],
      'balance'    => 'simple',
      'processes'  => 2,
      'tries'      => 3,
  ],
  ```

- [x] `🟡⚡` **[TASK-031]** Add Artisan commands for manual triggering (for testing):
  ```bash
  php artisan make:command Intelligence/RunDailyPipeline
  ```
  Command: `intelligence:run-pipeline {--org=}` — triggers full pipeline for one or all orgs.

---

## PHASE 3 — AI Insights Engine
> **Goal**: AI converts raw anomaly data into actionable plain-English recommendations.
> **Estimated time**: 1 day
> **Prerequisite**: Phase 1 + Phase 2 complete

---

### 3.1 — AiInsightsService

- [x] `🔴🤖` **[TASK-032]** Create `app/Services/Intelligence/AiInsightsService.php`

  Methods to implement:

  - `generateForClient(string $clientId, string $orgId, string $date): void`
    - Gets all `PerformanceAnomaly` records for client+date
    - Gets last 7 days of `PerformanceSnapshot` data for context
    - Builds prompt with `buildPrompt()`
    - Calls AI API with `callAiApi()`
    - Parses response with `parseResponse()`
    - Creates `AiInsight` records

  - `buildPrompt(array $anomalies, array $context, Client $client): string`
    - Structured prompt with real metric data only
    - Instructs AI to return valid JSON array
    - Includes client name, industry, and channel context
    - Example prompt structure is defined clearly so AI knows output format

  - `callAiApi(string $prompt): string`
    - Reads `config('intelligence.ai_provider')`
    - Routes to `callGemini()` or `callOpenAi()`
    - Handles rate limiting with exponential backoff (3 retries)
    - Throws `AiInsightsException` on failure

  - `callGemini(string $prompt): string`
    - Uses Guzzle (already installed) to POST to Gemini API
    - `https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent`
    - Parses response JSON → extracts text content

  - `callOpenAi(string $prompt): string`
    - Uses Guzzle to POST to OpenAI Chat Completions API
    - Model: `gpt-4o-mini` (cost-effective)
    - Returns content string

  - `parseResponse(string $rawResponse, string $clientId, string $orgId, string $date): array`
    - JSON-decodes AI response
    - Validates each insight has required fields
    - Maps to `AiInsight` model fillable columns
    - Falls back gracefully if JSON is malformed

  - `generateOpportunities(string $clientId, string $orgId, string $date): void`
    - Separate AI call specifically for opportunity spotting (not just problems)
    - Looks at: high impression share loss, day-of-week conversion patterns, underutilized budget

- [x] `🔴⚡` **[TASK-033]** Create `app/Exceptions/AiInsightsException.php`
  - Simple exception class for AI-layer errors

- [x] `🔴` **[TASK-034]** Create AI prompt templates in `app/Services/Intelligence/Prompts/`

  Two prompt files:
  - `AnomalyInsightPrompt.php` — for converting anomalies into insights
  - `OpportunityInsightPrompt.php` — for spotting growth opportunities

  Each is a PHP class with a static `build(array $data): string` method.

---

### 3.2 — Email Notification

- [x] `🟡` **[TASK-035]** Create `app/Mail/DailyBriefingMail.php`
  ```bash
  php artisan make:mail DailyBriefingMail --markdown=emails.intelligence.daily-briefing
  ```
  - Constructor: receives `DailyBriefing` model with eager-loaded `actionItems.client`
  - Subject: `📋 Agency Briefing — {date} | {urgent_count} urgent items`
  - `envelope()` and `content()` methods following Laravel 11 Mail pattern

- [x] `🟡` **[TASK-036]** Create `resources/views/emails/intelligence/daily-briefing.blade.php`
  - Markdown email template
  - Sections: Urgent actions, Important items, Opportunities, Quick stats table
  - Link to `/intelligence/briefing` for full view

---

## PHASE 4 — Intelligence UI  
> **Goal**: 5 new Livewire modules, full navigation integration.
> **Estimated time**: 3 days
> **Prerequisite**: Phase 1 complete (UI can be built with mock data before Phase 2/3)

---

### 4.0 — Directory Setup

- [x] `🔴⚡` **[TASK-037]** Create Livewire component directories:
  ```bash
  mkdir -p app/Livewire/Intelligence
  mkdir -p resources/views/livewire/intelligence
  ```

---

### 4.1 — Routes Registration

- [x] `🔴⚡` **[TASK-038]** Add Intelligence routes to `routes/web.php` inside the `auth + verified + organization` middleware group:
  ```php
  // Intelligence Module
  Route::prefix('intelligence')->name('intelligence.')->group(function () {
      Route::get('/', \App\Livewire\Intelligence\Overview::class)->name('overview');
      Route::get('/briefing', \App\Livewire\Intelligence\BriefingDashboard::class)->name('briefing');
      Route::get('/insights', \App\Livewire\Intelligence\InsightsFeed::class)->name('insights');
      Route::get('/alerts', \App\Livewire\Intelligence\AlertCenter::class)->name('alerts');
      Route::get('/clients/{client}/workspace', \App\Livewire\Intelligence\ClientWorkspace::class)->name('client.workspace');
  });
  ```

---

### 4.2 — Overview Component

- [x] `🟡` **[TASK-039]** Create `app/Livewire/Intelligence/Overview.php`
  - Loads all clients with their `latestHealthScore`
  - Groups by health status: healthy (≥70), attention (40-69), critical (<40)
  - Loads aggregate KPIs: total spend (yesterday), total leads (yesterday), avg ROAS, top performer, worst performer
  - Loads count of active anomalies by severity
  - Returns view with layout `layouts.app`

- [x] `🟡` **[TASK-040]** Create `resources/views/livewire/intelligence/overview.blade.php`
  - **Header**: "Performance Intelligence" with "Run Now" button (dispatches Artisan command)
  - **Section 1**: 4 KPI stat cards (total spend, total leads, avg ROAS, active alerts)
  - **Section 2**: Client health grid — card per client showing:
    - Client name, industry
    - Health score with color ring (green/yellow/red)
    - Trend indicator (↑↓→) with small sparkline
    - Top channel (best performing)
    - Active anomaly count badge
    - Link to `/intelligence/clients/{client}/workspace`
  - **Section 3**: Quick anomaly list (severity-sorted, last 5)
  - Dark mode-aware, smooth hover transitions on client cards

---

### 4.3 — Briefing Dashboard Component

- [x] `🟡` **[TASK-041]** Create `app/Livewire/Intelligence/BriefingDashboard.php`
  - Loads today's `DailyBriefing` for the org (or most recent if today not generated)
  - Eager loads `actionItems.client`
  - Public method: `completeItem($itemId)` — marks `BriefingActionItem` completed
  - Public method: `dismissItem($itemId)` — dismisses the linked `AiInsight`
  - Public method: `regenerate()` — dispatches `GenerateDailyBriefing` job (OWNER/ADMIN only)
  - Polls for `status = ready` if `status = generating`

- [x] `🟡` **[TASK-042]** Create `resources/views/livewire/intelligence/briefing-dashboard.blade.php`
  - **Header**: Date, briefing status badge, "Regenerate" button
  - **Summary bar**: X urgent · Y important · Z opportunities · X% completed today
  - **Section 1 — URGENT** (red left border): Action items sorted by sort_order
    - Client name chip, channel icon, title, action text, effort badge
    - "Mark Done" button (Livewire action), "View Insight" link
  - **Section 2 — IMPORTANT** (amber left border): Same layout
  - **Section 3 — OPPORTUNITIES** (green left border): Same layout
  - **Empty state**: "✅ All caught up! No briefing for today yet." with generate button
  - Progress bar at top showing % of items completed

---

### 4.4 — Insights Feed Component

- [x] `🟡` **[TASK-043]** Create `app/Livewire/Intelligence/InsightsFeed.php`
  - Public properties: `$filter = 'all'`, `$clientFilter = ''`, `$channelFilter = ''`, `$priorityFilter = ''`
  - Loads paginated `AiInsight` records (15 per page) with filters applied
  - Only shows non-dismissed, non-completed by default
  - Toggle: `showCompleted` and `showDismissed` booleans
  - Public methods: `dismiss($id)`, `complete($id)`, `setFilter($value)`

- [x] `🟡` **[TASK-044]** Create `resources/views/livewire/intelligence/insights-feed.blade.php`
  - **Filter bar**: Priority pills, Client dropdown, Channel dropdown, Status toggles
  - **Feed**: Stacked insight cards, each showing:
    - Priority badge (color-coded), category icon
    - Client name + channel
    - Title (bold), issue description (truncated), root cause (collapsible)
    - "Recommended Action" box (highlighted)
    - Expected impact + effort badge
    - Urgency tag (TODAY / THIS WEEK / NEXT WEEK)
    - Dismiss (×) and Complete (✓) buttons
  - Pagination at bottom
  - Empty state with illustration

---

### 4.5 — Alert Center Component

- [x] `🟡` **[TASK-045]** Create `app/Livewire/Intelligence/AlertCenter.php`
  - Loads `PerformanceAnomaly` records with `resolved_at IS NULL`
  - Groups by: critical → high → medium → low
  - Eager loads `client`, `snapshot`
  - Public properties: `$sevFilter = 'all'`, `$clientFilter = ''`
  - Public method: `resolve($anomalyId)` — marks anomaly resolved
  - Public method: `viewClient($clientId)` — redirect to client workspace

- [x] `🟡` **[TASK-046]** Create `resources/views/livewire/intelligence/alert-center.blade.php`
  - **Summary banner**: X critical · X high · X medium · X low — color-coded counts
  - **Filter row**: severity pills + client dropdown
  - **Alert list** grouped by severity, each row showing:
    - Severity badge, anomaly type label
    - Client name, channel icon
    - Metric name: current → baseline (deviation %)
    - Detection time (diff for humans)
    - "Resolve" button + "View Workspace" link
  - Critical alerts have pulsing red left border
  - "No active alerts" empty state (green check, celebratory)

---

### 4.6 — Client Workspace Component

- [x] `🟡` **[TASK-047]** Create `app/Livewire/Intelligence/ClientWorkspace.php`
  - Route model binding: receives `Client $client`
  - Authorization: user must belong to same organization
  - Public property: `$dateRange = '7d'` (options: 1d, 7d, 30d)
  - Loads:
    - Client's `channelConnections` (active)
    - `PerformanceSnapshot` records for date range, grouped by channel
    - `ClientHealthScore` records for sparkline (last 30 days)
    - Active `AiInsight` records for this client
    - Active `PerformanceAnomaly` records for this client
  - Public method: `setDateRange($range)` — updates property, refreshes data
  - Computes channel-level metrics for display

- [x] `🟡` **[TASK-048]** Create `resources/views/livewire/intelligence/client-workspace.blade.php`
  - **Header**: Client name, industry, health score ring, trend badge, date range selector
  - **Health Score Card**: Large score number, 4 dimension scores as horizontal bars
  - **Channel Grid**: One card per connected channel showing:
    - Channel icon + name
    - Key metrics: CTR, CPC, ROAS, Spend (for ad channels) OR Reach, Engagement (for organic)
    - vs baseline indicator (↑ green / ↓ red)
    - Active anomaly count badge
  - **Performance Timeline**: Chart placeholder (uses Chart.js via CDN) — spend + leads over selected period
  - **Active Insights panel**: List of `AiInsight` cards for this client
  - **Connected Accounts section**: List of connected channel accounts with last synced time

---

### 4.7 — Navigation Integration

- [x] `🟡` **[TASK-049]** Add "Intelligence" nav group to sidebar — update `resources/views/components/layouts/sidebar-navigation.blade.php`:
  ```html
  <!-- Intelligence -->
  <div class="nav-group-label">Intelligence</div>
  <a href="{{ route('intelligence.overview') }}" class="nav-link {{ request()->routeIs('intelligence.*') ? 'active' : '' }}">
      <svg><!-- brain/chart icon --></svg>
      Performance Intel
  </a>
  <a href="{{ route('intelligence.briefing') }}" class="nav-link">
      <svg><!-- clipboard icon --></svg>
      Morning Briefing
      @if($urgentCount > 0)
          <span class="badge-red">{{ $urgentCount }}</span>
      @endif
  </a>
  <a href="{{ route('intelligence.insights') }}" class="nav-link">
      <svg><!-- lightbulb icon --></svg>
      AI Insights
  </a>
  <a href="{{ route('intelligence.alerts') }}" class="nav-link">
      <svg><!-- bell icon --></svg>
      Alert Center
      @if($criticalCount > 0)
          <span class="badge-red animate-pulse">{{ $criticalCount }}</span>
      @endif
  </a>
  ```

- [x] `🟡⚡` **[TASK-050]** Create `app/View/Composers/NavigationComposer.php`
  - Provides `$urgentCount` (today's urgent briefing items not completed) and `$criticalCount` (unresolved critical anomalies) to sidebar
  - Registered in `AppServiceProvider::boot()` via `View::composer()`

- [x] `🟡⚡` **[TASK-051]** Register the view composer in `app/Providers/AppServiceProvider.php`:
  ```php
  View::composer('components.layouts.sidebar-navigation', NavigationComposer::class);
  ```

---

## PHASE 5 — Dashboard Upgrade
> **Goal**: Main dashboard shows intelligence preview without leaving the home page.
> **Estimated time**: 1 day
> **Prerequisite**: Phase 1 complete (needs models/data)

---

### 5.1 — Dashboard Livewire Updates

- [x] `🔴` **[TASK-052]** Update `app/Livewire/Dashboard/Index.php` — add 4 new data props:

  ```php
  // Add to render() method, existing $stats array:
  'client_health_grid' => Client::with(['latestHealthScore'])
      ->where('organization_id', $orgId)
      ->where('status', 'ACTIVE')
      ->limit(8)
      ->get(),

  'morning_briefing_preview' => DailyBriefing::with(['actionItems' => fn($q) => $q->limit(3)->orderBy('sort_order')])
      ->where('organization_id', $orgId)
      ->where('briefing_date', today())
      ->where('status', 'ready')
      ->first(),

  'recent_anomalies' => PerformanceAnomaly::with('client')
      ->where('organization_id', $orgId)
      ->whereNull('resolved_at')
      ->orderByRaw("FIELD(severity, 'critical', 'high', 'medium', 'low')")
      ->limit(5)
      ->get(),

  'performance_pulse' => [
      'total_spend_yesterday'  => PerformanceSnapshot::where(...)->whereDate('snapshot_date', yesterday())->sum('spend'),
      'total_leads_yesterday'  => PerformanceSnapshot::...->sum('leads'),
      'avg_roas_yesterday'     => PerformanceSnapshot::...->avg('roas'),
      'top_client'             => ...,
  ],
  ```

- [x] `🟡` **[TASK-053]** Update `resources/views/livewire/dashboard/index.blade.php` — add new bottom section after existing content:

  **Section: Performance Intelligence Preview**
  - Client health score mini-grid (up to 8 clients, 2-col grid)
    - Each: client name, score number, color circle, trend arrow
    - Click → goes to client workspace
  - Today's top 3 briefing action items OR "No briefing yet" CTA
  - Last 5 anomalies mini-list with severity badges
  - "View Full Intelligence Dashboard →" link to `/intelligence`

---

### 5.2 — Polish & Shared Helpers

- [x] `🟢⚡` **[TASK-054]** Create Blade component `resources/views/components/intelligence/health-score-ring.blade.php`
  - Reusable SVG ring that renders health score 0-100 with color coding
  - Props: `$score`, `$size = 'md'`
  - Sizes: sm (32px), md (48px), lg (64px)

- [x] `🟢⚡` **[TASK-055]** Create Blade component `resources/views/components/intelligence/priority-badge.blade.php`
  - Reusable badge for priority levels
  - Props: `$priority` (critical/high/medium/low/opportunity)
  - Returns styled pill with icon + label

- [x] `🟢⚡` **[TASK-056]** Create Blade component `resources/views/components/intelligence/channel-icon.blade.php`
  - SVG icon per channel type (meta_ads, google_ads, linkedin_ads, instagram, facebook_organic)
  - Props: `$channel`, `$size = 'sm'`

- [x] `🟢⚡` **[TASK-057]** Create Blade component `resources/views/components/intelligence/anomaly-badge.blade.php`
  - Severity-colored badge with icon
  - Props: `$severity`, `$count = null`

---

## PHASE 6 — Testing & Hardening
> **Goal**: Core pipeline verified, edge cases handled, developer can trigger flows manually.
> **Estimated time**: 1–2 days

---

### 6.1 — Manual Testing Commands

- [x] `🟢` **[TASK-058]** Create Artisan command `app/Console/Commands/Intelligence/SeedTestData.php`
  ```
  php artisan intelligence:seed-test-data {--org=}
  ```
  - Creates sample `PerformanceSnapshot` records for all clients in an org (last 30 days of mock data)
  - Useful for developing UI without waiting for real syncs

- [x] `🟢` **[TASK-059]** Create Artisan command `app/Console/Commands/Intelligence/RunPipeline.php`
  ```
  php artisan intelligence:run {--org=} {--date=} {--step=}
  ```
  - Runs any single step of the pipeline manually:
    - `--step=fetch` → FetchClientPerformanceData
    - `--step=anomalies` → RunAnomalyDetection
    - `--step=insights` → GenerateAiInsights
    - `--step=briefing` → GenerateDailyBriefing
    - `--step=all` → all steps sequentially

- [x] `🟢` **[TASK-060]** Create Artisan command `app/Console/Commands/Intelligence/TestAiConnection.php`
  ```
  php artisan intelligence:test-ai
  ```
  - Sends a minimal test prompt to configured AI provider
  - Prints response time and success/failure

---

### 6.2 — Error Handling & Resilience

- [x] `🟢` **[TASK-061]** Add try/catch in `PerformanceMonitorService::runForClient()` so one failed client doesn't block others. Log error with `Log::error()` including client_id and org_id context.

- [x] `🟢` **[TASK-062]** Add `AiInsightsService` fallback: if AI call fails after 3 retries, create a generic `AiInsight` record with `raw_ai_response = null` and a predefined fallback message based on anomaly type.

- [x] `🟢` **[TASK-063]** Add duplicate prevention in `FetchClientPerformanceData` job: check if `PerformanceSnapshot` already exists for `[client_id, channel_type, snapshot_date]` before creating — use `updateOrCreate()`.

- [x] `🟢` **[TASK-064]** Add `PerformanceAnomaly::resolve()` to auto-resolve stale anomalies: if same anomaly type no longer detected after 3 consecutive days, mark resolved.

---

### 6.3 — Performance Optimization

- [x] `🟢⚡` **[TASK-065]** Add database indexes (if not already in migrations):
  - `performance_snapshots`: composite index on `[client_id, snapshot_date, channel_type]`
  - `ai_insights`: index on `[organization_id, is_dismissed, is_completed, insight_date]`
  - `performance_anomalies`: index on `[organization_id, resolved_at, severity]`
  - `client_health_scores`: index on `[client_id, score_date]`

- [x] `🟢⚡` **[TASK-066]** Add caching to `NavigationComposer` — cache `$urgentCount` and `$criticalCount` for 5 minutes per org to avoid repeated queries on every page render:
  ```php
  Cache::remember("nav.urgent.{$orgId}", 300, fn() => ...);
  ```

- [x] `🟢⚡` **[TASK-067]** Add caching to `ClientWorkspace` component — cache aggregated channel metrics per client per date range for 10 minutes.

---

## Quick Reference — All New Files

### Migrations (7)
```
database/migrations/*_create_client_channel_connections_table.php   [TASK-003]
database/migrations/*_create_performance_snapshots_table.php        [TASK-004]
database/migrations/*_create_performance_anomalies_table.php        [TASK-005]
database/migrations/*_create_client_health_scores_table.php         [TASK-006]
database/migrations/*_create_ai_insights_table.php                  [TASK-007]
database/migrations/*_create_daily_briefings_table.php              [TASK-008]
database/migrations/*_create_briefing_action_items_table.php        [TASK-009]
```

### Models (7 new + 1 updated)
```
app/Models/ClientChannelConnection.php   [TASK-011]
app/Models/PerformanceSnapshot.php       [TASK-012]
app/Models/PerformanceAnomaly.php        [TASK-013]
app/Models/ClientHealthScore.php         [TASK-014]
app/Models/AiInsight.php                 [TASK-015]
app/Models/DailyBriefing.php             [TASK-016]
app/Models/BriefingActionItem.php        [TASK-017]
app/Models/Client.php  [UPDATED]         [TASK-018]
```

### Services (5)
```
app/Services/Intelligence/ChannelDataAggregatorService.php   [TASK-020]
app/Services/Intelligence/AnomalyDetectionService.php        [TASK-021]
app/Services/Intelligence/PerformanceMonitorService.php      [TASK-022]
app/Services/Intelligence/AiInsightsService.php              [TASK-032]
app/Services/Intelligence/Prompts/AnomalyInsightPrompt.php   [TASK-034]
app/Services/Intelligence/Prompts/OpportunityInsightPrompt.php [TASK-034]
```

### Jobs (5)
```
app/Jobs/Intelligence/FetchClientPerformanceData.php   [TASK-024]
app/Jobs/Intelligence/RunAnomalyDetection.php          [TASK-025]
app/Jobs/Intelligence/GenerateAiInsights.php           [TASK-026]
app/Jobs/Intelligence/GenerateDailyBriefing.php        [TASK-027]
app/Jobs/Intelligence/SendDailyBriefingEmail.php       [TASK-028]
```

### Livewire Components (5)
```
app/Livewire/Intelligence/Overview.php             [TASK-039]
app/Livewire/Intelligence/BriefingDashboard.php    [TASK-041]
app/Livewire/Intelligence/InsightsFeed.php         [TASK-043]
app/Livewire/Intelligence/AlertCenter.php          [TASK-045]
app/Livewire/Intelligence/ClientWorkspace.php      [TASK-047]
```

### Blade Views (5 Livewire + 4 components + 1 email)
```
- [x] `🟢` **[TASK-040]** Create `resources/views/livewire/intelligence/client-performance-center.blade.php`
resources/views/livewire/intelligence/briefing-dashboard.blade.php  [TASK-042]
resources/views/livewire/intelligence/insights-feed.blade.php       [TASK-044]
resources/views/livewire/intelligence/alert-center.blade.php        [TASK-046]
resources/views/livewire/intelligence/client-workspace.blade.php    [TASK-048]
resources/views/components/intelligence/health-score-ring.blade.php [TASK-054]
resources/views/components/intelligence/priority-badge.blade.php    [TASK-055]
resources/views/components/intelligence/channel-icon.blade.php      [TASK-056]
resources/views/components/intelligence/anomaly-badge.blade.php     [TASK-057]
resources/views/emails/intelligence/daily-briefing.blade.php        [TASK-036]
```

### Mail (1)
```
app/Mail/DailyBriefingMail.php   [TASK-035]
```

### Console Commands (3)
```
app/Console/Commands/Intelligence/SeedTestData.php   [TASK-058]
app/Console/Commands/Intelligence/RunPipeline.php    [TASK-059]
app/Console/Commands/Intelligence/TestAiConnection.php [TASK-060]
```

### Config & Helpers (3 files updated/created)
```
config/intelligence.php                             [TASK-002]
routes/web.php              [UPDATED]               [TASK-038]
routes/console.php          [UPDATED]               [TASK-029]
config/horizon.php          [UPDATED]               [TASK-030]
app/Providers/AppServiceProvider.php  [UPDATED]     [TASK-051]
app/View/Composers/NavigationComposer.php           [TASK-050]
```

---

## Progress Tracker

| Phase | Tasks | Done | % |
|-------|-------|------|---|
| Phase 1 — Foundation | TASK-001 to TASK-018 | 18 | 100% |
| Phase 2 — Monitoring | TASK-019 to TASK-031 | 13 | 100% |
| Phase 3 — AI Engine | TASK-032 to TASK-036 | 5 | 100% |
| Phase 4 — UI | TASK-037 to TASK-051 | 15 | 100% |
| Phase 5 — Dashboard | TASK-052 to TASK-057 | 6 | 100% |
| Phase 6 — Testing | TASK-058 to TASK-067 | 10 | 100% |
| **TOTAL** | **67 tasks** | **67** | **100%** |

---

*Last updated: 2026-03-30*
*Platform: DC OS — Laravel 11 · Livewire 3*
