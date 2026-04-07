# Twitter / X

## What DC OS Pulls

- Basic account identity and supports future extensions for organic metrics ingestion.

## Platform Approval & Setup (X Developer Portal)

1. Create an app in the X Developer Portal.
2. Enable OAuth 2.0 (Authorization Code with PKCE).
3. Add the OAuth redirect URI used by DC OS:
   - `https://YOUR_DOMAIN/integrations/oauth/twitter/callback`
4. Ensure the app has the requested scopes approved/enabled.

## Required Env Vars (DC OS)

- `TWITTER_CLIENT_ID`
- `TWITTER_CLIENT_SECRET`

## Required OAuth Scopes

DC OS requests:

- `tweet.read`
- `users.read`
- `offline.access`

## Connect in DC OS (Step-by-Step)

1. Log in as an OWNER/ADMIN.
2. Go to Clients → select a client → **Integrations**.
3. Click **Connect Twitter/X**.
4. Approve access on X.
5. After redirect, the connection will be saved under:
   - `client_channel_connections.channel_type = twitter`

