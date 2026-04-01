<?php

namespace App\Services\Intelligence;

use App\Models\PerformanceAnomaly;
use App\Models\AiInsight;
use App\Models\PerformanceSnapshot;
use App\Models\Client;
use App\Services\Intelligence\Prompts\AnomalyInsightPrompt;
use App\Services\Intelligence\Prompts\OpportunityInsightPrompt;
use App\Exceptions\AiInsightsException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiInsightsService
{
    /**
     * Entry point to generate insights for a client.
     */
    public function generateForClient(string $clientId, string $orgId, string $date): void
    {
        $anomalies = PerformanceAnomaly::where('client_id', $clientId)
            ->whereDate('detected_at', $date)
            ->whereDoesntHave('aiInsight')
            ->get();

        if ($anomalies->isNotEmpty()) {
            try {
                $client = Client::find($clientId);
                
                $data = [
                    'client_name' => $client->name,
                    'industry' => $client->industry ?? 'Marketing',
                    'anomalies' => $anomalies->map(fn($a) => [
                        'channel_type' => $a->channel_type,
                        'metric_name' => $a->metric_name,
                        'baseline' => (float) $a->baseline_value,
                        'current' => (float) $a->current_value,
                        'deviation' => (float) $a->deviation_percentage,
                    ])->toArray()
                ];

                $prompt = AnomalyInsightPrompt::build($data);
                $response = $this->callAiProvider($prompt);

                if ($response && is_array($response)) {
                    $this->parseAndPersistInsights($response, $clientId, $orgId, $date);
                } else {
                    $this->fallbackInsights($clientId, $orgId, $date, $anomalies);
                }
            } catch (\Exception $e) {
                Log::error("AiInsights generation failed for client {$clientId}: " . $e->getMessage());
                $this->fallbackInsights($clientId, $orgId, $date, $anomalies);
            }
        }

        // Also generate growth opportunities
        $this->generateOpportunities($clientId, $orgId, $date);
    }

    /**
     * [TASK-032] Separate AI call for growth opportunities.
     */
    public function generateOpportunities(string $clientId, string $orgId, string $date): void
    {
        $snapshots = PerformanceSnapshot::where('client_id', $clientId)
            ->where('snapshot_date', '>=', now()->subDays(7)->toDateString())
            ->orderBy('snapshot_date', 'desc')
            ->limit(21) // 7 days * 3 channels approx
            ->get();

        if ($snapshots->isEmpty()) return;

        try {
            $client = Client::find($clientId);
            $data = [
                'client_name' => $client->name,
                'industry' => $client->industry ?? 'Marketing',
                'snapshots' => $snapshots->map(fn($s) => [
                    'channel_type' => $s->channel_type,
                    'snapshot_date' => $s->snapshot_date->toDateString(),
                    'spend' => (float) $s->spend,
                    'conversions' => (float) $s->conversions,
                    'roas' => (float) $s->roas,
                ])->toArray()
            ];

            $prompt = OpportunityInsightPrompt::build($data);
            $response = $this->callAiProvider($prompt);

            if ($response && is_array($response)) {
                $this->parseAndPersistInsights($response, $clientId, $orgId, $date);
            }
        } catch (\Exception $e) {
            Log::warning("Growth opportunity analysis failed for client {$clientId}: " . $e->getMessage());
        }
    }

    /**
     * [TASK-062] Fallback if AI call fails.
     */
    protected function fallbackInsights(string $clientId, string $orgId, string $date, $anomalies): void
    {
        foreach ($anomalies as $anomaly) {
            AiInsight::create([
                'organization_id' => $orgId,
                'client_id' => $clientId,
                'anomaly_id' => $anomaly->id,
                'insight_date' => $date,
                'priority' => $anomaly->severity,
                'category' => 'issue',
                'title' => 'System Audit Required',
                'issue_description' => "Anomaly detected: {$anomaly->anomaly_type} in {$anomaly->channel_type} for {$anomaly->metric_name}.",
                'recommended_action' => "Manually investigate why {$anomaly->metric_name} shifted from " . round($anomaly->baseline_value, 2) . " to " . round($anomaly->current_value, 2),
                'expected_impact' => 'medium',
                'effort_level' => 'low',
            ]);
        }
    }

    protected function callAiProvider(string $prompt): ?array
    {
        $provider = config('intelligence.ai_provider', 'gemini');
        
        if ($provider === 'gemini') {
            return $this->callGemini($prompt);
        }
        
        if ($provider === 'openai') {
            return $this->callOpenAi($prompt);
        }
        
        return null;
    }

    protected function callGemini(string $prompt): ?array
    {
        $apiKey = config('intelligence.gemini_api_key');
        if (!$apiKey) {
            throw new AiInsightsException("Gemini API key not configured.");
        }

        $model = "gemini-1.5-flash";
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $response = Http::post($url, [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
            ]
        ]);

        if ($response->failed()) {
            throw new AiInsightsException("Gemini API call failed: " . $response->status() . " - " . $response->body());
        }

        $result = $response->json();
        $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
        
        if (!$text) return null;

        return json_decode($text, true);
    }

    protected function callOpenAi(string $prompt): ?array
    {
        $apiKey = config('intelligence.openai_api_key');
        if (!$apiKey) return null;

        $response = Http::withToken($apiKey)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a senior marketing performance analyst. Return ONLY JSON.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'response_format' => ['type' => 'json_object']
        ]);

        if ($response->failed()) return null;

        $result = $response->json();
        $text = $result['choices'][0]['message']['content'] ?? null;
        
        return $text ? json_decode($text, true) : null;
    }

    protected function parseAndPersistInsights(array $insights, string $clientId, string $orgId, string $date): void
    {
        // Handle both single object and list responses from AI
        if (isset($insights['insights'])) {
            $insights = $insights['insights'];
        }

        foreach ($insights as $insight) {
            AiInsight::create([
                'organization_id' => $orgId,
                'client_id' => $clientId,
                'anomaly_id' => $insight['anomaly_id'] ?? null,
                'insight_date' => $date,
                'priority' => $insight['priority'] ?? 'medium',
                'category' => $insight['category'] ?? 'issue',
                'title' => $insight['title'],
                'issue_description' => $insight['issue_description'],
                'root_cause' => $insight['root_cause'] ?? null,
                'recommended_action' => $insight['recommended_action'],
                'expected_impact' => $insight['expected_impact'] ?? 'medium',
                'effort_level' => $insight['effort_level'] ?? 'medium',
                'raw_ai_response' => $insight,
            ]);
        }
    }
}
