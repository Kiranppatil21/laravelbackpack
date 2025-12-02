<?php

namespace App\Jobs;

use App\Models\TenantSubscription;
use App\Services\RazorpayResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessRazorpayPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $paymentId;

    protected $apiInstance;

    public function __construct($paymentId, $apiInstance = null)
    {
        $this->paymentId = $paymentId;
        $this->apiInstance = $apiInstance;
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
        // prefer an explicitly provided API instance, then a container binding
        // (useful in tests where a fake is bound), then fall back to the real SDK class
        if (! $tenantId && $payment->order_id) {
            // If the job was constructed with an API instance (only for inline calls), use it.
            $api = $this->apiInstance ?? null;
            // Otherwise use the centralized resolver service (resolved from container) which prefers container bindings then env-based instantiation
            if (! $api) {
                try {
                    $resolver = app()->make(\App\Services\RazorpayResolverInterface::class);
                    $api = $resolver->resolve();
                } catch (\Throwable $t) {
                    // fall back to direct instantiation as last resort
                    $api = (new RazorpayResolver)->resolve();
                }
            }

            if ($api) {
                try {
                    $order = $api->order->fetch($payment->order_id);
                    $tenantId = is_array($order) ? ($order['receipt'] ?? null) : ($order->receipt ?? null);
                } catch (\Exception $e) {
                    Log::error('Failed to fetch order in ProcessRazorpayPayment: '.$e->getMessage());
                }
            }
        }

        if (! $tenantId) {
            // nothing we can do without tenant mapping
            Log::warning('ProcessRazorpayPayment: no tenant mapping for payment '.$payment->payment_id);

            return;
        }

        try {
            $central = config('tenancy.database.central_connection') ?? null;
            if ($central) {
                // compute tenant_uuid where possible
                $tenantUuid = null;
                if (is_string($tenantId) && preg_match('/[0-9a-fA-F\-]{36}/', $tenantId)) {
                    $tenantUuid = $tenantId;
                } else {
                    try {
                        $tenantUuid = DB::connection($central)->table('tenants')->where('id', $tenantId)->value('uuid');
                    } catch (\Throwable $e) {
                        $tenantUuid = null;
                    }
                }

                DB::connection($central)->table('tenant_subscriptions')->updateOrInsert(
                    ['tenant_id' => $tenantId],
                    ['tenant_uuid' => $tenantUuid, 'subscription_id' => $payment->payment_id, 'stripe_customer_id' => null, 'price_id' => null, 'status' => 'paid', 'raw' => json_encode($payment->raw), 'updated_at' => now(), 'created_at' => now()]
                );

                DB::connection($central)->table('tenants')->where('id', $tenantId)->update(['active' => true, 'activated_at' => now()]);
            } else {
                // compute tenant_uuid for local DB writes
                $tenantUuid = null;
                if (is_string($tenantId) && preg_match('/[0-9a-fA-F\-]{36}/', $tenantId)) {
                    $tenantUuid = $tenantId;
                } else {
                    try {
                        $tenantUuid = DB::table('tenants')->where('id', $tenantId)->value('uuid');
                    } catch (\Throwable $e) {
                        $tenantUuid = null;
                    }
                }

                TenantSubscription::updateOrCreate(
                    ['tenant_id' => $tenantId],
                    [
                        'tenant_uuid' => $tenantUuid,
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
            Log::error('Failed to process Razorpay payment in job: '.$e->getMessage());
        }
    }
}
