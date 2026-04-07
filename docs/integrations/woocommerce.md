# WooCommerce

## What DC OS Pulls

- Daily sales metrics via WooCommerce REST API.

## Platform Approval & Setup (WordPress / WooCommerce)

1. Ensure the WooCommerce REST API is available.
2. Create API keys:
   - WooCommerce → Settings → Advanced → REST API → Add key
3. Choose permissions:
   - Read is sufficient for reporting metrics.
4. Copy the generated:
   - Consumer Key
   - Consumer Secret

## Required Env Vars (DC OS)

No global env vars are required to connect a store. Credentials are stored per client.

Optional:

- `WOOCOMMERCE_DEFAULT_CURRENCY` (defaults to `USD`)

## Connect in DC OS (Step-by-Step)

1. Log in as an OWNER/ADMIN.
2. Go to Clients → select a client → **Integrations**.
3. In the WooCommerce section, enter:
   - Store URL (example: `https://store.example.com`)
   - Consumer Key
   - Consumer Secret
4. Click **Connect WooCommerce**.
5. DC OS stores the credential and enables daily sync under:
   - `client_channel_connections.channel_type = woocommerce`

## Notes

- For security, use HTTPS store URLs.

