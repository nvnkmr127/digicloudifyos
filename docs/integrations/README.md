# Integrations

This folder documents how to get platform approvals (OAuth/apps/permissions) and how to connect each platform to DC OS.

## How Connections Work in DC OS

- **Who can connect**: users with the **manage-organization** permission (typically OWNER/ADMIN).
- **Where to connect**: Clients → select a client → **Integrations** (`/clients/{client}/integrations`).
- **What gets created**:
  - `integration_credentials` (tokens/keys)
  - `client_channel_connections` (client ↔ channel mapping)
  - background sync jobs populate daily metrics and feed briefings/dashboards.

## OAuth Redirect URLs (Production)

DC OS uses these redirect routes:

- **OAuth integrations** (Google, Shopify, Meta organic, Twitter/X, LinkedIn organic)
  - `https://YOUR_DOMAIN/integrations/oauth/{provider}/callback`
  - Providers used by DC OS:
    - `google_analytics`
    - `google_search_console`
    - `google_merchant_center`
    - `google_business_profile`
    - `shopify`
    - `meta_organic`
    - `twitter`
    - `linkedin_organic`

- **Ads OAuth integrations** (Meta Ads, Google Ads, LinkedIn Ads)
  - `https://YOUR_DOMAIN/ads/callback/{platform}`
  - Platforms used by DC OS:
    - `meta`
    - `google`
    - `linkedin`

## Platform Guides

- [Google Analytics (GA4)](google-analytics.md)
- [Google Search Console](google-search-console.md)
- [Google Merchant Center](google-merchant-center.md)
- [Google Business Profile](google-business-profile.md)
- [Meta Organic (Facebook/Instagram)](meta-organic.md)
- [Meta Ads](meta-ads.md)
- [Meta Lead Gen Webhooks](meta-leadgen-webhooks.md)
- [Shopify](shopify.md)
- [WooCommerce](woocommerce.md)
- [Amazon Selling Partner API (SP-API)](amazon-sp-api.md)
- [Twitter/X](twitter-x.md)
- [LinkedIn Organic](linkedin-organic.md)
- [Google Ads](google-ads.md)
- [LinkedIn Ads](linkedin-ads.md)

## Troubleshooting (Quick)

- **Redirect URI mismatch**: confirm the exact callback URL registered in the provider matches your DC OS domain and path.
- **Missing permissions**: many providers require app review / advanced access before production data can be pulled.
- **Support correlation**: include the `X-Request-Id` response header (or `meta.request_id` in API errors) when reporting issues.

