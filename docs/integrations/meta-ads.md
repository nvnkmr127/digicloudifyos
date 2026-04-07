# Meta Ads

## What DC OS Pulls

- Ad account structure (campaigns, ad sets, ads, creatives)
- Insights and breakdowns used by the Ads analytics screens and briefings
- Lead gen hierarchy mapping when lead webhooks are enabled

## Platform Approval & Setup (Meta for Developers)

1. Create a Meta Developer App (Business type recommended).
2. Add products:
   - Marketing API
   - Facebook Login for Business
3. Add the OAuth redirect URI used by DC OS:
   - `https://YOUR_DOMAIN/ads/callback/meta`
4. Ensure required permissions are approved (App Review + business verification if required).

## Required Env Vars (DC OS)

- `META_APP_ID`
- `META_APP_SECRET`

## Connect in DC OS (Step-by-Step)

1. Log in as an OWNER/ADMIN.
2. Go to Clients → select a client → **Integrations**.
3. In the Ads section, click **Connect Meta Ads**.
4. Approve access for the ad account.
5. After redirect, DC OS will start initial sync jobs:
   - structure sync
   - insights sync (typically last 30 days)

## Notes

- Meta Lead Gen real-time lead pulls require a webhook subscription (separate doc).

