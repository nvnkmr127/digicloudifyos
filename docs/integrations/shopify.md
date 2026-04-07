# Shopify

## What DC OS Pulls

- Daily orders/revenue metrics via Shopify Admin API.

## Platform Approval & Setup (Shopify Admin)

1. In Shopify Admin, create an app:
   - Settings → Apps and sales channels → Develop apps (or use your partner app).
2. Configure OAuth:
   - Set the App URL to your DC OS domain.
   - Add the redirect URI used by DC OS:
     - `https://YOUR_DOMAIN/integrations/oauth/shopify/callback`
3. Configure required scopes (API access):
   - DC OS default: `read_orders,read_products`
   - Add additional scopes only if you need more data.
4. Install the app on the Shopify store to grant access.

## Required Env Vars (DC OS)

- `SHOPIFY_CLIENT_ID`
- `SHOPIFY_CLIENT_SECRET`
- `SHOPIFY_SCOPES` (optional, default `read_orders,read_products`)

## Connect in DC OS (Step-by-Step)

1. Log in as an OWNER/ADMIN.
2. Go to Clients → select a client → **Integrations**.
3. In the Shopify section, enter the shop domain:
   - `your-store.myshopify.com`
4. Click **Connect Shopify**.
5. Approve the app install/permissions in Shopify.
6. After redirect, the connection will be saved under:
   - `client_channel_connections.channel_type = shopify`

## Notes

- DC OS enforces `*.myshopify.com` on callback and blocks malformed domains.

