# Google Ads

## What DC OS Pulls

- Ad account structure and performance insights for Google Ads.

## Platform Approval & Setup (Google Ads + Google Cloud)

1. Create (or select) a Google Cloud project and OAuth client.
2. Add the Ads OAuth redirect URI used by DC OS:
   - `https://YOUR_DOMAIN/ads/callback/google`
3. In Google Ads, obtain a Developer Token (required for API access).
4. Ensure the Google account you will connect has access to the Google Ads account.

## Required Env Vars (DC OS)

- `GOOGLE_CLIENT_ID`
- `GOOGLE_CLIENT_SECRET`
- `GOOGLE_ADS_DEVELOPER_TOKEN`

## Connect in DC OS (Step-by-Step)

1. Log in as an OWNER/ADMIN.
2. Go to Clients → select a client → **Integrations**.
3. In the Ads section, click **Connect Google Ads**.
4. Approve access in Google.
5. After redirect, DC OS will start initial sync jobs for structure and insights.

