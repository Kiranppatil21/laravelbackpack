<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Stancl\Tenancy\Database\Models\Tenant;

class RazorpayFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_signup_with_razorpay_shows_checkout()
    {
        Config::set('queue.default', 'sync');

        // Fake Razorpay Api instance
        $fake = new class {
            public $order;
            public function __construct()
            {
                $this->order = new class {
                    public function create($params)
                    {
                        return ['id' => 'order_fake_1', 'amount' => $params['amount'], 'currency' => $params['currency'], 'receipt' => $params['receipt']];
                    }
                    public function fetch($id)
                    {
                        return ['id' => $id, 'receipt' => null];
                    }
                };
            }
        };

        // Bind the fake to the container key used in the code
        $this->app->instance('\Razorpay\Api\Api', $fake);

        $response = $this->post('/signup', [
            'name' => 'RZ Inc',
            'domain' => 'rz.test',
            'admin_name' => 'Admin',
            'admin_email' => 'admin@rz.test',
            'gateway' => 'razorpay',
            'amount' => 49.99,
        ]);

        $response->assertStatus(200);
        $response->assertSee('checkout.razorpay.com');

        $this->assertDatabaseHas('tenants', ['name' => 'RZ Inc', 'domain' => 'rz.test']);
    }

    public function test_razorpay_webhook_payment_captured_activates_tenant()
    {
        Config::set('queue.default', 'sync');

        // create tenant
        $tenantId = \Illuminate\Support\Facades\DB::table('tenants')->insertGetId([
            'name' => 'RZFlow',
            'domain' => 'rzflow.test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Fake API responds to order fetch with receipt set to tenant id
        $fake = new class($tenantId) {
            public $order;
            public function __construct($tenantId)
            {
                $this->order = new class($tenantId) {
                    private $tenantId;
                    public function __construct($t)
                    {
                        $this->tenantId = $t;
                    }
                    public function fetch($id)
                    {
                        return ['id' => $id, 'receipt' => (string)$this->tenantId];
                    }
                };
            }
        };

        $this->app->instance('\Razorpay\Api\Api', $fake);

        $payload = [
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_fake_1',
                        'order_id' => 'order_fake_1',
                        'amount' => 4999,
                        'currency' => 'INR',
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/razorpay/webhook', $payload);
        $response->assertStatus(200);

        $this->assertDatabaseHas('razorpay_payments', ['payment_id' => 'pay_fake_1']);
        $this->assertDatabaseHas('tenant_subscriptions', ['subscription_id' => 'pay_fake_1']);
        $this->assertDatabaseHas('tenants', ['id' => $tenantId, 'active' => 1]);
    }
}
