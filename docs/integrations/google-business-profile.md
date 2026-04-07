# Google Business Profile

## What DC OS Pulls

- Location-level metrics and insights for Google Business Profile listings.

## Platform Approval & Setup (Google Cloud)

1. Create (or select) a Google Cloud project.
2. Configure **OAuth consent screen**.
3. Create OAuth credentials (OAuth client ID).
4. Add the OAuth redirect URI used by DC OS:
   - `https://YOUR_DOMAIN/integrations/oauth/google_business_profile/callback`
5. Enable these APIs for the project:
   - Google Business Profile APIs (account management + business information)

## Required Env Vars (DC OS)

- `GOOGLE_CLIENT_ID`
- `GOOGLE_CLIENT_SECRET`

## Required OAuth Scopes

DC OS requests:

- `https://www.googleapis.com/auth/business.manage`
- `openid`, `email`, `profile`

## Connect in DC OS (Step-by-Step)

1. Log in as an OWNER/ADMIN.
2. Go to Clients → select a client → **Integrations**.
3. Click **Connect Google Business Profile**.
4. Choose a Google account that manages the listing.
5. Approve permissions.
6. After redirect, the connection will be saved under:
   - `client_channel_connections.channel_type = google_business_profile`

## Notes

- DC OS will attempt to discover the first accessible account and location.

