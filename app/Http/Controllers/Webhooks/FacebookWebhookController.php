<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessFacebookLeadWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FacebookWebhookController extends Controller
{
    /**
     * Verify Webhook (GET request from Facebook).
     */
    public function verify(Request $request)
    {
        $verifyToken = config('services.facebook.webhook_verify_token', 'dcos_lead_sync_secret');
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            Log::info('Facebook Webhook Verified Successfully');
            return response($challenge, 200);
        }

        Log::warning('Facebook Webhook Verification Failed', [
            'received_token' => $token,
            'mode' => $mode
        ]);

        return response('Unauthorized', 403);
    }

    /**
     * Handle Incoming Event (POST request from Facebook).
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::info('Facebook Webhook Payload Received', ['object' => $payload['object'] ?? 'unknown']);

        if (($payload['object'] ?? '') === 'page') {
            foreach ($payload['entry'] ?? [] as $entry) {
                foreach ($entry['changes'] ?? [] as $change) {
                    if (($change['field'] ?? '') === 'leadgen') {
                        $leadgenId = $change['value']['leadgen_id'] ?? null;
                        $pageId = $change['value']['page_id'] ?? null;
                        $formId = $change['value']['form_id'] ?? null;

                        if ($leadgenId) {
                            Log::info('New Facebook Lead Webhook detected', ['leadgen_id' => $leadgenId]);
                            ProcessFacebookLeadWebhook::dispatch($leadgenId, $pageId, $formId);
                        }
                    }
                }
            }
        }

        return response('EVENT_RECEIVED', 200);
    }
}
