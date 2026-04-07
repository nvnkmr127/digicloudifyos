# Meta Organic (Facebook/Instagram)

## What DC OS Pulls

- Page-level organic metrics and insights for Facebook and Instagram (where linked).

## Platform Approval & Setup (Meta for Developers)

1. Create a Meta Developer App (Business type recommended).
2. Add **Facebook Login** (or Facebook Login for Business).
3. Add the OAuth redirect URI used by DC OS:
   - `https://YOUR_DOMAIN/integrations/oauth/meta_organic/callback`
4. Ensure your app has the required permissions and completes App Review as needed.

## Required Env Vars (DC OS)

- `META_APP_ID`
- `META_APP_SECRET`

## Required OAuth Scopes

DC OS requests:

- `pages_show_list`
- `pages_read_engagement`
- `pages_read_user_content`
- `read_insights`
- `instagram_basic`
- `instagram_manage_insights`
- plus `email`, `public_profile`

## Connect in DC OS (Step-by-Step)

1. Log in as an OWNER/ADMIN.
2. Go to Clients → select a client → **Integrations**.
3. Click **Connect Facebook/Instagram (Organic)**.
4. Choose the Meta account that has access to the Page.
5. Approve permissions.
6. After redirect, DC OS will store:
   - `client_channel_connections.channel_type = facebook_organic` (Page)
   - `client_channel_connections.channel_type = instagram` (if an IG business account is attached)

## Notes

- DC OS will select the first Page returned by `/me/accounts` and store its id/name.
- For lead capture, also configure Meta Lead Gen webhooks (separate doc).

