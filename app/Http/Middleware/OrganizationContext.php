<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OrganizationContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $request->merge([
                'organization_id' => $request->user()->organization_id,
            ]);
        }

        return $next($request);
    }
}
