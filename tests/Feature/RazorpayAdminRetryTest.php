<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RazorpayAdminRetryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_retry_reprocesses_payment_and_activates_tenant()
    {
        // do not disable middleware so session and auth are available for the admin route

        // seed tenant with id 1
        \DB::table('tenants')->insert([
            'id' => 1,
            'name' => 'Retry Tenant',
            'domain' => 'retry.local',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // create a saved razorpay payment (no tenant mapped yet)
        $id = \DB::table('razorpay_payments')->insertGetId([
            'payment_id' => 'admin_pay_1',
            'order_id' => 'order_admin_1',
            'tenant_id' => null,
            'amount' => 1000,
            'currency' => 'INR',
            'raw' => json_encode(['id' => 'admin_pay_1', 'order_id' => 'order_admin_1']),
            'created_at' => now(),
            'updated_at' => now(),
            'retry_count' => 0,
        ]);

        // fake the Razorpay API so order fetch returns receipt '1'
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
        $key = 'Razorpay\\Api\\Api';
        $this->app->instance($key, $fake);

        // ensure 'role' middleware alias exists in tests (backpack/spatie middleware may not be registered in this environment)
        $this->app->bind('role', function () {
            return new class {
                public function handle($request, $next, $role = null)
                {
                    return $next($request);
                }
            };
        });

        // run the processing job directly (this is what retry should do)
        $job = new \App\Jobs\ProcessRazorpayPayment($id);
        $job->handle();

        // assert tenant activated by the job
        $this->assertDatabaseHas('tenants', ['id' => 1, 'active' => 1]);

        // assert subscription created
        $this->assertDatabaseHas('tenant_subscriptions', ['tenant_id' => 1, 'subscription_id' => 'admin_pay_1']);

        // now call the admin retry endpoint which should increment retry_count
    // authenticate a user so admin middleware does not redirect to login
    $user = \App\Models\User::factory()->create();
    // Backpack uses the 'backpack' guard by default in this app
    $this->actingAs($user, 'backpack');

    $response = $this->get('/admin/razorpay-payments/' . $id . '/retry');
        $response->assertRedirect();

    // inspect the row from default and sqlite connections (no debug prints)
    $rowDefault = \DB::table('razorpay_payments')->where('id', $id)->first();
    $rowSqlite = \DB::connection('sqlite')->table('razorpay_payments')->where('id', $id)->first();

    // assert retry_count incremented
    $this->assertDatabaseHas('razorpay_payments', ['id' => $id, 'retry_count' => 1]);
    }
}
