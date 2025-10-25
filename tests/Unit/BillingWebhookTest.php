<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stancl\Tenancy\Database\Models\Tenant;
use App\Models\TenantSubscription;

class BillingWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_session_completed_creates_subscription_record()
    {
        // ensure queue runs synchronously in tests so the job is processed immediately
        \Illuminate\Support\Facades\Config::set('queue.default', 'sync');
        // create a tenant row directly (avoid Tenant model casting differences in tests)
        $tenantId = \Illuminate\Support\Facades\DB::table('tenants')->insertGetId([
            'name' => 'TestTenant',
            'domain' => 'testtenant.local',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $tenant = Tenant::find($tenantId);

        // build a minimal checkout.session.completed payload
        $payload = [
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_123',
                    'metadata' => [
                        'tenant_id' => $tenant->getKey(),
                    ],
                    'subscription' => 'sub_test_123',
                    'customer' => 'cus_test_123',
                ],
            ],
        ];

        $response = $this->postJson('/stripe/webhook', $payload);
        $response->assertStatus(200);

        $this->assertDatabaseHas('tenant_subscriptions', [
            'tenant_id' => $tenant->getKey(),
            'subscription_id' => 'sub_test_123',
            'stripe_customer_id' => 'cus_test_123',
            'status' => 'active',
        ]);
    }

    public function test_subscription_updated_updates_record()
    {
        \Illuminate\Support\Facades\Config::set('queue.default', 'sync');
        $tenantId = \Illuminate\Support\Facades\DB::table('tenants')->insertGetId([
            'name' => 'T2',
            'domain' => 't2.local',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $tenant = Tenant::find($tenantId);
        TenantSubscription::create([
            'tenant_id' => $tenant->getKey(),
            'subscription_id' => 'sub_abc',
            'stripe_customer_id' => 'cus_old',
            'status' => 'trialing',
            'raw' => null,
        ]);

        $payload = [
            'type' => 'customer.subscription.updated',
            'data' => [
                'object' => [
                    'id' => 'sub_abc',
                    'status' => 'active',
                    'customer' => 'cus_new',
                    'items' => [
                        'data' => [
                            ['price' => ['id' => 'price_gold']]
                        ]
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/stripe/webhook', $payload);
        $response->assertStatus(200);

        $this->assertDatabaseHas('tenant_subscriptions', [
            'tenant_id' => $tenant->getKey(),
            'subscription_id' => 'sub_abc',
            'stripe_customer_id' => 'cus_new',
            'status' => 'active',
            'price_id' => 'price_gold',
        ]);
    }
}
