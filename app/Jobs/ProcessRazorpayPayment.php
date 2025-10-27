<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\RazorpayPayment;
use App\Models\TenantSubscription;
use Illuminate\Support\Facades\DB;

class ProcessRazorpayPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $paymentId;

    public function __construct($paymentId)
    {
        $this->paymentId = $paymentId;
    }

    public function handle()
    {
        $central = config('tenancy.database.central_connection') ?? null;

        // read payment from central DB to avoid tenant/connection issues
        if ($central) {
            $paymentRow = DB::connection($central)->table('razorpay_payments')->where('id', $this->paymentId)->first();
        } else {
            $paymentRow = DB::table('razorpay_payments')->where('id', $this->paymentId)->first();
        }

        if (! $paymentRow) {
            return;
        }

        $payment = (object) [
            'id' => $paymentRow->id,
            'payment_id' => $paymentRow->payment_id,
            'order_id' => $paymentRow->order_id,
            'tenant_id' => $paymentRow->tenant_id,
            'amount' => $paymentRow->amount,
            'currency' => $paymentRow->currency,
            'raw' => is_string($paymentRow->raw) ? json_decode($paymentRow->raw, true) : $paymentRow->raw,
            'created_at' => $paymentRow->created_at,
        ];

        $tenantId = $payment->tenant_id;

        // try to fetch tenant id from order receipt if not present
        // prefer an app container binding (useful in tests where a fake is bound),
        // fall back to checking the real SDK class
        if (! $tenantId && $payment->order_id) {
            // Prefer container-bound fake (useful in tests). Try several common keys.
            $api = null;
            $boundKeys = ['Razorpay\\Api\\Api', '\\Razorpay\\Api\\Api', 'razorpay.api', 'razorpay'];
            foreach ($boundKeys as $key) {
                try {
                    if (app()->bound($key)) {
                        $api = app()->make($key);
                        break;
                    }
                } catch (\Throwable $t) {
                    // ignore and continue
                }
            }

            // If no container binding, and the class exists, instantiate with env keys if available.
            if (! $api && class_exists('Razorpay\\Api\\Api')) {
                try {
                    $keyId = env('RAZORPAY_KEY_ID');
                    $keySecret = env('RAZORPAY_KEY_SECRET');
                    if ($keyId && $keySecret) {
                        $api = new \Razorpay\Api\Api($keyId, $keySecret);
                    } else {
                        // Last resort: attempt to instantiate without args (some tests may bind a fake via class name)
                        $api = app()->make('Razorpay\\Api\\Api');
                    }
                } catch (\Throwable $t) {
                    Log::error('Failed to instantiate Razorpay Api in ProcessRazorpayPayment: ' . $t->getMessage());
                }
            }

            if ($api) {
                try {
                    $order = $api->order->fetch($payment->order_id);
                    $tenantId = is_array($order) ? ($order['receipt'] ?? null) : ($order->receipt ?? null);
                } catch (\Exception $e) {
                    Log::error('Failed to fetch order in ProcessRazorpayPayment: ' . $e->getMessage());
                }
            }
        }

        if (! $tenantId) {
            // nothing we can do without tenant mapping
            Log::warning('ProcessRazorpayPayment: no tenant mapping for payment ' . $payment->payment_id);
            return;
        }

        try {
            $central = config('tenancy.database.central_connection') ?? null;
            if ($central) {
                DB::connection($central)->table('tenant_subscriptions')->updateOrInsert(
                    ['tenant_id' => $tenantId],
                    ['subscription_id' => $payment->payment_id, 'stripe_customer_id' => null, 'price_id' => null, 'status' => 'paid', 'raw' => json_encode($payment->raw), 'updated_at' => now(), 'created_at' => now()]
                );

                DB::connection($central)->table('tenants')->where('id', $tenantId)->update(['active' => true, 'activated_at' => now()]);
            } else {
                TenantSubscription::updateOrCreate(
                    ['tenant_id' => $tenantId],
                    [
                        'subscription_id' => $payment->payment_id,
                        'stripe_customer_id' => null,
                        'price_id' => null,
                        'status' => 'paid',
                        'raw' => $payment->raw,
                    ]
                );

                DB::table('tenants')->where('id', $tenantId)->update(['active' => true, 'activated_at' => now()]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to process Razorpay payment in job: ' . $e->getMessage());
        }
    }
}
