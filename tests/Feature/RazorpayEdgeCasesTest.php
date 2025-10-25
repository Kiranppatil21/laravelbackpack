<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RazorpayEdgeCasesTest extends TestCase
{
    use RefreshDatabase;

    public function test_signature_mismatch_returns_400()
    {
        // ensure webhook secret is set so signature verification runs
        putenv('RAZORPAY_WEBHOOK_SECRET=testing_secret');

        $payload = ['event' => 'payment.captured', 'payload' => ['payment' => ['entity' => ['id' => 'p1']]]];
        $response = $this->withHeaders(['X-Razorpay-Signature' => 'bad'])->postJson('/razorpay/webhook', $payload);
        $response->assertStatus(400);
    }

    public function test_duplicate_webhook_is_idempotent()
    {
        // ensure a tenant exists with id '1' because the test fake order uses receipt '1'
        \DB::table('tenants')->insert([
            'id' => 1,
            'name' => 'Test Tenant',
            'domain' => 'test.local',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // bind fake API so webhook processing can fetch order if needed
        $fake = new class {
            public $order;
            public function __construct()
            {
                $this->order = new class {
                    public function fetch($id)
                    {
                        return ['id' => $id, 'receipt' => '1'];
                    }
                };
            }
        };
        $this->app->instance('\Razorpay\Api\Api', $fake);

        $payload = ['event' => 'payment.captured', 'payload' => ['payment' => ['entity' => ['id' => 'dup_pay', 'order_id' => 'orderdup', 'amount' => 1000, 'currency' => 'INR']]]];

        $response1 = $this->postJson('/razorpay/webhook', $payload);
        $response1->assertStatus(200);

        $response2 = $this->postJson('/razorpay/webhook', $payload);
        $response2->assertStatus(200);

        $this->assertDatabaseCount('razorpay_payments', 1);
    }
}
