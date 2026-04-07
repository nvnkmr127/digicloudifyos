# KPI Definitions

## Core Units

- Currency: same unit as stored spend/revenue values (no formatting).
- Percent: 0–100.
- Ratio: unitless (e.g. ROAS `2.5x`).

## Formulas

### CTR (Click-through rate)

- Percent: `(clicks / impressions) * 100`

### CPC (Cost per click)

- Currency per click: `spend / clicks`

### CPM (Cost per 1,000 impressions)

- Currency per 1,000 impressions: `(spend / impressions) * 1000`

### CVR (Conversion rate)

- Percent: `(conversions / clicks) * 100`

### CPL (Cost per lead)

- Currency per lead: `spend / leads`

### ROAS

- Ratio: `revenue / spend`
- Blended ROAS must be computed from totals (recommended): `SUM(revenue) / SUM(spend)` rather than `AVG(roas)`.

### ROI (estimate)

- Percent: `((revenue - spend) / spend) * 100`
- Null when `spend = 0`.

## Date Windows

- Dashboard “30days/90days” windows should be interpreted as rolling windows anchored to `now()` (server timezone).
- “Daily” pipelines typically run for `yesterday` (date-only) to avoid partial-day metrics.

