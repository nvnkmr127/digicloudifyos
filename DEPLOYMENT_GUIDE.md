# Deployment Guide & Environment Matrix

This document outlines the deployment topology, environment variables, and operational playbooks for the DC OS application.

## Environment Matrix

The application supports multiple environments. The following core variables must be adjusted across environments to maintain parity and security:

| Variable | Dev / Local | Staging | Production | Description |
|---|---|---|---|---|
| `APP_ENV` | `local` | `staging` | `production` | Determines Laravel's environment behavior. |
| `APP_DEBUG` | `true` | `false` | `false` | MUST be `false` in production to prevent leaking stack traces. |
| `DB_CONNECTION` | `sqlite` / `mysql` | `mysql` / `pgsql` | `mysql` / `pgsql` | Production databases should use a managed DB service. |
| `SESSION_DRIVER` | `database` | `redis` | `redis` | Redis provides better scale for production sessions. |
| `QUEUE_CONNECTION` | `database` / `sync` | `redis` | `redis` | Horizon requires `redis` to run. |
| `CACHE_STORE` | `database` | `redis` | `redis` | Distributed caching backend. |

### Required External Integrations (Prod/Staging)
To enable the full feature set, these third-party credentials must be provided:
- **Meta/Facebook**: `META_APP_ID`, `META_APP_SECRET`, `META_REDIRECT_URI`, `META_VERIFY_TOKEN`
- **Google**: `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`, `GOOGLE_ADS_DEVELOPER_TOKEN`
- **AI/Intelligence**: `OPENAI_API_KEY` or `GEMINI_API_KEY` (configured via `AI_PROVIDER`)
- **Other Ads/Social**: `LINKEDIN_*`, `SHOPIFY_*`, `TWITTER_*`, `AMAZON_LWA_*`

## Deployment Checklist & Reliability Risks

### Current Topology Risks
1. **Missing Containerization:** The project lacks `Dockerfile` and `docker-compose.yml` for production parity, making reproducible deployments difficult.
2. **Missing CI/CD Deploy Steps:** The current GitHub Action (`.github/workflows/ci.yml`) only runs tests and builds assets. It does not push artifacts or execute `php artisan migrate --force`.
3. **Environment Drift:** The `.env.example` file is missing many required variables present in `config/services.php` and `config/intelligence.php` (e.g., API keys).

### Health Checks & Probes
- **Liveness/Readiness Endpoint:** `GET /up`
  Laravel 11 natively provides this endpoint. It checks if the framework boots and if the database is reachable.
- **Queue Health:** Rely on Laravel Horizon (`php artisan horizon:status`) to monitor queue health.

### Deployment Playbook (Manual / Standard)
Until containerization/IaC is introduced, deployments must follow this idempotent script:

```bash
# 1. Enter maintenance mode
php artisan down || true

# 2. Pull latest code
git pull origin main

# 3. Install dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader
npm ci
npm run build

# 4. Clear and cache configurations
php artisan optimize:clear
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache

# 5. Run migrations (force required in prod)
php artisan migrate --force

# 6. Restart Queue workers
php artisan horizon:terminate

# 7. Exit maintenance mode
php artisan up
```

## Recommended Next Steps
- Add a `Dockerfile` for standardized deployments.
- Update `.env.example` to include placeholder values for all third-party API keys.
- Introduce deployment pipelines in `.github/workflows/deploy.yml`.
