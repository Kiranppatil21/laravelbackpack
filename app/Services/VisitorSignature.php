<?php

namespace App\Services;

class VisitorSignature
{
    /**
     * Create HMAC signature for a payload using timestamp.
     * Message format: timestamp|payload
     */
    public static function sign(string $payload, string $secret, ?string $timestamp = null): array
    {
        $ts = $timestamp ?? (string) time();
        $message = $ts . '|' . $payload;
        $sig = hash_hmac('sha256', $message, $secret);

        return ['timestamp' => $ts, 'signature' => $sig];
    }

    /**
     * Verify an incoming signature against payload and timestamp.
     */
    public static function verify(string $payload, string $timestamp, string $signature, string $secret): bool
    {
        $message = $timestamp . '|' . $payload;
        $expected = hash_hmac('sha256', $message, $secret);
        return hash_equals($expected, $signature);
    }
}
