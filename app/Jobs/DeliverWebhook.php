<?php

namespace App\Jobs;

use App\Models\Webhook;
use App\Services\WebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeliverWebhook implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 60;

    public $backoff = [60, 300, 900];

    public function __construct(
        public Webhook $webhook,
        public string $event,
        public array $payload
    ) {
        $this->onQueue('webhooks');
    }

    public function uniqueId(): string
    {
        return $this->webhook->id.':'.$this->event.':'.md5(json_encode($this->payload));
    }

    public function handle(WebhookService $webhookService): void
    {
        $webhookService->deliver($this->webhook, $this->event, $this->payload);
    }
}
