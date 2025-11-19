import React, { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import MarketingLayout from '@/Components/MarketingLayout.jsx';

export default function Register({ plans = {}, errors = {} }) {
    const [step, setStep] = useState(1);
    const [formData, setFormData] = useState({
        // Company Info
        company_name: '',
        company_email: '',
        company_phone: '',
        company_address: '',
        
        // Admin User
        admin_name: '',
        admin_email: '',
        admin_password: '',
        admin_password_confirmation: '',
        
        // Plan Selection
        selected_plan: 'professional',
        billing_period: 'monthly',
        
        // Payment
        payment_method: 'card',
        
        // Agreement
        terms_accepted: false,
        privacy_accepted: false
    });

    const planDetails = {
        starter: {
            name: "Starter",
            monthly: 2999,
            yearly: 29990,
            features: ["Up to 25 guards", "Basic attendance", "Simple payroll", "Mobile access"]
        },
        professional: {
            name: "Professional",
            monthly: 5999,
            yearly: 59990,
            features: ["Up to 100 guards", "GPS attendance", "Complete payroll", "Advanced features"]
        },
        enterprise: {
            name: "Enterprise",
            monthly: 12999,
            yearly: 129990,
            features: ["Unlimited guards", "Custom integrations", "White-label", "24/7 support"]
        }
    };

    const handleInputChange = (e) => {
        const { name, value, type, checked } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: type === 'checkbox' ? checked : value
        }));
    };

    const handleNext = () => {
        if (step < 4) setStep(step + 1);
    };

    const handlePrevious = () => {
        if (step > 1) setStep(step - 1);
    };

    const handleSubmit = () => {
        if (!agreed) {
            alert('Please agree to the terms and conditions');
            return;
        }

        // Submit the form data
        router.post('/register', {
            ...formData,
            agreed_to_terms: agreed
        }, {
            onError: (errors) => {
                console.error('Registration errors:', errors);
                // Handle validation errors
            }
        });
    };

    const jsonLd = {
        "@context": "https://schema.org",
        "@type": "WebPage",
        "name": "Register for SecureServe - Start Your Free Trial",
        "description": "Create your SecureServe account and start managing your security operations with our comprehensive platform. Choose from Starter, Professional, or Enterprise plans.",
        "url": window.location.href,
        "mainEntity": {
            "@type": "Service",
            "name": "SecureServe Registration",
            "provider": {
                "@type": "Organization",
                "name": "SecureServe",
                "description": "Complete security management platform for Indian agencies"
            }
        }
    };
    
    const selectedPlan = planDetails[formData.selected_plan];
    const planPrice = formData.billing_period === 'monthly' ? selectedPlan.monthly : selectedPlan.yearly;
    const monthlyPrice = formData.billing_period === 'monthly' ? planPrice : Math.floor(selectedPlan.yearly / 12);

    return (
        <MarketingLayout 
            title="Register - Start Your Free Trial | SecureServe"
            description="Create your SecureServe account and start managing your security operations. Choose from Starter (₹2,999/month), Professional (₹5,999/month), or Enterprise (₹12,999/month) plans."
            keywords="security software registration, free trial, security management signup, create account, security agency software"
            jsonLd={jsonLd}
        >
            <div className="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
                {/* Progress Bar */}
                <div className="mb-8">
                    <div className="flex items-center justify-between mb-4">
                        {[1, 2, 3, 4].map((stepNumber) => (
                            <div
                                key={stepNumber}
                                className={`flex items-center justify-center w-10 h-10 rounded-full border-2 ${
                                    step >= stepNumber
                                        ? 'bg-blue-600 border-blue-600 text-white'
                                        : 'border-gray-300 text-gray-400'
                                }`}
                            >
                                {stepNumber}
                            </div>
                        ))}
                    </div>
                    <div className="w-full bg-gray-200 rounded-full h-2">
                        <div
                            className="bg-blue-600 h-2 rounded-full transition-all duration-300"
                            style={{ width: `${(step / 4) * 100}%` }}
                        ></div>
                    </div>
                    <div className="flex justify-between text-sm text-gray-600 mt-2">
                        <span>Company Info</span>
                        <span>Admin Account</span>
                        <span>Choose Plan</span>
                        <span>Complete Setup</span>
                    </div>
                </div>

                <div className="bg-white rounded-2xl shadow-xl overflow-hidden">
                    {/* Step 1: Company Information */}
                    {step === 1 && (
                        <div className="p-8">
                            <div className="text-center mb-8">
                                <h2 className="text-3xl font-bold text-gray-900 mb-2">Company Information</h2>
                                <p className="text-gray-600">Tell us about your security agency</p>
                            </div>
                            
                            <div className="space-y-6">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Company Name *
                                    </label>
                                    <input
                                        type="text"
                                        name="company_name"
                                        value={formData.company_name}
                                        onChange={handleInputChange}
                                        className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="ABC Security Services"
                                        required
                                    />
                                </div>
                                
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-2">
                                            Company Email *
                                        </label>
                                        <input
                                            type="email"
                                            name="company_email"
                                            value={formData.company_email}
                                            onChange={handleInputChange}
                                            className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="info@abcsecurity.com"
                                            required
                                        />
                                    </div>
                                    
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-2">
                                            Phone Number *
                                        </label>
                                        <input
                                            type="tel"
                                            name="company_phone"
                                            value={formData.company_phone}
                                            onChange={handleInputChange}
                                            className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="+91 9876543210"
                                            required
                                        />
                                    </div>
                                </div>
                                
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Business Address
                                    </label>
                                    <textarea
                                        name="company_address"
                                        value={formData.company_address}
                                        onChange={handleInputChange}
                                        rows={3}
                                        className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Enter your business address"
                                    />
                                </div>
                            </div>
                            
                            <div className="flex justify-end mt-8">
                                <button
                                    onClick={handleNext}
                                    className="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors"
                                >
                                    Next Step →
                                </button>
                            </div>
                        </div>
                    )}

                    {/* Step 2: Admin Account */}
                    {step === 2 && (
                        <div className="p-8">
                            <div className="text-center mb-8">
                                <h2 className="text-3xl font-bold text-gray-900 mb-2">Create Admin Account</h2>
                                <p className="text-gray-600">Set up your administrator login</p>
                            </div>
                            
                            <div className="space-y-6">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Admin Full Name *
                                    </label>
                                    <input
                                        type="text"
                                        name="admin_name"
                                        value={formData.admin_name}
                                        onChange={handleInputChange}
                                        className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="John Doe"
                                        required
                                    />
                                </div>
                                
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Admin Email *
                                    </label>
                                    <input
                                        type="email"
                                        name="admin_email"
                                        value={formData.admin_email}
                                        onChange={handleInputChange}
                                        className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="admin@abcsecurity.com"
                                        required
                                    />
                                    <p className="text-sm text-gray-500 mt-1">This will be your login email</p>
                                </div>
                                
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-2">
                                            Password *
                                        </label>
                                        <input
                                            type="password"
                                            name="admin_password"
                                            value={formData.admin_password}
                                            onChange={handleInputChange}
                                            className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Create strong password"
                                            required
                                        />
                                    </div>
                                    
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-2">
                                            Confirm Password *
                                        </label>
                                        <input
                                            type="password"
                                            name="admin_password_confirmation"
                                            value={formData.admin_password_confirmation}
                                            onChange={handleInputChange}
                                            className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Confirm your password"
                                            required
                                        />
                                    </div>
                                </div>
                                
                                <div className="bg-gray-50 rounded-lg p-4">
                                    <h4 className="font-medium text-gray-900 mb-2">Password Requirements:</h4>
                                    <ul className="text-sm text-gray-600 space-y-1">
                                        <li>• At least 8 characters long</li>
                                        <li>• Include uppercase and lowercase letters</li>
                                        <li>• Include at least one number</li>
                                        <li>• Include at least one special character</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div className="flex justify-between mt-8">
                                <button
                                    onClick={handlePrevious}
                                    className="bg-gray-100 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-200 transition-colors"
                                >
                                    ← Previous
                                </button>
                                <button
                                    onClick={handleNext}
                                    className="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors"
                                >
                                    Next Step →
                                </button>
                            </div>
                        </div>
                    )}

                    {/* Step 3: Plan Selection */}
                    {step === 3 && (
                        <div className="p-8">
                            <div className="text-center mb-8">
                                <h2 className="text-3xl font-bold text-gray-900 mb-2">Choose Your Plan</h2>
                                <p className="text-gray-600">Select the plan that best fits your agency size</p>
                            </div>
                            
                            {/* Billing Period Toggle */}
                            <div className="flex justify-center mb-8">
                                <div className="bg-gray-100 rounded-lg p-1 inline-flex">
                                    <button
                                        onClick={() => setFormData(prev => ({ ...prev, billing_period: 'monthly' }))}
                                        className={`px-6 py-2 rounded-md font-medium transition-all ${
                                            formData.billing_period === 'monthly'
                                                ? 'bg-white text-gray-900 shadow-sm'
                                                : 'text-gray-600'
                                        }`}
                                    >
                                        Monthly
                                    </button>
                                    <button
                                        onClick={() => setFormData(prev => ({ ...prev, billing_period: 'yearly' }))}
                                        className={`px-6 py-2 rounded-md font-medium transition-all ${
                                            formData.billing_period === 'yearly'
                                                ? 'bg-white text-gray-900 shadow-sm'
                                                : 'text-gray-600'
                                        }`}
                                    >
                                        Yearly
                                        <span className="ml-2 bg-green-500 text-white text-xs px-2 py-1 rounded-full">
                                            Save 17%
                                        </span>
                                    </button>
                                </div>
                            </div>
                            
                            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                                {Object.entries(planDetails).map(([key, plan]) => (
                                    <div
                                        key={key}
                                        className={`border-2 rounded-lg p-6 cursor-pointer transition-all ${
                                            formData.selected_plan === key
                                                ? 'border-blue-600 bg-blue-50'
                                                : 'border-gray-200 hover:border-gray-300'
                                        }`}
                                        onClick={() => setFormData(prev => ({ ...prev, selected_plan: key }))}
                                    >
                                        <div className="flex items-center justify-between mb-4">
                                            <h3 className="text-xl font-bold text-gray-900">{plan.name}</h3>
                                            <div className={`w-5 h-5 rounded-full border-2 ${
                                                formData.selected_plan === key
                                                    ? 'border-blue-600 bg-blue-600'
                                                    : 'border-gray-300'
                                            }`}>
                                                {formData.selected_plan === key && (
                                                    <div className="w-full h-full rounded-full bg-white transform scale-50"></div>
                                                )}
                                            </div>
                                        </div>
                                        
                                        <div className="mb-4">
                                            <span className="text-3xl font-bold text-gray-900">
                                                ₹{formData.billing_period === 'monthly' 
                                                    ? plan.monthly.toLocaleString() 
                                                    : Math.floor(plan.yearly / 12).toLocaleString()
                                                }
                                            </span>
                                            <span className="text-gray-600">/month</span>
                                        </div>
                                        
                                        <ul className="space-y-2">
                                            {plan.features.map((feature, index) => (
                                                <li key={index} className="flex items-center text-sm">
                                                    <svg className="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                                                    </svg>
                                                    {feature}
                                                </li>
                                            ))}
                                        </ul>
                                    </div>
                                ))}
                            </div>
                            
                            {/* Trial Notice */}
                            <div className="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                                <div className="flex items-center">
                                    <svg className="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                                    </svg>
                                    <span className="text-green-800 font-medium">
                                        14-day free trial included • No credit card required for trial
                                    </span>
                                </div>
                            </div>
                            
                            <div className="flex justify-between">
                                <button
                                    onClick={handlePrevious}
                                    className="bg-gray-100 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-200 transition-colors"
                                >
                                    ← Previous
                                </button>
                                <button
                                    onClick={handleNext}
                                    className="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors"
                                >
                                    Continue →
                                </button>
                            </div>
                        </div>
                    )}

                    {/* Step 4: Complete Setup */}
                    {step === 4 && (
                        <div className="p-8">
                            <div className="text-center mb-8">
                                <h2 className="text-3xl font-bold text-gray-900 mb-2">Complete Your Setup</h2>
                                <p className="text-gray-600">Review your information and start your free trial</p>
                            </div>
                            
                            {/* Summary */}
                            <div className="bg-gray-50 rounded-lg p-6 mb-8">
                                <h3 className="text-lg font-semibold text-gray-900 mb-4">Setup Summary</h3>
                                
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <h4 className="font-medium text-gray-900 mb-2">Company Information</h4>
                                        <p className="text-sm text-gray-600">{formData.company_name}</p>
                                        <p className="text-sm text-gray-600">{formData.company_email}</p>
                                        <p className="text-sm text-gray-600">{formData.company_phone}</p>
                                    </div>
                                    
                                    <div>
                                        <h4 className="font-medium text-gray-900 mb-2">Administrator</h4>
                                        <p className="text-sm text-gray-600">{formData.admin_name}</p>
                                        <p className="text-sm text-gray-600">{formData.admin_email}</p>
                                    </div>
                                </div>
                                
                                <div className="border-t border-gray-200 pt-4 mt-4">
                                    <div className="flex justify-between items-center">
                                        <div>
                                            <h4 className="font-medium text-gray-900">{selectedPlan.name} Plan</h4>
                                            <p className="text-sm text-gray-600">
                                                {formData.billing_period === 'monthly' ? 'Monthly' : 'Annual'} billing
                                            </p>
                                        </div>
                                        <div className="text-right">
                                            <p className="text-2xl font-bold text-gray-900">
                                                ₹{monthlyPrice.toLocaleString()}/month
                                            </p>
                                            {formData.billing_period === 'yearly' && (
                                                <p className="text-sm text-gray-600">
                                                    Billed annually (₹{planPrice.toLocaleString()})
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            {/* Terms and Conditions */}
                            <div className="space-y-4 mb-8">
                                <div className="flex items-start">
                                    <input
                                        type="checkbox"
                                        name="terms_accepted"
                                        checked={formData.terms_accepted}
                                        onChange={handleInputChange}
                                        className="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                        required
                                    />
                                    <label className="ml-3 text-sm text-gray-700">
                                        I agree to the{' '}
                                        <a href="#" className="text-blue-600 hover:text-blue-700">Terms of Service</a>{' '}
                                        and{' '}
                                        <a href="#" className="text-blue-600 hover:text-blue-700">Privacy Policy</a>
                                    </label>
                                </div>
                                
                                <div className="flex items-start">
                                    <input
                                        type="checkbox"
                                        name="privacy_accepted"
                                        checked={formData.privacy_accepted}
                                        onChange={handleInputChange}
                                        className="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                        required
                                    />
                                    <label className="ml-3 text-sm text-gray-700">
                                        I consent to receiving marketing communications and product updates
                                    </label>
                                </div>
                            </div>
                            
                            <div className="flex justify-between">
                                <button
                                    onClick={handlePrevious}
                                    className="bg-gray-100 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-200 transition-colors"
                                >
                                    ← Previous
                                </button>
                                <button
                                    onClick={handleSubmit}
                                    disabled={!formData.terms_accepted || !formData.privacy_accepted}
                                    className="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed"
                                >
                                    Start Free Trial
                                </button>
                            </div>
                        </div>
                    )}
                </div>

                {/* Security Notice */}
                <div className="mt-8 text-center text-sm text-gray-600">
                    <p className="flex items-center justify-center">
                        <svg className="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fillRule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                        </svg>
                        Your information is secured with enterprise-grade encryption
                    </p>
                </div>
            </div>
        </MarketingLayout>
    );
}