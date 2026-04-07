# Meta Lead Gen Webhooks (Facebook Lead Ads)

This is required if you want DC OS to ingest new leads in near real-time from Meta Lead Ads.

## Platform Approval & Setup (Meta for Developers)

1. In your Meta Developer App, add the **Webhooks** product.
2. Choose the object: **Page**.
3. Subscribe to the field: **leadgen**.
4. Configure the webhook endpoint:
   - **Callback URL**: `https://YOUR_DOMAIN/webhooks/facebook`
   - **Verify Token**: set your own secure token (do not rely on defaults)
5. Put the app in Live mode after completing App Review and permissions approvals.

## Required Env Vars (DC OS)

- `META_VERIFY_TOKEN` (required)
- `META_APP_ID`
- `META_APP_SECRET`

## How Verification Works

- Meta will call the verification endpoint with `hub_mode`, `hub_verify_token`, and `hub_challenge`.
- DC OS will only verify if `META_VERIFY_TOKEN` is configured and matches exactly.

## Lead Delivery Flow in DC OS

1. Meta sends a signed webhook POST to `/webhooks/facebook`.
2. DC OS validates the request signature (required outside local/testing).
3. DC OS enqueues processing to pull full lead payload and map it to campaigns/ad sets/ads.
4. Data is stored to:
   - `facebook_leads`
   - main `leads` module
5. Workflow automations may trigger (email/webhooks/tasks/etc).

## Notes

- Use HTTPS and a publicly reachable domain (for local development use a tunnel like ngrok).
- If you rotate Meta app secrets, also rotate `META_VERIFY_TOKEN` and re-verify the webhook configuration.

