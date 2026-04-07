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
        $verifyToken = config('services.facebook.webhook_verify_token');
        if (! is_string($verifyToken) || $verifyToken === '') {
            return response('Webhook not configured', 503);
        }

        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe' && is_string($token) && hash_equals($verifyToken, $token)) {
            Log::info('Facebook Webhook Verified Successfully');

            return response($challenge, 200);
        }

        Log::warning('Facebook Webhook Verification Failed', [
            'mode' => $mode,
        ]);

        return response('Unauthorized', 403);
    }

    /**
     * Handle Incoming Event (POST request from Facebook).
     */
    public function handle(Request $request)
    {
        // 1. Verify Payload Signature
        $signature = $request->header('X-Hub-Signature-256');
        $appSecret = config('services.facebook.client_secret');

        if ($signature && $appSecret) {
            $expectedSignature = 'sha256='.hash_hmac('sha256', $request->getContent(), $appSecret);
            if (! hash_equals($expectedSignature, $signature)) {
                Log::warning('Facebook Webhook Signature Verification Failed');

                return response('Invalid Signature', 401);
            }
        } else {
            if (! app()->environment(['local', 'testing'])) {
                Log::warning('Facebook Webhook Missing Signature');

                return response('Missing Signature', 401);
            }
        }

        // 2. Process Payload
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
