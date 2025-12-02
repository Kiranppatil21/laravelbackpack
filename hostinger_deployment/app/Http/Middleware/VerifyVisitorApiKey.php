<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VerifyVisitorApiKey
{
    /**
     * Check for X-VISITOR-API-KEY header (falls back to X-API-KEY) and compare with env VISITOR_API_KEY.
     * If API key is not supplied, support HMAC signature verification via X-VISITOR-SIGNATURE
     * with secret from VISITOR_HMAC_SECRET. If neither secret is configured, middleware is a no-op.
     */
    /**
     * Cached secrets and TTL to avoid calling env() on every request.
     */
    protected $apiKey;
    protected $hmacSecret;
    protected $hmacTTL;

    public function __construct()
    {
        $this->apiKey = env('VISITOR_API_KEY');
        $this->hmacSecret = env('VISITOR_HMAC_SECRET');
        // TTL in seconds for timestamp freshness; default 5 minutes
        $this->hmacTTL = (int) env('VISITOR_HMAC_TTL', 300);
    }

    public function handle(Request $request, Closure $next)
    {
        // If neither protection is configured, allow through for local/dev
        if (empty($this->apiKey) && empty($this->hmacSecret)) {
            return $next($request);
        }

        // First check for simple API key header
        $header = $request->header('X-VISITOR-API-KEY') ?? $request->header('X-API-KEY');
        if (! empty($this->apiKey) && $header && hash_equals($this->apiKey, $header)) {
            return $next($request);
        }

        // If API key didn't match, check HMAC signature with freshness check
        if (! empty($this->hmacSecret)) {
            $sig = $request->header('X-VISITOR-SIGNATURE');
            $timestamp = $request->header('X-VISITOR-TIMESTAMP');
            if ($sig && $timestamp) {
                $sig = trim($sig);

                // timestamp must be an integer (seconds)
                if (! ctype_digit((string) $timestamp)) {
                    return response()->json(['message' => 'Invalid API credentials'], 401);
                }

                $timestampInt = (int) $timestamp;

                // reject requests with timestamps outside the allowed window to mitigate replay
                if (abs(time() - $timestampInt) > $this->hmacTTL) {
                    return response()->json(['message' => 'Timestamp outside allowed window'], 401);
                }

                $payload = $request->getContent();
                // construct message as timestamp + payload to avoid replay attacks
                $message = $timestamp . '|' . $payload;
                $expected = hash_hmac('sha256', $message, $this->hmacSecret);
                if (hash_equals($expected, $sig)) {
                    return $next($request);
                }
            }
        }

        return response()->json(['message' => 'Invalid API credentials'], 401);
    }
}
