<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Stancl\Tenancy\Database\Models\Tenant;
use App\Models\TenantSubscription;

class SignupFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_signup_creates_tenant_and_redirects_to_stripe()
    {
        Config::set('queue.default', 'sync');

        // bind a fake Stripe client into the container
        $fake = new class {
            public $customers;
            public $checkout;
            public function __construct()
            {
                $this->customers = new class {
                    public function create($data)
                    {
                        $c = new \stdClass();
                        $c->id = 'cus_fake_1';
                        return $c;
                    }
                };

                $this->checkout = new class {
                    public $sessions;
                    public function __construct()
                    {
                        $this->sessions = new class {
                            public function create($params)
                            {
                                $s = new \stdClass();
                                $s->url = 'https://stripe.fake/checkout';
                                $s->id = 'cs_fake_1';
                                return $s;
                            }
                        };
                    }
                };
            }
        };

        $this->app->instance(\Stripe\StripeClient::class, $fake);

        $response = $this->post('/signup', [
            'name' => 'ACME Inc',
            'domain' => 'acme.test',
            'admin_name' => 'Admin',
            'admin_email' => 'admin@acme.test',
            'price_id' => null,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('https://stripe.fake/checkout');

        // tenant should have been created centrally
        $this->assertDatabaseHas('tenants', ['name' => 'ACME Inc', 'domain' => 'acme.test']);
    }

    public function test_complete_flow_creates_subscription_and_activates_tenant()
    {
        Config::set('queue.default', 'sync');

        // create a tenant via DB (simulate pre-signup state)
        $tenantId = \Illuminate\Support\Facades\DB::table('tenants')->insertGetId([
            'name' => 'FlowTenant',
            'domain' => 'flow.test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tenant = Tenant::find($tenantId);

        $payload = [
            'id' => 'evt_cs_1',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_fake_1',
                    'metadata' => ['tenant_id' => $tenant->getKey()],
                    'subscription' => 'sub_fake_1',
                    'customer' => 'cus_fake_1',
                ],
            ],
        ];

        $response = $this->postJson('/stripe/webhook', $payload);
        $response->assertStatus(200);

        $this->assertDatabaseHas('tenant_subscriptions', [
            'tenant_id' => $tenant->getKey(),
            'subscription_id' => 'sub_fake_1',
            'stripe_customer_id' => 'cus_fake_1',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('tenants', ['id' => $tenant->getKey(), 'active' => 1]);
    }
}
