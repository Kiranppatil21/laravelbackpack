<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\RazorpayPayment;
use App\Models\TenantSubscription;
use Stancl\Tenancy\Database\Models\Tenant;
use App\Jobs\ProcessRazorpayPayment;

class RazorpayController extends Controller
{
    public function webhook(Request $request)
    {
        // Verify webhook signature
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');
        $secret = env('RAZORPAY_WEBHOOK_SECRET');

        if ($secret && $signature) {
            $generated = hash_hmac('sha256', $payload, $secret);
            if (! hash_equals($generated, $signature)) {
                Log::warning('Razorpay webhook signature mismatch');
                return response('Invalid signature', 400);
            }
        }

        $event = json_decode($payload, true);
        $eventType = $event['event'] ?? null;

        // basic idempotency: rely on unique payment id in our table
        try {
            if (isset($event['payload']['payment']['entity']['id'])) {
                $paymentId = $event['payload']['payment']['entity']['id'];
                // if we've already recorded this payment, ignore
                if (DB::table('razorpay_payments')->where('payment_id', $paymentId)->exists()) {
                    return response('OK');
                }
            }
        } catch (\Exception $e) {
            // ignore
        }

        switch ($eventType) {
            case 'payment.captured':
                $entity = $event['payload']['payment']['entity'] ?? null;
                if ($entity) {
                    $paymentId = $entity['id'] ?? null;
                    $orderId = $entity['order_id'] ?? null;
                    $amount = $entity['amount'] ?? null;
                    $currency = $entity['currency'] ?? null;

                    // store raw payment
                    $payment = RazorpayPayment::create([
                        'payment_id' => $paymentId,
                        'order_id' => $orderId,
                        'amount' => $amount,
                        'currency' => $currency,
                        'raw' => $entity,
                    ]);

                    // dispatch background job to process payment (idempotent)
                    try {
                        ProcessRazorpayPayment::dispatch($payment->id);
                    } catch (\Exception $e) {
                        // if dispatching fails, run inline as a fallback so webhooks still activate tenants in tests/environments without queue
                        Log::warning('Dispatch failed for ProcessRazorpayPayment, running inline: ' . $e->getMessage());
                        $job = new ProcessRazorpayPayment($payment->id);
                        app()->call([$job, 'handle']);
                    }
                }
                break;

            default:
                Log::info('Unhandled Razorpay event: ' . ($eventType ?? 'unknown'));
        }

        return response('OK');
    }
}
