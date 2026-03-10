# Meta Developer App Setup Guide

To enable the Facebook Ads and Lead Gen integration, follow these steps to create and configure your Meta Developer App.

## 1. Create the App
1. Go to the [Meta for Developers](https://developers.facebook.com/) portal.
2. Click **My Apps** and select **Create App**.
3. Select **"Other"** as the use case.
4. Choose **"Business"** as the app type (required for Marketing API and Facebook Login for Business).
5. Give your app a name (e.g., `DC OS - Marketing Integration`) and associate it with your **Business Account**.

## 2. Configure Products
Add the following products to your app:

### A. Marketing API
- Basic configuration is required.
- You will primarily use this for syncing campaigns, ad sets, and insights.

### B. Facebook Login for Business
- Go to **Settings** > **Allowed Domains for the JavaScript SDK**.
- Go to **Settings** under "Facebook Login for Business".
- Add your **Valid OAuth Redirect URIs**:
  - `https://your-domain.com/ads/callback/meta` (Replace with your actual production or ngrok URL).

### C. Webhooks
- Select **"Page"** from the lookup dropdown.
- Click **"Subscribe to this object"**.
- **Callback URL**: `https://your-domain.com/webhooks/facebook`
- **Verify Token**: `dcos_lead_sync_secret` (Matches `META_VERIFY_TOKEN` in [.env](file:///Users/naveenadicharla/Documents/DC%20OS/.env)).
- Subscribe to the **"leadgen"** field.

**Processing Flow (Real-Time):**
1. **Event**: Meta detected a new lead and sends a POST request.
2. **Controller**: [FacebookWebhookController](file:///Users/naveenadicharla/Documents/DC%20OS/app/Http/Controllers/Webhooks/FacebookWebhookController.php#10-64) validates the source.
3. **Queue**: [ProcessFacebookLeadWebhook](file:///Users/naveenadicharla/Documents/DC%20OS/app/Jobs/ProcessFacebookLeadWebhook.php#18-80) job is dispatched.
4. **Sync**: [MetaAdsService](file:///Users/naveenadicharla/Documents/DC%20OS/app/Services/MetaAdsService.php#34-1084) pulls the full lead identity including hierarchy data.
5. **Persistence**: Saved to `facebook_leads` and main `leads` module.
6. **Automation**: [ProcessWorkflowAutomation](file:///Users/naveenadicharla/Documents/DC%20OS/app/Jobs/ProcessWorkflowAutomation.php#16-209) triggers rules (e.g., WhatsApp, Email).

### D. WhatsApp Business Platform (Optional)
- Add this product if you intend to send automated WhatsApp messages from the platform.

## 3. Storage & Permissions
Ensure you have the following permissions approved (use "App Review" when moving to live mode):
- `ads_management`
- `ads_read`
- `business_management`
- `pages_manage_ads` (for Lead Gen sync)
- `pages_show_list`
- `pages_read_engagement`

## 4. System Configuration
Update your [.env](file:///Users/naveenadicharla/Documents/DC%20OS/.env) file with the credentials found in **App Settings** > **Basic**:

```env
META_APP_ID=your_app_id
META_APP_SECRET=your_app_secret
META_REDIRECT_URI=https://your-domain.com/ads/callback/meta
META_VERIFY_TOKEN=dcos_lead_sync_secret
```

> [!IMPORTANT]
> For local development, use a tool like **ngrok** to expose your local server to the internet so Meta can send webhooks to your local machine. Update your `APP_URL` and `META_REDIRECT_URI` accordingly.
