<?php

namespace App\Http\Middleware;

use App\Models\ApiRequestLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * After Middleware — API request logging.
 *
 * ApiRequestLogger runs after response to capture status code and duration
 */
class ApiRequestLogger
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        ApiRequestLog::create([
            'user_id'     => $request->user()?->id,
            'method'      => $request->method(),
            'path'        => $request->path(),
            'status_code' => $response->getStatusCode(),
            'duration_ms' => $durationMs,
            'ip'          => $request->ip(),
            'user_agent'  => $request->userAgent(),
        ]);

        return $response;
    }
}
