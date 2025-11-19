import React, { useState, useEffect } from 'react';
import { Link, router } from '@inertiajs/react';

export default function Payment({ tenant, plan, billing_cycle, amount, razorpay_key }) {
    const [loading, setLoading] = useState(false);
    const [paymentError, setPaymentError] = useState('');

    const loadRazorpayScript = () => {
        return new Promise((resolve) => {
            const script = document.createElement('script');
            script.src = 'https://checkout.razorpay.com/v1/checkout.js';
            script.onload = () => resolve(true);
            script.onerror = () => resolve(false);
            document.body.appendChild(script);
        });
    };

    const handlePayment = async () => {
        setLoading(true);
        setPaymentError('');

        const scriptLoaded = await loadRazorpayScript();
        
        if (!scriptLoaded) {
            setPaymentError('Failed to load payment gateway. Please try again.');
            setLoading(false);
            return;
        }

        const options = {
            key: razorpay_key,
            amount: amount * 100, // Amount in paise
            currency: 'INR',
            name: 'SecureServe',
            description: `${plan.name} - ${billing_cycle} subscription`,
            order_id: `order_${tenant.id}_${Date.now()}`, // This should come from backend
            handler: function (response) {
                // Handle successful payment
                router.post('/payment/success', {
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_order_id: response.razorpay_order_id,
                    razorpay_signature: response.razorpay_signature,
                    tenant_id: tenant.id,
                    amount: amount,
                });
            },
            prefill: {
                name: tenant.name,
                email: tenant.email,
                contact: tenant.phone,
            },
            theme: {
                color: '#2563eb',
            },
            modal: {
                ondismiss: function () {
                    setLoading(false);
                },
            },
        };

        const razorpay = new window.Razorpay(options);
        razorpay.open();
        setLoading(false);
    };

    return (
        <div className="min-h-screen bg-gray-50">
            {/* Navigation */}
            <nav className="bg-white shadow-sm border-b">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16">
                        <div className="flex items-center">
                            <Link href="/marketing" className="flex items-center">
                                <div className="h-8 w-8 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
                                    <span className="text-white font-bold text-sm">SS</span>
                                </div>
                                <span className="ml-2 text-xl font-bold text-gray-900">SecureServe</span>
                            </Link>
                        </div>
                        
                        <div className="flex items-center">
                            <span className="text-gray-600">Step 5 of 5: Payment</span>
                        </div>
                    </div>
                </div>
            </nav>

            {/* Payment Section */}
            <section className="py-16">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center mb-12">
                        <h1 className="text-4xl font-bold text-gray-900 mb-4">
                            Complete Your Subscription
                        </h1>
                        <p className="text-xl text-gray-600">
                            You're one step away from accessing SecureServe
                        </p>
                    </div>

                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-12">
                        {/* Order Summary */}
                        <div>
                            <h2 className="text-2xl font-bold text-gray-900 mb-6">Order Summary</h2>
                            
                            <div className="bg-white rounded-lg shadow-lg p-6 border">
                                <div className="mb-6">
                                    <h3 className="text-lg font-semibold text-gray-900 mb-2">Company Details</h3>
                                    <div className="text-gray-600">
                                        <p><strong>Company:</strong> {tenant.name}</p>
                                        <p><strong>Email:</strong> {tenant.email}</p>
                                        <p><strong>Phone:</strong> {tenant.phone}</p>
                                    </div>
                                </div>
                                
                                <hr className="my-6" />
                                
                                <div className="mb-6">
                                    <h3 className="text-lg font-semibold text-gray-900 mb-2">Subscription Plan</h3>
                                    <div className="flex justify-between items-center mb-2">
                                        <span className="text-gray-900 font-medium">{plan.name}</span>
                                        <span className="text-gray-900">₹{amount.toLocaleString()}</span>
                                    </div>
                                    <div className="flex justify-between items-center text-sm text-gray-600">
                                        <span>Billing: {billing_cycle}</span>
                                        <span>{billing_cycle === 'yearly' ? '2 months free!' : ''}</span>
                                    </div>
                                </div>
                                
                                <div className="mb-6">
                                    <h4 className="font-medium text-gray-900 mb-2">Plan Features:</h4>
                                    <ul className="text-sm text-gray-600 space-y-1">
                                        {plan.features.map((feature, index) => (
                                            <li key={index} className="flex items-start">
                                                <svg className="w-4 h-4 text-green-500 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                                                </svg>
                                                {feature}
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                                
                                <hr className="my-6" />
                                
                                <div className="flex justify-between items-center text-xl font-bold text-gray-900">
                                    <span>Total Amount:</span>
                                    <span>₹{amount.toLocaleString()}</span>
                                </div>
                                
                                {billing_cycle === 'yearly' && (
                                    <p className="text-sm text-green-600 mt-2">
                                        You save ₹{((plan.monthly * 12) - plan.yearly).toLocaleString()} with yearly billing!
                                    </p>
                                )}
                            </div>
                        </div>

                        {/* Payment Method */}
                        <div>
                            <h2 className="text-2xl font-bold text-gray-900 mb-6">Payment Method</h2>
                            
                            <div className="bg-white rounded-lg shadow-lg p-6 border">
                                <div className="mb-6">
                                    <div className="flex items-center justify-between mb-4">
                                        <h3 className="text-lg font-semibold text-gray-900">Secure Payment</h3>
                                        <div className="flex items-center space-x-2">
                                            <img src="https://razorpay.com/assets/logo-mark.svg" alt="Razorpay" className="h-6" />
                                            <span className="text-sm text-gray-600">Powered by Razorpay</span>
                                        </div>
                                    </div>
                                    
                                    <div className="grid grid-cols-4 gap-2 mb-4">
                                        <div className="border rounded p-2 text-center">
                                            <span className="text-xs text-gray-600">Visa</span>
                                        </div>
                                        <div className="border rounded p-2 text-center">
                                            <span className="text-xs text-gray-600">Mastercard</span>
                                        </div>
                                        <div className="border rounded p-2 text-center">
                                            <span className="text-xs text-gray-600">UPI</span>
                                        </div>
                                        <div className="border rounded p-2 text-center">
                                            <span className="text-xs text-gray-600">Netbanking</span>
                                        </div>
                                    </div>
                                    
                                    <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                                        <div className="flex items-start">
                                            <svg className="w-5 h-5 text-blue-500 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd" />
                                            </svg>
                                            <div>
                                                <h4 className="text-sm font-medium text-blue-900">14-Day Free Trial</h4>
                                                <p className="text-sm text-blue-700">
                                                    Your trial has already started! You can cancel anytime during the trial period without charge.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {paymentError && (
                                    <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                                        <p className="text-sm text-red-800">{paymentError}</p>
                                    </div>
                                )}
                                
                                <button
                                    onClick={handlePayment}
                                    disabled={loading}
                                    className="w-full bg-blue-600 text-white py-4 px-6 rounded-lg font-semibold text-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {loading ? 'Processing...' : `Pay ₹${amount.toLocaleString()}`}
                                </button>
                                
                                <div className="mt-4 text-center">
                                    <p className="text-xs text-gray-500">
                                        Your payment is processed securely by Razorpay.
                                        <br />
                                        By clicking "Pay", you agree to our Terms of Service and Privacy Policy.
                                    </p>
                                </div>
                            </div>
                            
                            <div className="mt-8 bg-gray-50 rounded-lg p-6">
                                <h3 className="text-lg font-semibold text-gray-900 mb-4">Money-Back Guarantee</h3>
                                <div className="space-y-3 text-sm text-gray-600">
                                    <div className="flex items-start">
                                        <svg className="w-5 h-5 text-green-500 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                                        </svg>
                                        <span>14-day free trial period</span>
                                    </div>
                                    <div className="flex items-start">
                                        <svg className="w-5 h-5 text-green-500 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                                        </svg>
                                        <span>30-day money-back guarantee</span>
                                    </div>
                                    <div className="flex items-start">
                                        <svg className="w-5 h-5 text-green-500 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                                        </svg>
                                        <span>24/7 customer support</span>
                                    </div>
                                    <div className="flex items-start">
                                        <svg className="w-5 h-5 text-green-500 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                                        </svg>
                                        <span>Cancel anytime</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    );
}