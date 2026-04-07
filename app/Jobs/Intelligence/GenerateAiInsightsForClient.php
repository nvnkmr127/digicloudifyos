<?php

namespace App\Jobs\Intelligence;

use App\Services\Intelligence\AiInsightsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateAiInsightsForClient implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 300;

    public function __construct(
        public string $organizationId,
        public string $clientId,
        public string $date
    ) {
        $this->onQueue('intelligence');
    }

    public function handle(AiInsightsService $aiInsightsService): void
    {
        $aiInsightsService->generateForClient($this->clientId, $this->organizationId, $this->date);
    }
}

