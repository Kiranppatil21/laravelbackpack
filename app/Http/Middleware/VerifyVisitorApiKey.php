<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyVisitorApiKey
{
    /**
     * Check for X-VISITOR-API-KEY header (falls back to X-API-KEY) and compare with env VISITOR_API_KEY.
     */
    public function handle(Request $request, Closure $next)
    {
        $configured = env('VISITOR_API_KEY');

        // If not configured, allow through (no-op) so local/dev doesn't need the key.
        if (empty($configured)) {
            return $next($request);
        }

        $header = $request->header('X-VISITOR-API-KEY') ?? $request->header('X-API-KEY');

        if (! $header || ! hash_equals($configured, $header)) {
            return response()->json(['message' => 'Invalid API key'], 401);
        }

        return $next($request);
    }
}
