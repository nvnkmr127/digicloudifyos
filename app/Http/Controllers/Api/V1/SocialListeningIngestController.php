<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SocialListeningIngestRequest;
use App\Http\Responses\ApiResponse;
use App\Models\SocialListeningEvent;

class SocialListeningIngestController extends Controller
{
    public function ingest(SocialListeningIngestRequest $request)
    {
        $secret = config('services.social_listening.webhook_secret');
        if (! is_string($secret) || $secret === '') {
            abort(503, 'Social listening webhook not configured.');
        }

        $signature = $request->header('X-Signature');
        if (! is_string($signature) || $signature === '') {
            abort(401, 'Missing signature.');
        }

        $raw = $request->getContent();
        $expected = hash_hmac('sha256', $raw, $secret);

        if (! hash_equals($expected, $signature)) {
            abort(401, 'Invalid signature.');
        }

        $orgId = $request->input('organization_id');
        $clientId = $request->input('client_id');
        $sourceType = $request->input('source_type', 'webhook');
        $events = $request->input('events', []);

        $stored = 0;

        foreach ($events as $event) {
            if (! is_array($event)) {
                continue;
            }
            $externalId = (string) ($event['external_id'] ?? '');
            $eventDate = isset($event['event_date']) ? (string) $event['event_date'] : now()->toDateString();

            SocialListeningEvent::updateOrCreate(
                [
                    'organization_id' => (string) $orgId,
                    'client_id' => (string) $clientId,
                    'source_type' => (string) $sourceType,
                    'external_id' => $externalId,
                ],
                [
                    'client_competitor_id' => isset($event['client_competitor_id']) ? (string) $event['client_competitor_id'] : null,
                    'title' => isset($event['title']) ? (string) $event['title'] : null,
                    'url' => isset($event['url']) ? (string) $event['url'] : null,
                    'content' => isset($event['content']) ? (string) $event['content'] : null,
                    'author' => isset($event['author']) ? (string) $event['author'] : null,
                    'published_at' => isset($event['published_at']) ? (string) $event['published_at'] : null,
                    'event_date' => $eventDate,
                    'raw_data' => $event,
                ]
            );

            $stored++;
        }

        return ApiResponse::success([
            'stored' => $stored,
        ]);
    }
}
