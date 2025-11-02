<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SignupProvisioningE2ETest extends TestCase
{
    use RefreshDatabase;

    public function test_signup_with_razorpay_then_webhook_activates_tenant()
    {
        // Bind a fake Razorpay API so the signup controller can create an order
        $fakeApi = new class
        {
            public $order;

            public function __construct()
            {
                $this->order = new class
                {
                    public function create($payload)
                    {
                        return ['id' => 'order_e2e_1', 'amount' => $payload['amount'] ?? 1000, 'receipt' => 1];
                    }

                    public function fetch($id)
                    {
                        // return the receipt that maps to the tenant id inserted in the signup flow
                        return ['id' => $id, 'receipt' => '1'];
                    }
                };
            }
        };
        $this->app->instance('\Razorpay\\Api\\Api', $fakeApi);

    // Force jobs to run synchronously within this test so background worker
    // resolution (which runs in a separate process) does not lose our
    // container binding for the fake Razorpay API.
    $this->app['config']->set('queue.default', 'sync');

        // Post to signup with razorpay gateway
        $response = $this->post('/signup', [
            'name' => 'E2E Tenant',
            'domain' => 'e2e.local',
            'admin_name' => 'E2E Admin',
            'admin_email' => 'admin@e2e.test',
            'gateway' => 'razorpay',
            'amount' => 10.00,
        ]);

        // signup should return a checkout view with order id (200 OK)
        $response->assertStatus(200);

        // Simulate Razorpay webhook payment.captured payload (no signature — webhook secret not set in tests)
        $payload = [
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_e2e_1',
                        'order_id' => 'order_e2e_1',
                        'amount' => 1000,
                        'currency' => 'INR',
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/razorpay/webhook', $payload);
        $response->assertStatus(200);

        // Payment row should exist
        $this->assertDatabaseHas('razorpay_payments', ['payment_id' => 'pay_e2e_1']);

        // The job should have run and activated the tenant (receipt used as tenant id)
        $this->assertDatabaseHas('tenant_subscriptions', ['subscription_id' => 'pay_e2e_1']);

        // The tenant should be marked active
        $this->assertDatabaseHas('tenants', ['active' => 1]);
    }
}
