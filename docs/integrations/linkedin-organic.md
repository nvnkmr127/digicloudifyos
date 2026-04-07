# LinkedIn Organic

## What DC OS Pulls

- Organization-level identity and (where enabled) organic publishing/analytics foundations for reporting.

## Platform Approval & Setup (LinkedIn Developer Portal)

1. Create an app in the LinkedIn Developer Portal.
2. Configure OAuth 2.0 settings.
3. Add the OAuth redirect URI used by DC OS:
   - `https://YOUR_DOMAIN/integrations/oauth/linkedin_organic/callback`
4. Request the required products/permissions and complete any required review:
   - Many LinkedIn organization scopes require approval.

## Required Env Vars (DC OS)

- `LINKEDIN_CLIENT_ID`
- `LINKEDIN_CLIENT_SECRET`

## Required OAuth Scopes

DC OS requests:

- `r_liteprofile`
- `r_emailaddress`
- `r_organization_social`
- `rw_organization_admin`

## Connect in DC OS (Step-by-Step)

1. Log in as an OWNER/ADMIN.
2. Go to Clients → select a client → **Integrations**.
3. Click **Connect LinkedIn (Organic)**.
4. Approve access.
5. After redirect, the connection will be saved under:
   - `client_channel_connections.channel_type = linkedin_organic`

## Notes

- DC OS will look up organizations where the authenticated user is an approved administrator.

