<?php

namespace App\Services;

use App\Jobs\DeliverWebhook;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    public function triggerEvent(string $organizationId, string $event, array $payload): void
    {
        $webhooks = Webhook::where('organization_id', $organizationId)
            ->where('active', true)
            ->get();

        foreach ($webhooks as $webhook) {
            if ($webhook->shouldTriggerForEvent($event)) {
                DeliverWebhook::dispatch($webhook, $event, $payload);
            }
        }
    }

    public function deliver(Webhook $webhook, string $event, array $payload): WebhookDelivery
    {
        $delivery = WebhookDelivery::create([
            'webhook_id' => $webhook->id,
            'event' => $event,
            'payload' => $payload,
        ]);

        try {
            $signature = $this->generateSignature($payload, $webhook->secret);

            $headers = array_merge($webhook->headers ?? [], [
                'X-Webhook-Signature' => $signature,
                'X-Webhook-Event' => $event,
                'Content-Type' => 'application/json',
            ]);

            $response = \Illuminate\Support\Facades\Http::withHeaders($headers)
                ->timeout(30)
                ->post($webhook->url, $payload);

            $delivery->update([
                'response_status' => $response->status(),
                'response_body' => $response->body(),
                'delivered_at' => now(),
            ]);

            if (! $response->successful()) {
                throw new \Exception("HTTP {$response->status()}: {$response->body()}");
            }

            Log::info('Webhook delivered successfully', [
                'webhook_id' => $webhook->id,
                'event' => $event,
                'delivery_id' => $delivery->id,
            ]);

        } catch (\Exception $e) {
            $delivery->update([
                'failed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);

            Log::error('Webhook delivery failed', [
                'webhook_id' => $webhook->id,
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
        }

        return $delivery;
    }

    protected function generateSignature(array $payload, ?string $secret): string
    {
        if (! $secret) {
            return '';
        }

        return hash_hmac('sha256', json_encode($payload), $secret);
    }
}
