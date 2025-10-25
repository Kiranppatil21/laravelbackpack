<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Database\Models\Tenant;
use App\Models\TenantSubscription;

class BillingController
{
    /**
     * Start a Stripe Checkout session for a tenant. Expects a price id or uses STRIPE_PRICE_ID.
     */
    public function checkout(Request $request, $id)
    {
        $tenant = Tenant::find($id);
        if (! $tenant) {
            return response()->json(['message' => 'Tenant not found'], 404);
        }

        $priceId = $request->input('price_id') ?: env('STRIPE_PRICE_ID');
        if (! $priceId) {
            return response()->json(['message' => 'No price_id provided and STRIPE_PRICE_ID not set'], 400);
        }

        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));

        $session = $stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'mode' => 'subscription',
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'metadata' => [
                'tenant_id' => $tenant->getTenantKey(),
            ],
            'success_url' => config('app.url') . '/admin?checkout_success=1&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => config('app.url') . '/admin?checkout_cancel=1',
        ]);

        // Redirect administrators to the Stripe-hosted checkout page
        return redirect($session->url);
    }

    /**
     * Stripe webhook receiver. Verifies signature and handles important events.
     */
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = env('STRIPE_WEBHOOK_SECRET');

        try {
            if ($webhookSecret) {
                $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
            } else {
                // if no signing secret configured, parse without verification
                $event = json_decode($payload);
            }
        } catch (\Exception $e) {
            Log::error('Stripe webhook verification failed: ' . $e->getMessage());
            return response('Invalid signature', 400);
        }

        // convert event to array for job and dispatch processing job (idempotent)
        $eventArray = null;
        if (is_object($event)) {
            $eventArray = json_decode(json_encode($event), true);
        } else {
            $eventArray = $event;
        }

        try {
            // dispatch job to process the webhook (queue driver controls execution)
            \App\Jobs\ProcessStripeEvent::dispatch($eventArray);
        } catch (\Exception $e) {
            Log::error('Failed to dispatch ProcessStripeEvent job: ' . $e->getMessage());
            // As a fallback, attempt to process synchronously
            try {
                (new \App\Jobs\ProcessStripeEvent($eventArray))->handle();
            } catch (\Exception $ex) {
                Log::error('Fallback synchronous processing failed: ' . $ex->getMessage());
            }
        }

        return response('OK');
    }
}
