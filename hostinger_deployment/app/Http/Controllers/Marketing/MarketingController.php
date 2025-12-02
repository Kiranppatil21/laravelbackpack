<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarketingController extends Controller
{
    /**
     * Show the registration form
     */
    public function showRegister()
    {
        return Inertia::render('Marketing/Register');
    }

    /**
     * Handle registration form submission
     */
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'required|string|max:500',
            'company_phone' => 'required|string|max:20',
            'company_email' => 'required|email|max:255|unique:tenants,email',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255',
            'admin_phone' => 'required|string|max:20',
            'admin_password' => 'required|string|min:8|confirmed',
            'selected_plan' => 'required|string|in:starter,professional,enterprise',
            'billing_cycle' => 'required|string|in:monthly,yearly',
            'agreed_to_terms' => 'required|accepted',
        ]);

        DB::beginTransaction();
        
        try {
            // Create tenant
            $tenant = Tenant::create([
                'id' => Str::uuid(),
                'name' => $validatedData['company_name'],
                'email' => $validatedData['company_email'],
                'phone' => $validatedData['company_phone'],
                'address' => $validatedData['company_address'],
                'plan' => $validatedData['selected_plan'],
                'billing_cycle' => $validatedData['billing_cycle'],
                'status' => 'trial', // Start with trial status
                'trial_ends_at' => now()->addDays(14), // 14-day trial
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create domain for the tenant
            $domain = Str::slug($validatedData['company_name']) . '.secureserve.local';
            $tenant->domains()->create([
                'domain' => $domain
            ]);

            // Initialize tenant database and create admin user
            tenancy()->initialize($tenant);

            $adminUser = User::create([
                'name' => $validatedData['admin_name'],
                'email' => $validatedData['admin_email'],
                'phone' => $validatedData['admin_phone'],
                'password' => Hash::make($validatedData['admin_password']),
                'email_verified_at' => now(),
            ]);

            // Assign admin role (assuming you have role management)
            if (class_exists('Spatie\Permission\Models\Role')) {
                $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Agency Owner']);
                $adminUser->assignRole($adminRole);
            }

            DB::commit();

            // Redirect to payment page or success page
            return redirect()->route('marketing.payment', [
                'tenant' => $tenant->id,
                'plan' => $validatedData['selected_plan'],
                'billing' => $validatedData['billing_cycle']
            ])->with('success', 'Account created successfully! Complete your payment to activate your subscription.');

        } catch (\Exception $e) {
            DB::rollback();
            
            return back()->withErrors([
                'general' => 'There was an error creating your account. Please try again.'
            ])->withInput();
        }
    }

    /**
     * Show payment page
     */
    public function showPayment(Request $request)
    {
        $tenant = Tenant::findOrFail($request->tenant);
        
        $plans = [
            'starter' => [
                'name' => 'Starter Plan',
                'monthly' => 2999,
                'yearly' => 29990,
                'features' => ['Up to 50 employees', 'Basic attendance', 'Mobile app access']
            ],
            'professional' => [
                'name' => 'Professional Plan', 
                'monthly' => 5999,
                'yearly' => 59990,
                'features' => ['Up to 200 employees', 'Advanced payroll', 'Client management', 'Visitor system']
            ],
            'enterprise' => [
                'name' => 'Enterprise Plan',
                'monthly' => 12999,
                'yearly' => 129990,
                'features' => ['Unlimited employees', 'Multi-location support', 'Custom integrations', 'Priority support']
            ]
        ];

        $selectedPlan = $plans[$request->plan];
        $amount = $request->billing === 'yearly' ? $selectedPlan['yearly'] : $selectedPlan['monthly'];

        return Inertia::render('Marketing/Payment', [
            'tenant' => $tenant,
            'plan' => $selectedPlan,
            'billing_cycle' => $request->billing,
            'amount' => $amount,
            'razorpay_key' => config('services.razorpay.key'),
        ]);
    }

    /**
     * Handle payment success
     */
    public function paymentSuccess(Request $request)
    {
        $validatedData = $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string', 
            'razorpay_signature' => 'required|string',
            'tenant_id' => 'required|string|exists:tenants,id',
        ]);

        // Verify payment with Razorpay
        // This would typically involve verifying the signature
        
        DB::beginTransaction();
        
        try {
            $tenant = Tenant::findOrFail($validatedData['tenant_id']);
            
            // Update tenant status to active
            $tenant->update([
                'status' => 'active',
                'subscription_starts_at' => now(),
                'subscription_ends_at' => $tenant->billing_cycle === 'yearly' ? now()->addYear() : now()->addMonth(),
            ]);

            // Record payment
            $tenant->payments()->create([
                'payment_id' => $validatedData['razorpay_payment_id'],
                'order_id' => $validatedData['razorpay_order_id'],
                'amount' => $request->amount,
                'currency' => 'INR',
                'status' => 'completed',
                'payment_method' => 'razorpay',
                'payment_date' => now(),
            ]);

            DB::commit();

            return redirect()->route('marketing.success', ['tenant' => $tenant->id])
                ->with('success', 'Payment successful! Your account is now active.');

        } catch (\Exception $e) {
            DB::rollback();
            
            return back()->withErrors([
                'payment' => 'Payment verification failed. Please contact support.'
            ]);
        }
    }

    /**
     * Show success page
     */
    public function showSuccess(Request $request)
    {
        $tenant = Tenant::findOrFail($request->tenant);
        
        return Inertia::render('Marketing/Success', [
            'tenant' => $tenant,
            'login_url' => 'https://' . $tenant->domains->first()->domain . '/admin',
        ]);
    }
}