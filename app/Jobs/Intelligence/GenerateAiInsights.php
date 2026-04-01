<?php

namespace App\Jobs\Intelligence;

use App\Models\PerformanceAnomaly;
use App\Models\Client;
use App\Services\Intelligence\AiInsightsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateAiInsights implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->onQueue('intelligence');
    }

    /**
     * Execute the job.
     */
    public function handle(AiInsightsService $aiService): void
    {
        Log::info("GenerateAiInsights job started.");

        // Groups anomalies by client to reduce API calls
        $unprocessedClientIds = PerformanceAnomaly::whereNull('resolved_at')
            ->whereDoesntHave('aiInsight')
            ->distinct()
            ->pluck('client_id');

        foreach ($unprocessedClientIds as $clientId) {
            $client = Client::find($clientId);
            if (!$client) continue;

            try {
                $aiService->generateForClient($clientId, $client->organization_id, today()->toDateString());
            } catch (\Exception $e) {
                Log::error("Failed AI Insight generation for client {$clientId}: " . $e->getMessage());
            }
        }

        Log::info("GenerateAiInsights job completed. Clients processed: " . $unprocessedClientIds->count());
    }
}
