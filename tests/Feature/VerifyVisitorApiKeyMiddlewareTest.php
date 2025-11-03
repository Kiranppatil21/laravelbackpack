<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Http\Request;

class VerifyVisitorApiKeyMiddlewareTest extends TestCase
{
    public function test_signed_request_within_ttl_allowed()
    {
        // set env before constructing middleware (middleware caches env in ctor)
        putenv('VISITOR_HMAC_SECRET=testsecret');
        putenv('VISITOR_HMAC_TTL=300');

        $timestamp = (string) time();
        $payload = json_encode(['hello' => 'world']);
        $message = $timestamp . '|' . $payload;
        $sig = hash_hmac('sha256', $message, 'testsecret');

        $request = Request::create('/', 'POST', [], [], [], [
            'HTTP_X_VISITOR_TIMESTAMP' => $timestamp,
            'HTTP_X_VISITOR_SIGNATURE' => $sig,
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $middleware = new \App\Http\Middleware\VerifyVisitorApiKey();

        $response = $middleware->handle($request, function ($req) {
            return response('ok', 200);
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_old_timestamp_rejected()
    {
        putenv('VISITOR_HMAC_SECRET=testsecret');
        putenv('VISITOR_HMAC_TTL=300');

        $timestamp = (string) (time() - 1000);
        $payload = json_encode([]);
        $message = $timestamp . '|' . $payload;
        $sig = hash_hmac('sha256', $message, 'testsecret');

        $request = Request::create('/', 'POST', [], [], [], [
            'HTTP_X_VISITOR_TIMESTAMP' => $timestamp,
            'HTTP_X_VISITOR_SIGNATURE' => $sig,
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $middleware = new \App\Http\Middleware\VerifyVisitorApiKey();

        $response = $middleware->handle($request, function ($req) {
            return response('ok', 200);
        });

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertStringContainsString('Timestamp outside allowed window', $response->getContent());
    }

    public function test_malformed_timestamp_rejected()
    {
        putenv('VISITOR_HMAC_SECRET=testsecret');
        putenv('VISITOR_HMAC_TTL=300');

        $timestamp = 'notanint';
        $payload = json_encode([]);
        $message = $timestamp . '|' . $payload;
        $sig = hash_hmac('sha256', $message, 'testsecret');

        $request = Request::create('/', 'POST', [], [], [], [
            'HTTP_X_VISITOR_TIMESTAMP' => $timestamp,
            'HTTP_X_VISITOR_SIGNATURE' => $sig,
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $middleware = new \App\Http\Middleware\VerifyVisitorApiKey();

        $response = $middleware->handle($request, function ($req) {
            return response('ok', 200);
        });

        $this->assertEquals(401, $response->getStatusCode());
    }
}
