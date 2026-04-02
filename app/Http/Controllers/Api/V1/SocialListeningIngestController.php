<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SocialListeningEvent;
use Illuminate\Http\Request;

class SocialListeningIngestController extends Controller
{
    public function ingest(Request $request)
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

        $payload = $request->json()->all();
        if (! is_array($payload)) {
            abort(422, 'Invalid payload.');
        }

        $orgId = $payload['organization_id'] ?? null;
        $clientId = $payload['client_id'] ?? null;
        $sourceType = $payload['source_type'] ?? 'webhook';
        $events = $payload['events'] ?? [];

        if (! is_string($orgId) || ! is_string($clientId) || ! is_array($events)) {
            abort(422, 'Missing required fields.');
        }

        $stored = 0;

        foreach ($events as $event) {
            if (! is_array($event)) {
                continue;
            }
            $externalId = $event['external_id'] ?? null;
            if (! is_string($externalId) || $externalId === '') {
                continue;
            }

            $eventDate = $event['event_date'] ?? null;
            if (! is_string($eventDate) || $eventDate === '') {
                $eventDate = now()->toDateString();
            }

            SocialListeningEvent::updateOrCreate(
                [
                    'organization_id' => $orgId,
                    'client_id' => $clientId,
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

        return response()->json([
            'ok' => true,
            'stored' => $stored,
        ]);
    }
}
