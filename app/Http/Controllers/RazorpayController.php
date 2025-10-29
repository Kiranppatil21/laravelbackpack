<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessRazorpayPayment;
use App\Models\RazorpayPayment;
use App\Services\RazorpayResolverInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RazorpayController extends Controller
{
    protected RazorpayResolverInterface $razorpayResolver;

    public function __construct(RazorpayResolverInterface $razorpayResolver)
    {
        $this->razorpayResolver = $razorpayResolver;
    }

    public function webhook(Request $request)
    {
        // Verify webhook signature
        $payload = $request->getContent();
    $signature = $request->header('X-Razorpay-Signature');
    // read webhook secret from config (phpstan-friendly)
    $secret = config('services.razorpay.webhook_secret');

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
                        // tenant_uuid may not be known at webhook time; write null so later background job
                        // (ProcessRazorpayPayment) can populate it when it determines the tenant mapping.
                        'tenant_uuid' => null,
                        'raw' => $entity,
                    ]);

                    // dispatch background job to process payment (idempotent)
                    try {
                        ProcessRazorpayPayment::dispatch($payment->id);
                    } catch (\Exception $e) {
                        // if dispatching fails, run inline as a fallback so webhooks still activate tenants in tests/environments without queue
                        Log::warning('Dispatch failed for ProcessRazorpayPayment, running inline: '.$e->getMessage());

                        // resolve Razorpay API instance using the injected resolver and pass into job
                        $apiInstance = $this->razorpayResolver->resolve();

                        $job = new ProcessRazorpayPayment($payment->id, $apiInstance);
                        app()->call([$job, 'handle']);
                    }
                }
                break;

            default:
                Log::info('Unhandled Razorpay event: '.($eventType ?? 'unknown'));
        }

        return response('OK');
    }
}
