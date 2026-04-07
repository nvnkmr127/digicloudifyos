# LinkedIn Ads

## What DC OS Pulls

- Ad account structure and performance insights for LinkedIn Ads.

## Platform Approval & Setup (LinkedIn Developer Portal)

1. Create an app in the LinkedIn Developer Portal.
2. Configure OAuth 2.0 settings.
3. Add the Ads OAuth redirect URI used by DC OS:
   - `https://YOUR_DOMAIN/ads/callback/linkedin`
4. Request required LinkedIn Marketing API permissions and complete any required review.

## Required Env Vars (DC OS)

- `LINKEDIN_CLIENT_ID`
- `LINKEDIN_CLIENT_SECRET`

## Connect in DC OS (Step-by-Step)

1. Log in as an OWNER/ADMIN.
2. Go to Clients → select a client → **Integrations**.
3. In the Ads section, click **Connect LinkedIn Ads**.
4. Approve access.
5. After redirect, DC OS will start initial sync jobs for structure and insights.

