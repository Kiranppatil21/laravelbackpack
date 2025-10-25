<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use App\Models\RazorpayPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RazorpayPaymentCrudController extends CrudController
{
    public function setup()
    {
        $this->crud->setModel(RazorpayPayment::class);
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/razorpay-payment');
        $this->crud->setEntityNameStrings('Razorpay payment', 'Razorpay payments');
    }

    protected function setupListOperation()
    {
        $this->crud->addColumn(['name' => 'payment_id', 'label' => 'Payment ID']);
        $this->crud->addColumn(['name' => 'order_id', 'label' => 'Order ID']);
        $this->crud->addColumn(['name' => 'tenant_id', 'label' => 'Tenant ID']);
        $this->crud->addColumn(['name' => 'amount', 'label' => 'Amount']);
        $this->crud->addColumn(['name' => 'currency', 'label' => 'Currency']);
        $this->crud->addColumn(['name' => 'created_at', 'label' => 'Received At']);

        // add a simple retry button per-row (calls admin retry route)
        $this->crud->addButtonFromView('line', 'retry', 'razorpay_retry_button', 'beginning');
    }

    public function retry($id)
    {
        $central = config('tenancy.database.central_connection') ?? null;


        // read payment from central DB to avoid tenant-connection confusion
        if ($central) {
            $paymentRow = DB::connection($central)->table('razorpay_payments')->where('id', $id)->first();
        } else {
            $paymentRow = DB::table('razorpay_payments')->where('id', $id)->first();
        }
        // normalize
        $payment = $paymentRow;

        if (! $payment) {
            request()->session()->flash('error', 'Payment not found');
            return redirect()->back();
        }

        // increment audit fields immediately (so admin retry attempts are recorded)
        try {
            if ($central) {
                DB::connection($central)->table('razorpay_payments')->where('id', $payment->id)->increment('retry_count');
                DB::connection($central)->table('razorpay_payments')->where('id', $payment->id)->update(['last_retry_at' => now()]);
            } else {
                DB::table('razorpay_payments')->where('id', $payment->id)->increment('retry_count');
                DB::table('razorpay_payments')->where('id', $payment->id)->update(['last_retry_at' => now()]);
            }
            
        } catch (\Exception $e) {
            Log::warning('Failed to write retry audit: ' . $e->getMessage());
        }

        // determine tenant id (try fetching order to read receipt -> tenant id)
        $tenantId = $payment->tenant_id ?? null;
        if (class_exists('Razorpay\\Api\\Api') && ! empty($payment->order_id)) {
            try {
                $api = app()->make('Razorpay\\Api\\Api');
                $order = $api->order->fetch($payment->order_id);
                $receipt = is_array($order) ? ($order['receipt'] ?? null) : ($order->receipt ?? null);
                if ($receipt) {
                    $tenantId = $receipt;
                }
            } catch (\Exception $e) {
                Log::warning('Order fetch failed during admin retry: ' . $e->getMessage());
            }
        }

        // dispatch processing job (it will write to central DB) regardless; job is idempotent
        try {
            $job = new \App\Jobs\ProcessRazorpayPayment($payment->id);
            // try to run the handler synchronously (useful for environments without queue workers)
            app()->call([$job, 'handle']);
            request()->session()->flash('success', 'Retry processing finished');
        } catch (\Exception $e) {
            Log::warning('Synchronous job handle failed in retry: ' . $e->getMessage());
            // fallback: write directly to central DB if we have a tenant mapping
            try {
                if ($tenantId) {
                    if ($central) {
                        DB::connection($central)->table('tenant_subscriptions')->updateOrInsert(
                            ['tenant_id' => $tenantId],
                            ['subscription_id' => $payment->payment_id, 'stripe_customer_id' => null, 'price_id' => null, 'status' => 'paid', 'raw' => json_encode($payment->raw), 'updated_at' => now(), 'created_at' => now()]
                        );

                        DB::connection($central)->table('tenants')->where('id', $tenantId)->update(['active' => true, 'activated_at' => now()]);
                    } else {
                        DB::table('tenant_subscriptions')->updateOrInsert(
                            ['tenant_id' => $tenantId],
                            ['subscription_id' => $payment->payment_id, 'stripe_customer_id' => null, 'price_id' => null, 'status' => 'paid', 'raw' => json_encode($payment->raw), 'updated_at' => now(), 'created_at' => now()]
                        );

                        DB::table('tenants')->where('id', $tenantId)->update(['active' => true, 'activated_at' => now()]);
                    }
                }
                request()->session()->flash('success', 'Retry processing finished (fallback)');
            } catch (\Exception $ex) {
                Log::warning('Fallback processing failed during admin retry: ' . $ex->getMessage());
                request()->session()->flash('error', 'Retry failed');
            }
        }

        return redirect()->back();
    }
}
