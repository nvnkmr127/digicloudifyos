# Contributing

## Naming Conventions

### Platforms and Channels

- Use `meta` in code and UI for the Meta ecosystem. Use `facebook` only where the external API or webhook contract requires it.
- Use consistent identifiers across layers:
  - Ad account platforms are stored as uppercase enum values (e.g. `META_ADS`, `GOOGLE_ADS`) in `ad_accounts.platform`.
  - Client channel connections use lowercase snake case identifiers (e.g. `meta_ads`, `ga4`, `google_search_console`) in `client_channel_connections.channel_type`.
  - Route params should be normalized to the canonical identifiers before persistence.

### Status Fields

- Prefer PHP enums for “allowed values” and reuse them from Livewire + FormRequests.
- Status values must be consistent across DB, model helpers/scopes, and UI selects.

## Error Handling

- API errors must use a consistent envelope:
  - `{ success: false, message: string, errors: array, meta?: { request_id?: string } }`
- Do not return raw exception messages to users unless explicitly intended and non-sensitive.
- Every inbound request should have a `request_id`:
  - Read from `X-Request-Id` if provided, else generate.
  - Include it in logs and return it as `X-Request-Id` response header.

## Logging and Sensitive Data

- Do not log secrets (tokens, client secrets, webhook secrets) or PII (email, phone, address).
- For webhook deliveries:
  - Store redacted payloads and truncate response bodies.
  - Never throw exceptions that embed full response bodies.

## Background Jobs

- Jobs that call external APIs must set:
  - `timeout()` and `retry()` on HTTP requests.
  - `$tries` and `$timeout` on the job.
- Avoid network calls inside DB transactions.
- Scheduled commands should use `withoutOverlapping()` unless there is a strong reason not to.

## Data Integrity

- Multi-write workflows must be transactional when database state must be all-or-nothing.
- Enforce invariants in schema with unique constraints where appropriate.

## Repo Hygiene

- Never commit runtime artifacts:
  - `storage/framework/views/*`
  - `storage/framework/sessions/*`
  - `storage/framework/cache/*`

