<?php

namespace App\Jobs\Intelligence;

use App\Models\Client;
use App\Models\PerformanceAnomaly;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
    public function handle(): void
    {
        $date = now()->subDay()->toDateString();

        // Groups anomalies by client to reduce API calls
        $unprocessedClientIds = PerformanceAnomaly::whereNull('resolved_at')
            ->whereDate('detected_at', $date)
            ->whereDoesntHave('aiInsight')
            ->distinct()
            ->pluck('client_id');

        $clients = Client::whereIn('id', $unprocessedClientIds)->get()->keyBy('id');

        foreach ($unprocessedClientIds as $clientId) {
            $client = $clients->get($clientId);
            if (! $client) {
                continue;
            }

            GenerateAiInsightsForClient::dispatch($client->organization_id, $clientId, $date);
        }
    }
}
