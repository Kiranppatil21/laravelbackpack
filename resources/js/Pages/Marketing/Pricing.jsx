import React, { useState } from 'react';
import { Link } from '@inertiajs/react';
import MarketingLayout from '@/Components/MarketingLayout.jsx';

export default function Pricing() {
    const [billingPeriod, setBillingPeriod] = useState('monthly');

    const jsonLd = {
        "@context": "https://schema.org",
        "@type": "Product",
        "name": "SecureServe Security Management Platform",
        "description": "Complete security management platform with flexible pricing plans",
        "offers": [
            {
                "@type": "Offer",
                "name": "Starter Plan",
                "price": billingPeriod === 'monthly' ? "2999" : "29990",
                "priceCurrency": "INR",
                "priceValidUntil": "2026-12-31",
                "availability": "https://schema.org/InStock"
            },
            {
                "@type": "Offer",
                "name": "Professional Plan",
                "price": billingPeriod === 'monthly' ? "5999" : "59990",
                "priceCurrency": "INR",
                "priceValidUntil": "2026-12-31",
                "availability": "https://schema.org/InStock"
            },
            {
                "@type": "Offer",
                "name": "Enterprise Plan",
                "price": billingPeriod === 'monthly' ? "12999" : "129990",
                "priceCurrency": "INR",
                "priceValidUntil": "2026-12-31",
                "availability": "https://schema.org/InStock"
            }
        ]
    };

    const plans = {
        starter: {
            name: "Starter",
            description: "Perfect for small security agencies starting out",
            monthly: 2999,
            yearly: 29990,
            features: [
                "Up to 25 guards",
                "Basic attendance tracking",
                "Simple payroll processing",
                "Client management",
                "Mobile app access",
                "Email support",
                "Basic reporting"
            ],
            limitations: [
                "Limited to 3 client locations",
                "Basic visitor management",
                "Standard templates only"
            ],
            popular: false
        },
        professional: {
            name: "Professional",
            description: "Ideal for growing agencies with advanced needs",
            monthly: 5999,
            yearly: 59990,
            features: [
                "Up to 100 guards",
                "Advanced attendance with GPS",
                "Complete payroll with compliance",
                "Unlimited client locations",
                "Advanced visitor management",
                "Custom reporting",
                "Priority support",
                "Mobile & web access",
                "API integrations",
                "Custom branding"
            ],
            limitations: [
                "Advanced analytics add-on",
                "Premium integrations add-on"
            ],
            popular: true
        },
        enterprise: {
            name: "Enterprise",
            description: "For large agencies requiring complete customization",
            monthly: 12999,
            yearly: 129990,
            features: [
                "Unlimited guards",
                "Multi-location management",
                "Advanced analytics & BI",
                "Custom integrations",
                "White-label solution",
                "Dedicated account manager",
                "24/7 premium support",
                "Custom workflows",
                "Advanced security features",
                "Compliance management",
                "API access",
                "Training & onboarding"
            ],
            limitations: [],
            popular: false
        }
    };

    const addOns = [
        {
            name: "Advanced Analytics",
            description: "Business intelligence dashboard with predictive analytics",
            price: 999
        },
        {
            name: "Premium Integrations",
            description: "Connect with 50+ third-party tools and services",
            price: 1499
        },
        {
            name: "Custom Mobile App",
            description: "White-labeled mobile apps for your brand",
            price: 4999
        },
        {
            name: "Dedicated Support",
            description: "Dedicated support manager and priority assistance",
            price: 2999
        }
    ];

    return (
        <MarketingLayout 
            title="Pricing Plans - Affordable Security Management | SecureServe"
            description="Choose the perfect plan for your security agency. Starter ₹2,999/month, Professional ₹5,999/month, Enterprise ₹12,999/month. 14-day free trial. No setup fees."
            keywords="security software pricing, affordable security management, security agency plans, Indian security software cost, payroll software pricing"
            jsonLd={jsonLd}
        >


            {/* Hero Section */}
            <section className="bg-gradient-to-br from-blue-600 to-indigo-700 py-20">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h1 className="text-4xl md:text-5xl font-bold text-white mb-6">
                        Simple, Transparent Pricing
                    </h1>
                    <p className="text-xl text-blue-100 mb-8">
                        Choose the perfect plan for your security agency. Start with a 14-day free trial.
                    </p>
                    
                    {/* Billing Toggle */}
                    <div className="bg-white bg-opacity-20 rounded-lg p-1 inline-flex">
                        <button
                            onClick={() => setBillingPeriod('monthly')}
                            className={`px-6 py-2 rounded-md font-medium transition-all ${
                                billingPeriod === 'monthly'
                                    ? 'bg-white text-blue-600 shadow-sm'
                                    : 'text-blue-100 hover:text-white'
                            }`}
                        >
                            Monthly
                        </button>
                        <button
                            onClick={() => setBillingPeriod('yearly')}
                            className={`px-6 py-2 rounded-md font-medium transition-all ${
                                billingPeriod === 'yearly'
                                    ? 'bg-white text-blue-600 shadow-sm'
                                    : 'text-blue-100 hover:text-white'
                            }`}
                        >
                            Yearly
                            <span className="ml-2 bg-green-500 text-white text-xs px-2 py-1 rounded-full">
                                Save 17%
                            </span>
                        </button>
                    </div>
                </div>
            </section>

            {/* Pricing Cards */}
            <section className="py-20 -mt-10">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        {Object.entries(plans).map(([key, plan]) => (
                            <div
                                key={key}
                                className={`bg-white rounded-2xl shadow-lg overflow-hidden ${
                                    plan.popular ? 'ring-4 ring-blue-500 ring-opacity-50 transform scale-105' : ''
                                }`}
                            >
                                {plan.popular && (
                                    <div className="bg-blue-600 text-white text-center py-3 font-semibold">
                                        Most Popular
                                    </div>
                                )}
                                
                                <div className="p-8">
                                    <h3 className="text-2xl font-bold text-gray-900 mb-2">{plan.name}</h3>
                                    <p className="text-gray-600 mb-6">{plan.description}</p>
                                    
                                    <div className="mb-8">
                                        <div className="flex items-baseline">
                                            <span className="text-4xl font-bold text-gray-900">
                                                ₹{billingPeriod === 'monthly' 
                                                    ? plan.monthly.toLocaleString() 
                                                    : Math.floor(plan.yearly / 12).toLocaleString()
                                                }
                                            </span>
                                            <span className="ml-2 text-gray-600">/month</span>
                                        </div>
                                        {billingPeriod === 'yearly' && (
                                            <div className="text-sm text-gray-500 mt-1">
                                                Billed annually (₹{plan.yearly.toLocaleString()})
                                            </div>
                                        )}
                                    </div>
                                    
                                    <ul className="space-y-4 mb-8">
                                        {plan.features.map((feature, index) => (
                                            <li key={index} className="flex items-start">
                                                <svg className="w-5 h-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                                                </svg>
                                                <span className="text-gray-700">{feature}</span>
                                            </li>
                                        ))}
                                    </ul>
                                    
                                    <Link
                                        href={`/register?plan=${key}&billing=${billingPeriod}`}
                                        className={`block w-full text-center py-3 px-6 rounded-lg font-semibold transition-all ${
                                            plan.popular
                                                ? 'bg-blue-600 text-white hover:bg-blue-700 transform hover:scale-105'
                                                : 'bg-gray-100 text-gray-900 hover:bg-gray-200'
                                        }`}
                                    >
                                        Start Free Trial
                                    </Link>
                                    
                                    <div className="mt-4 text-center text-sm text-gray-500">
                                        14-day free trial • No credit card required
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Add-ons Section */}
            <section className="py-20 bg-white">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center mb-16">
                        <h2 className="text-3xl font-bold text-gray-900 mb-4">
                            Powerful Add-ons
                        </h2>
                        <p className="text-xl text-gray-600">
                            Extend your platform with premium features and integrations
                        </p>
                    </div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        {addOns.map((addon, index) => (
                            <div key={index} className="bg-gray-50 rounded-xl p-6 border border-gray-200 hover:shadow-lg transition-all">
                                <h3 className="text-lg font-semibold text-gray-900 mb-2">
                                    {addon.name}
                                </h3>
                                <p className="text-gray-600 text-sm mb-4">
                                    {addon.description}
                                </p>
                                <div className="flex items-center justify-between">
                                    <span className="text-xl font-bold text-gray-900">
                                        ₹{addon.price.toLocaleString()}/mo
                                    </span>
                                    <button className="text-blue-600 text-sm font-medium hover:text-blue-700">
                                        Add →
                                    </button>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Feature Comparison */}
            <section className="py-20 bg-gray-50">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center mb-16">
                        <h2 className="text-3xl font-bold text-gray-900 mb-4">
                            Compare Plans
                        </h2>
                        <p className="text-xl text-gray-600">
                            Detailed comparison of features across all plans
                        </p>
                    </div>
                    
                    <div className="bg-white rounded-2xl shadow-lg overflow-hidden">
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="text-left py-4 px-6 font-semibold text-gray-900">Features</th>
                                        <th className="text-center py-4 px-6 font-semibold text-gray-900">Starter</th>
                                        <th className="text-center py-4 px-6 font-semibold text-gray-900 bg-blue-50">Professional</th>
                                        <th className="text-center py-4 px-6 font-semibold text-gray-900">Enterprise</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200">
                                    <tr>
                                        <td className="py-4 px-6 font-medium">Number of Guards</td>
                                        <td className="py-4 px-6 text-center">25</td>
                                        <td className="py-4 px-6 text-center bg-blue-50">100</td>
                                        <td className="py-4 px-6 text-center">Unlimited</td>
                                    </tr>
                                    <tr>
                                        <td className="py-4 px-6 font-medium">Client Locations</td>
                                        <td className="py-4 px-6 text-center">3</td>
                                        <td className="py-4 px-6 text-center bg-blue-50">Unlimited</td>
                                        <td className="py-4 px-6 text-center">Unlimited</td>
                                    </tr>
                                    <tr>
                                        <td className="py-4 px-6 font-medium">GPS Attendance</td>
                                        <td className="py-4 px-6 text-center">❌</td>
                                        <td className="py-4 px-6 text-center bg-blue-50">✅</td>
                                        <td className="py-4 px-6 text-center">✅</td>
                                    </tr>
                                    <tr>
                                        <td className="py-4 px-6 font-medium">Advanced Analytics</td>
                                        <td className="py-4 px-6 text-center">❌</td>
                                        <td className="py-4 px-6 text-center bg-blue-50">Add-on</td>
                                        <td className="py-4 px-6 text-center">✅</td>
                                    </tr>
                                    <tr>
                                        <td className="py-4 px-6 font-medium">API Access</td>
                                        <td className="py-4 px-6 text-center">❌</td>
                                        <td className="py-4 px-6 text-center bg-blue-50">✅</td>
                                        <td className="py-4 px-6 text-center">✅</td>
                                    </tr>
                                    <tr>
                                        <td className="py-4 px-6 font-medium">White Labeling</td>
                                        <td className="py-4 px-6 text-center">❌</td>
                                        <td className="py-4 px-6 text-center bg-blue-50">❌</td>
                                        <td className="py-4 px-6 text-center">✅</td>
                                    </tr>
                                    <tr>
                                        <td className="py-4 px-6 font-medium">Support</td>
                                        <td className="py-4 px-6 text-center">Email</td>
                                        <td className="py-4 px-6 text-center bg-blue-50">Priority</td>
                                        <td className="py-4 px-6 text-center">24/7 Dedicated</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            {/* FAQ Section */}
            <section className="py-20 bg-white">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center mb-16">
                        <h2 className="text-3xl font-bold text-gray-900 mb-4">
                            Frequently Asked Questions
                        </h2>
                    </div>
                    
                    <div className="space-y-8">
                        <div>
                            <h3 className="text-lg font-semibold text-gray-900 mb-2">
                                Can I change my plan anytime?
                            </h3>
                            <p className="text-gray-600">
                                Yes, you can upgrade or downgrade your plan at any time. Changes take effect immediately, and we'll prorate the billing accordingly.
                            </p>
                        </div>
                        
                        <div>
                            <h3 className="text-lg font-semibold text-gray-900 mb-2">
                                What happens during the free trial?
                            </h3>
                            <p className="text-gray-600">
                                You get full access to all Professional plan features for 14 days. No credit card required. After the trial, you can choose to subscribe or the account will be suspended.
                            </p>
                        </div>
                        
                        <div>
                            <h3 className="text-lg font-semibold text-gray-900 mb-2">
                                Do you provide data migration assistance?
                            </h3>
                            <p className="text-gray-600">
                                Yes! Our team provides free data migration assistance for Professional and Enterprise plans. We'll help you import your existing data seamlessly.
                            </p>
                        </div>
                        
                        <div>
                            <h3 className="text-lg font-semibold text-gray-900 mb-2">
                                Is my data secure?
                            </h3>
                            <p className="text-gray-600">
                                Absolutely. We use enterprise-grade security with data encryption, regular backups, and compliance with Indian data protection regulations.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {/* CTA Section */}
            <section className="py-20 bg-blue-600">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h2 className="text-3xl font-bold text-white mb-6">
                        Ready to Get Started?
                    </h2>
                    <p className="text-xl text-blue-100 mb-8">
                        Start your free trial today. No credit card required.
                    </p>
                    <div className="flex flex-col sm:flex-row gap-4 justify-center">
                        <Link href="/register" className="bg-white text-blue-600 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-gray-100 transition-all transform hover:scale-105">
                            Start Free Trial
                        </Link>
                        <a href="mailto:sales@secureserve.com" className="border-2 border-white text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-white hover:text-blue-600 transition-colors">
                            Contact Sales
                        </a>
                    </div>
                </div>
            </section>
        </MarketingLayout>
    );
}