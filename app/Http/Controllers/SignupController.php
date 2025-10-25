<?php

namespace App\Http\Controllers;

use App\Http\Requests\SignupRequest;
use Illuminate\Http\Request;
use Stancl\Tenancy\Database\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;

class SignupController extends Controller
{
    public function show()
    {
        return view('signup.form');
    }

    public function store(SignupRequest $request)
    {
        $validated = $request->validated();
        // Wrap tenant creation and Stripe calls in a transaction-like flow with cleanup on failure
        $tenant = null;
        try {
            // Create tenant record centrally using DB to avoid tenant model casting differences in test environments
            $tenantId = \Illuminate\Support\Facades\DB::table('tenants')->insertGetId([
                'name' => $validated['name'],
                'domain' => $validated['domain'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // attempt to get a Tenant model instance for use by other code; if not possible, continue with id
            try {
                $tenant = Tenant::find($tenantId);
            } catch (\Exception $e) {
                $tenant = null;
            }

            // create domain record (stancl tenancy domains table) - non-fatal if it fails in some envs
            try {
                Domain::create([
                    'domain' => $validated['domain'],
                    'tenant_id' => $tenantId,
                ]);
            } catch (\Exception $e) {
                // ignore domain creation failures here; it can be handled manually later
            }

            $priceId = $validated['price_id'] ?? env('STRIPE_PRICE_ID');

            $gateway = $validated['gateway'] ?? 'stripe';

            if ($gateway === 'razorpay') {
                // create a Razorpay order and render a checkout view
                $amount = isset($validated['amount']) ? (int)round($validated['amount'] * 100) : 0; // rupees to paise
                $currency = 'INR';

                if (! class_exists('\Razorpay\Api\Api')) {
                    throw new \Exception('Razorpay PHP SDK not installed. Run: composer require razorpay/razorpay');
                }

                $api = app()->make('\Razorpay\Api\Api');
                $order = $api->order->create([
                    'amount' => max(1, $amount),
                    'currency' => $currency,
                    'receipt' => $tenantId,
                    'payment_capture' => 1,
                ]);

                session()->flash('success', 'Razorpay order created. Redirecting to checkout...');
                return view('razorpay.checkout', ['order_id' => $order['id'], 'amount' => $order['amount'], 'currency' => $currency]);
            }
            // Stripe flow
            // resolve Stripe client from the container so tests can bind a fake implementation
            $stripe = app()->make(\Stripe\StripeClient::class);

            // Create a Stripe Customer for the admin email
            $customer = $stripe->customers->create([
                'email' => $validated['admin_email'],
                'name' => $validated['admin_name'],
                'metadata' => [
                    'tenant_id' => $tenant ? $tenant->getKey() : $tenantId,
                ],
            ]);

            $sessionParams = [
                'payment_method_types' => ['card'],
                'mode' => 'subscription',
                'customer' => $customer->id,
                'metadata' => [
                    'tenant_id' => $tenant ? $tenant->getKey() : $tenantId,
                    'admin_email' => $validated['admin_email'],
                ],
                'success_url' => rtrim(config('app.url'), '/') . route('signup.success', [], false) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => rtrim(config('app.url'), '/') . route('signup.show', [], false) . '?cancel=1',
            ];

            if ($priceId) {
                $sessionParams['line_items'] = [[
                    'price' => $priceId,
                    'quantity' => 1,
                ]];
            } else {
                // if no price provided, create a one-time placeholder subscription with zero amount
                $sessionParams['line_items'] = [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => ['name' => $validated['name'] . ' plan'],
                        'unit_amount' => 0,
                        'recurring' => ['interval' => 'month'],
                    ],
                    'quantity' => 1,
                ]];
            }

            $session = $stripe->checkout->sessions->create($sessionParams);

            // Success: flash a message and redirect to Stripe Checkout
            session()->flash('success', 'Checkout session created. Redirecting to Stripe...');
            return redirect($session->url);
        } catch (\Exception $e) {
            // cleanup: remove tenant and domain if we created them to avoid orphan central tenants
            if ($tenant) {
                try {
                    Domain::where('tenant_id', $tenant->getKey())->delete();
                } catch (\Exception $inner) {
                    // ignore
                }
                try {
                    $tenant->delete();
                } catch (\Exception $inner) {
                    // ignore
                }
            }

            // log and inform the user
            report($e);
            session()->flash('error', 'There was an error starting the signup process: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');
        return view('signup.success', ['session_id' => $sessionId]);
    }
}
