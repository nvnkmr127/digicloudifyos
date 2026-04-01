<?php

namespace App\Console\Commands\Intelligence;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestAiConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'intelligence:test-ai';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the configured AI provider connectivity and response latency.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $provider = config('intelligence.ai_provider', 'gemini');
        $apiKey = match ($provider) {
            'gemini' => config('intelligence.gemini_api_key'),
            'openai' => config('intelligence.openai_api_key'),
            default => null,
        };

        $this->info("Testing AI Connectivity: {$provider}");

        if (!$apiKey) {
            $this->error("API Key not found in config/intelligence.php or .env");
            return;
        }

        $prompt = "Hello AI, respond with 'Neural Link Active' if you receive this message.";

        if ($provider !== 'gemini') {
            $this->error("Provider [{$provider}] is not supported by this command yet.");
            return;
        }

        $this->comment("Calling Gemini API...");
        $start = microtime(true);

        $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ]
        ]);

        $duration = round(microtime(true) - $start, 2);

        if ($response->successful()) {
            $this->info("Success! Response time: {$duration}s");
            $this->comment("Response: " . $response->json()['candidates'][0]['content']['parts'][0]['text']);
        } else {
            $this->error("Connection Failed (HTTP {$response->status()})");
            $this->error($response->body());
        }
    }
}
