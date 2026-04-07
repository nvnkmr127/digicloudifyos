# Google Merchant Center

## What DC OS Pulls

- Merchant account information and (where enabled) commerce performance inputs used for reporting/briefings.

## Platform Approval & Setup (Google Cloud)

1. Create (or select) a Google Cloud project.
2. Configure **OAuth consent screen**.
3. Create OAuth credentials (OAuth client ID).
4. Add the OAuth redirect URI used by DC OS:
   - `https://YOUR_DOMAIN/integrations/oauth/google_merchant_center/callback`
5. Enable the Content API for Shopping.

## Required Env Vars (DC OS)

- `GOOGLE_CLIENT_ID`
- `GOOGLE_CLIENT_SECRET`

## Required OAuth Scopes

DC OS requests:

- `https://www.googleapis.com/auth/content`
- `openid`, `email`, `profile`

## Connect in DC OS (Step-by-Step)

1. Log in as an OWNER/ADMIN.
2. Go to Clients → select a client → **Integrations**.
3. Click **Connect Merchant Center**.
4. Choose a Google account that has access to the Merchant Center account.
5. Approve permissions.
6. After redirect, the connection will be saved under:
   - `client_channel_connections.channel_type = google_merchant_center`

## Notes

- DC OS will attempt to discover the first accessible Merchant account id and store it in the connection.

