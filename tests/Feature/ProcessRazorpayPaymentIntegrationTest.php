<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use App\Jobs\ProcessRazorpayPayment;

class ProcessRazorpayPaymentIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_processes_payment_and_activates_tenant()
    {
        // Create a tenant row we'll reference via the order receipt
    $tenantId = 1;
        DB::table('tenants')->insert([
            'id' => $tenantId,
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Test Tenant',
            'domain' => 'test-tenant.local',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert a raw razorpay_payments row with an order id
        $paymentId = 'pay_test_1';
        $orderId = 'order_test_1';
        $rowId = DB::table('razorpay_payments')->insertGetId([
            'payment_id' => $paymentId,
            'order_id' => $orderId,
            'amount' => 1000,
            'currency' => 'INR',
            'raw' => json_encode(['id' => $paymentId, 'order_id' => $orderId]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Bind a fake Razorpay API that returns an order with receipt => tenantId
        $fake = new class($tenantId) {
            public $order;
            public function __construct($tenantId)
            {
                $this->order = new class($tenantId) {
                    protected $tenantId;
                    public function __construct($tenantId)
                    {
                        $this->tenantId = $tenantId;
                    }
                        public function fetch($id)
                        {
                            return ['id' => $id, 'receipt' => (string) $this->tenantId];
                        }
                };
            }
        };
        $this->app->instance('Razorpay\\Api\\Api', $fake);

        // Run the job (inline)
        $job = new ProcessRazorpayPayment($rowId);
        $job->handle();

        // Assert tenant_subscriptions has entry for tenantId
        $this->assertDatabaseHas('tenant_subscriptions', [
            'tenant_id' => $tenantId,
            'subscription_id' => $paymentId,
            'status' => 'paid',
        ]);

        // Assert tenant activated flag set
        $this->assertDatabaseHas('tenants', [
            'id' => $tenantId,
            'active' => 1,
        ]);
    }
}
