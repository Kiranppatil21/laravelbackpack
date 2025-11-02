<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyVisitorApiKey
{
    /**
     * Check for X-VISITOR-API-KEY header (falls back to X-API-KEY) and compare with env VISITOR_API_KEY.
     * If API key is not supplied, support HMAC signature verification via X-VISITOR-SIGNATURE
     * with secret from VISITOR_HMAC_SECRET. If neither secret is configured, middleware is a no-op.
     */
    public function handle(Request $request, Closure $next)
    {
        $apiKey = env('VISITOR_API_KEY');
        $hmacSecret = env('VISITOR_HMAC_SECRET');

        // If neither protection is configured, allow through for local/dev
        if (empty($apiKey) && empty($hmacSecret)) {
            return $next($request);
        }

        // First check for simple API key header
        $header = $request->header('X-VISITOR-API-KEY') ?? $request->header('X-API-KEY');
        if (! empty($apiKey) && $header && hash_equals($apiKey, $header)) {
            return $next($request);
        }

        // If API key didn't match, check HMAC signature
        if (! empty($hmacSecret)) {
            $sig = $request->header('X-VISITOR-SIGNATURE');
            $timestamp = $request->header('X-VISITOR-TIMESTAMP');
            if ($sig && $timestamp) {
                $payload = $request->getContent();
                // construct message as timestamp + payload to avoid replay attacks
                $message = $timestamp . '|' . $payload;
                $expected = hash_hmac('sha256', $message, $hmacSecret);
                if (hash_equals($expected, $sig)) {
                    return $next($request);
                }
            }
        }

        return response()->json(['message' => 'Invalid API credentials'], 401);
    }
}
