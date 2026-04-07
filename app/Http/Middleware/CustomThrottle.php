<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class CustomThrottle
{
    public function handle(Request $request, Closure $next, string $limit = '60'): Response
    {
        $key = $this->resolveRequestSignature($request);

        if (RateLimiter::tooManyAttempts($key, $limit)) {
            $seconds = RateLimiter::availableIn($key);

            $requestId = $request->attributes->get('request_id');

            $meta = ['retry_after' => $seconds];
            if (is_string($requestId) && $requestId !== '') {
                $meta['request_id'] = $requestId;
            }

            return ApiResponse::error(
                'Too many requests. Please try again later.',
                [],
                $meta,
                429
            )->header('Retry-After', $seconds);
        }

        RateLimiter::hit($key, 60);

        $response = $next($request);

        if (is_object($response) && isset($response->headers)) {
            $response->headers->set('X-RateLimit-Limit', (string) $limit);
            $response->headers->set('X-RateLimit-Remaining', (string) RateLimiter::remaining($key, $limit));
        }

        return $response;
    }

    protected function resolveRequestSignature(Request $request): string
    {
        if ($user = $request->user()) {
            return 'api-user-'.$user->id;
        }

        return 'api-ip-'.$request->ip();
    }
}
