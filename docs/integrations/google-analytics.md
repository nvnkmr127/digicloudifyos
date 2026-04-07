# Google Analytics (GA4)

## What DC OS Pulls

- Daily GA4 metrics via the Google Analytics Data API.

## Platform Approval & Setup (Google Cloud)

1. Create (or select) a Google Cloud project.
2. Configure **OAuth consent screen**.
3. Create OAuth credentials:
   - Google Cloud Console → APIs & Services → Credentials → **Create Credentials** → **OAuth client ID**.
4. Add the OAuth redirect URI used by DC OS:
   - `https://YOUR_DOMAIN/integrations/oauth/google_analytics/callback`
5. Ensure the GA APIs are enabled for the project:
   - Google Analytics Admin API
   - Google Analytics Data API
6. In production, verify the app and request any required sensitive-scope verification if Google prompts for it.

## Required Env Vars (DC OS)

Set these in your deployment environment:

- `GOOGLE_CLIENT_ID`
- `GOOGLE_CLIENT_SECRET`

## Required OAuth Scopes

DC OS requests:

- `https://www.googleapis.com/auth/analytics.readonly`
- `openid`, `email`, `profile`

## Connect in DC OS (Step-by-Step)

1. Log in as an OWNER/ADMIN.
2. Go to Clients → select a client → **Integrations**.
3. Click **Connect Google Analytics**.
4. Choose the Google account that has access to the GA4 property.
5. Approve the permissions request.
6. After redirect back to DC OS, the connection will be saved under:
   - `client_channel_connections.channel_type = ga4`

## Notes

- If the Google user has access to multiple GA4 properties, DC OS will auto-discover the first property it can see and store the property id in the connection metadata.

