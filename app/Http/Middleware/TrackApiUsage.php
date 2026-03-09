<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TrackApiUsage
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        $this->logApiRequest($request, $response, $duration);

        return $response;
    }

    protected function logApiRequest(Request $request, Response $response, float $duration): void
    {
        $data = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
            'organization_id' => $request->user()?->organization_id,
            'status_code' => $response->getStatusCode(),
            'duration_ms' => round($duration, 2),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
        ];

        if ($duration > 1000) {
            Log::warning('Slow API request detected', $data);
        } else {
            Log::info('API request', $data);
        }
    }
}
