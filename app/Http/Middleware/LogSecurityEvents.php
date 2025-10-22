<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\SecurityLogger;

class LogSecurityEvents
{
    /**
     * Handle an incoming request and log security-relevant events.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log 403 Forbidden responses (authorization failures)
        if ($response->getStatusCode() === 403 && auth()->check()) {
            SecurityLogger::logAuthorizationFailure(
                auth()->id(),
                $request->method(),
                $request->path()
            );
        }

        // Log 429 Too Many Requests (rate limit exceeded)
        if ($response->getStatusCode() === 429) {
            SecurityLogger::logRateLimitExceeded($request->path());
        }

        return $response;
    }
}
