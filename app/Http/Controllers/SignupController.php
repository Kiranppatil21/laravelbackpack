<?php

namespace App\Http\Controllers;

use App\Http\Requests\SignupRequest;
use App\Models\Domain;
use App\Models\Tenant;
use App\Services\RazorpayResolverInterface;
use Illuminate\Http\Request;

class SignupController extends Controller
{
    protected RazorpayResolverInterface $razorpayResolver;

    public function __construct(RazorpayResolverInterface $razorpayResolver)
    {
        $this->razorpayResolver = $razorpayResolver;
    }

    public function show()
    {
        return view('signup.form');
    }

    public function store(SignupRequest $request)
    {
        $validated = $request->validated();
        // Wrap tenant creation and Stripe calls in a transaction-like flow with cleanup on failure
        $tenant = null;
        $tenantIntId = null;
        try {
            // Insert tenant row into central `tenants` table with UUID while
            // keeping integer `id` untouched for FK compatibility.
            $uuid = (string) \Illuminate\Support\Str::uuid();

            // Insert and get the integer primary key for the central tenants table so
            // domains FK remains valid in the current schema.
            $tenantIntId = \Illuminate\Support\Facades\DB::table('tenants')->insertGetId([
                'uuid' => $uuid,
                'name' => $validated['name'],
                'domain' => $validated['domain'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Get a Tenant model instance (stancl) by uuid so other code can use it
            $tenant = Tenant::where('uuid', $uuid)->first();

            // create domain record pointing to the central tenant's integer id for now
            Domain::create([
                'domain' => $validated['domain'],
                'tenant_id' => $tenantIntId,
                'tenant_uuid' => $uuid,
            ]);

            $priceId = $validated['price_id'] ?? config('services.stripe.price_id');

            $gateway = $validated['gateway'] ?? 'stripe';

            if ($gateway === 'razorpay') {
                // create a Razorpay order and render a checkout view
                $amount = isset($validated['amount']) ? (int) round($validated['amount'] * 100) : 0; // rupees to paise
                $currency = 'INR';

                // resolve Razorpay API via resolver (container-bound fakes will be preferred in tests)
                $api = $this->razorpayResolver->resolve();
                if (! $api) {
                    throw new \Exception('Razorpay PHP SDK not available or RAZORPAY keys missing. Run: composer require razorpay/razorpay and set RAZORPAY_KEY_ID/RAZORPAY_KEY_SECRET');
                }

                $order = $api->order->create([
                    'amount' => max(1, $amount),
                    'currency' => $currency,
                    'receipt' => $tenant ? $tenant->getKey() : $tenantIntId,
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
                    'tenant_id' => $tenant ? $tenant->getKey() : $tenantIntId,
                    'tenant_uuid' => $uuid,
                ],
            ]);

            $sessionParams = [
                'payment_method_types' => ['card'],
                'mode' => 'subscription',
                'customer' => $customer->id,
                'metadata' => [
                    'tenant_id' => $tenant ? $tenant->getKey() : $tenantIntId,
                    'tenant_uuid' => $uuid,
                    'admin_email' => $validated['admin_email'],
                ],
                'success_url' => rtrim(config('app.url'), '/').route('signup.success', [], false).'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => rtrim(config('app.url'), '/').route('signup.show', [], false).'?cancel=1',
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
                        'product_data' => ['name' => $validated['name'].' plan'],
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
            if ($tenantIntId) {
                try {
                    Domain::where('tenant_id', $tenantIntId)->delete();
                } catch (\Exception $inner) {
                    // ignore
                }
            }

            if ($tenant) {
                try {
                    $tenant->delete();
                } catch (\Exception $inner) {
                    // ignore
                }
            }

            // log and inform the user
            report($e);
            session()->flash('error', 'There was an error starting the signup process: '.$e->getMessage());

            return redirect()->back()->withInput();
        }
    }

    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');

        return view('signup.success', ['session_id' => $sessionId]);
    }
}
