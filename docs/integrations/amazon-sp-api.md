# Amazon Selling Partner API (SP-API)

## What DC OS Pulls

- Daily marketplace metrics via Amazon SP-API.

## Platform Approval & Setup (Amazon)

Amazon SP-API requires multiple pieces:

1. Register as an Amazon SP-API developer (Seller Central / Developer Console).
2. Create a Login with Amazon (LWA) application:
   - Get **LWA Client ID** and **LWA Client Secret**.
3. Create an SP-API application authorization (refresh token):
   - Generate an **SP-API Refresh Token** for the seller.
4. Create AWS IAM credentials for SP-API:
   - Get **AWS Access Key ID** and **AWS Secret Access Key**.
5. Choose your marketplace and region settings:
   - Marketplace ID (example: `ATVPDKIKX0DER` for US)
   - SP-API endpoint (example: `https://sellingpartnerapi-na.amazon.com`)
   - AWS region (example: `us-east-1`)

## Required Env Vars (DC OS)

Global configuration for LWA:

- `AMAZON_LWA_CLIENT_ID`
- `AMAZON_LWA_CLIENT_SECRET`

Optional defaults:

- `AMAZON_SPAPI_AWS_REGION` (default `us-east-1`)
- `AMAZON_SPAPI_ENDPOINT` (default `https://sellingpartnerapi-na.amazon.com`)
- `AMAZON_SPAPI_MARKETPLACE_ID` (default `ATVPDKIKX0DER`)

## Connect in DC OS (Step-by-Step)

1. Log in as an OWNER/ADMIN.
2. Go to Clients → select a client → **Integrations**.
3. In the Amazon section, enter:
   - Seller ID (optional)
   - Marketplace ID
   - Endpoint
   - AWS Region
   - AWS Access Key ID
   - AWS Secret Access Key
   - LWA Refresh Token
4. Click **Connect Amazon**.
5. DC OS stores the credential and enables daily sync under:
   - `client_channel_connections.channel_type = amazon`

## Notes

- Keep AWS keys and refresh tokens secret and rotate them if exposed.

