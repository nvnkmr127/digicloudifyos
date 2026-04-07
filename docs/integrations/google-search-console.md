# Google Search Console

## What DC OS Pulls

- Daily Search Analytics metrics (clicks, impressions, CTR, position) via the Search Console API.

## Platform Approval & Setup (Google Cloud)

1. Create (or select) a Google Cloud project.
2. Configure **OAuth consent screen**.
3. Create OAuth credentials (OAuth client ID).
4. Add the OAuth redirect URI used by DC OS:
   - `https://YOUR_DOMAIN/integrations/oauth/google_search_console/callback`
5. Enable the Search Console API for the project.

## Required Env Vars (DC OS)

- `GOOGLE_CLIENT_ID`
- `GOOGLE_CLIENT_SECRET`

## Required OAuth Scopes

DC OS requests:

- `https://www.googleapis.com/auth/webmasters.readonly`
- `openid`, `email`, `profile`

## Connect in DC OS (Step-by-Step)

1. Log in as an OWNER/ADMIN.
2. Go to Clients → select a client → **Integrations**.
3. Click **Connect Search Console**.
4. Select the Google account that has access to the Search Console property.
5. Approve permissions.
6. After redirect, the connection will be saved under:
   - `client_channel_connections.channel_type = google_search_console`

## Notes

- DC OS will try to auto-discover the first accessible site URL and store it as the account id/name.

