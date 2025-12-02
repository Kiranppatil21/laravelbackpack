import React from 'react';
import { Link } from '@inertiajs/react';
import MarketingLayout from '@/Components/MarketingLayout.jsx';

export default function Landing() {
    const jsonLd = {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "SecureServe",
        "description": "Complete security management platform for employee tracking, payroll management, and visitor control in India",
        "url": "https://secureserve.com",
        "applicationCategory": "SecurityApplication",
        "operatingSystem": "Web, iOS, Android",
        "offers": [
            {
                "@type": "Offer",
                "name": "Starter Plan",
                "price": "2999",
                "priceCurrency": "INR"
            },
            {
                "@type": "Offer", 
                "name": "Professional Plan",
                "price": "5999",
                "priceCurrency": "INR"
            },
            {
                "@type": "Offer",
                "name": "Enterprise Plan", 
                "price": "12999",
                "priceCurrency": "INR"
            }
        ]
    };

    return (
        <MarketingLayout 
            title="SecureServe - Complete Security Management Platform for India"
            description="Streamline your security operations with SecureServe. GPS attendance tracking, Indian payroll compliance, employee management, and visitor control system. Start your 14-day free trial."
            keywords="security management software, employee tracking India, GPS attendance, payroll software, visitor management system, security guards management, Indian compliance"
            jsonLd={jsonLd}
        >
            {/* Hero Section */}
            <section className="relative bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 overflow-hidden">
                {/* Background decorations */}
                <div className="absolute inset-0 bg-black opacity-10"></div>
                <div className="absolute inset-0">
                    <div className="absolute top-1/4 left-1/4 w-64 h-64 bg-white opacity-5 rounded-full blur-3xl"></div>
                    <div className="absolute bottom-1/4 right-1/4 w-96 h-96 bg-white opacity-5 rounded-full blur-3xl"></div>
                </div>
                
                <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 lg:py-24">
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">
                        {/* Hero Content */}
                        <div className="text-center lg:text-left">
                            <h1 className="text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-6 leading-tight">
                                Complete Security 
                                <span className="block text-blue-200">Management Platform</span>
                            </h1>
                            
                            <p className="text-lg sm:text-xl text-blue-100 mb-8 max-w-2xl mx-auto lg:mx-0">
                                Streamline your security operations with GPS attendance, Indian payroll compliance, 
                                employee management, and visitor control in one powerful platform.
                            </p>
                            
                            <div className="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                                <Link 
                                    href="/register" 
                                    className="bg-white text-blue-600 px-8 py-4 rounded-xl font-semibold text-lg hover:bg-blue-50 transition-all transform hover:scale-105 shadow-lg"
                                >
                                    Start 14-Day Free Trial
                                </Link>
                                <Link 
                                    href="/demo" 
                                    className="border-2 border-white text-white px-8 py-4 rounded-xl font-semibold text-lg hover:bg-white hover:text-blue-600 transition-all"
                                >
                                    Watch Demo
                                </Link>
                            </div>
                            
                            <div className="flex flex-wrap items-center justify-center lg:justify-start gap-6 mt-8 text-blue-100">
                                <div className="flex items-center">
                                    <svg className="w-5 h-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                                    </svg>
                                    14-day free trial
                                </div>
                                <div className="flex items-center">
                                    <svg className="w-5 h-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                                    </svg>
                                    No credit card required
                                </div>
                                <div className="flex items-center">
                                    <svg className="w-5 h-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                                    </svg>
                                    Indian compliance
                                </div>
                            </div>
                        </div>
                        
                        {/* Hero Visual */}
                        <div className="relative mt-8 lg:mt-0">
                            <div className="relative z-10 bg-white rounded-2xl shadow-2xl overflow-hidden">
                                <div className="bg-gray-100 px-4 py-3 flex items-center space-x-2">
                                    <div className="w-3 h-3 bg-red-400 rounded-full"></div>
                                    <div className="w-3 h-3 bg-yellow-400 rounded-full"></div>
                                    <div className="w-3 h-3 bg-green-400 rounded-full"></div>
                                    <div className="ml-4 text-sm text-gray-600">SecureServe Dashboard</div>
                                </div>
                                <div className="p-8 bg-gradient-to-br from-blue-50 to-indigo-100 min-h-80 sm:min-h-96">
                                    <div className="grid grid-cols-2 gap-4 mb-6">
                                        <div className="bg-white rounded-lg p-4 shadow-sm">
                                            <div className="flex items-center justify-between mb-2">
                                                <span className="text-sm text-gray-600">Active Guards</span>
                                                <div className="w-2 h-2 bg-green-500 rounded-full"></div>
                                            </div>
                                            <div className="text-2xl font-bold text-gray-900">127</div>
                                        </div>
                                        <div className="bg-white rounded-lg p-4 shadow-sm">
                                            <div className="flex items-center justify-between mb-2">
                                                <span className="text-sm text-gray-600">Today's Attendance</span>
                                                <div className="w-2 h-2 bg-blue-500 rounded-full"></div>
                                            </div>
                                            <div className="text-2xl font-bold text-gray-900">98.5%</div>
                                        </div>
                                    </div>
                                    <div className="bg-white rounded-lg p-4 shadow-sm">
                                        <h3 className="text-sm text-gray-600 mb-3">Recent Activities</h3>
                                        <div className="space-y-2">
                                            <div className="flex items-center text-sm">
                                                <div className="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                                                <span className="text-gray-700">Guard checked in at Location A</span>
                                                <span className="ml-auto text-gray-500 hidden sm:block">2m ago</span>
                                            </div>
                                            <div className="flex items-center text-sm">
                                                <div className="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
                                                <span className="text-gray-700">Visitor registered at Building B</span>
                                                <span className="ml-auto text-gray-500 hidden sm:block">5m ago</span>
                                            </div>
                                            <div className="flex items-center text-sm">
                                                <div className="w-2 h-2 bg-yellow-500 rounded-full mr-3"></div>
                                                <span className="text-gray-700">Payroll generated for Oct 2025</span>
                                                <span className="ml-auto text-gray-500 hidden sm:block">1h ago</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            {/* Floating cards */}
                            <div className="absolute -top-4 -right-4 bg-white rounded-lg shadow-lg p-4 z-20 hidden sm:block">
                                <div className="flex items-center">
                                    <div className="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                        <svg className="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div className="text-sm font-semibold text-gray-900">GPS Verified</div>
                                        <div className="text-xs text-gray-600">Real-time tracking</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div className="absolute -bottom-4 -left-4 bg-white rounded-lg shadow-lg p-4 z-20 hidden sm:block">
                                <div className="flex items-center">
                                    <div className="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                        <svg className="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div className="text-sm font-semibold text-gray-900">Compliant</div>
                                        <div className="text-xs text-gray-600">Indian labor laws</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* Features Overview */}
            <section className="py-16 sm:py-20 bg-white" id="features">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center mb-16">
                        <h2 className="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                            Everything You Need to Manage Security Operations
                        </h2>
                        <p className="text-lg sm:text-xl text-gray-600 max-w-3xl mx-auto">
                            From employee onboarding to payroll processing, SecureServe provides all the tools 
                            you need to run a successful security agency in India.
                        </p>
                    </div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        {/* Feature 1 */}
                        <div className="text-center p-6 rounded-2xl bg-gradient-to-br from-blue-50 to-blue-100 hover:shadow-lg transition-all">
                            <div className="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                                <svg className="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <h3 className="text-xl font-semibold text-gray-900 mb-3">Employee Management</h3>
                            <p className="text-gray-600">Complete guard profiles with documents, family records, and training certifications.</p>
                        </div>

                        {/* Feature 2 */}
                        <div className="text-center p-6 rounded-2xl bg-gradient-to-br from-green-50 to-green-100 hover:shadow-lg transition-all">
                            <div className="w-16 h-16 bg-green-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                                <svg className="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <h3 className="text-xl font-semibold text-gray-900 mb-3">GPS Attendance</h3>
                            <p className="text-gray-600">Real-time location verification with QR code scanning and automated timesheets.</p>
                        </div>

                        {/* Feature 3 */}
                        <div className="text-center p-6 rounded-2xl bg-gradient-to-br from-purple-50 to-purple-100 hover:shadow-lg transition-all">
                            <div className="w-16 h-16 bg-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                                <svg className="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                </svg>
                            </div>
                            <h3 className="text-xl font-semibold text-gray-900 mb-3">Indian Payroll</h3>
                            <p className="text-gray-600">Complete compliance with EPF, ESIC, TDS, and state-specific professional tax calculations.</p>
                        </div>

                        {/* Feature 4 */}
                        <div className="text-center p-6 rounded-2xl bg-gradient-to-br from-orange-50 to-orange-100 hover:shadow-lg transition-all">
                            <div className="w-16 h-16 bg-orange-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                                <svg className="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <h3 className="text-xl font-semibold text-gray-900 mb-3">Client Management</h3>
                            <p className="text-gray-600">Manage client relationships, contracts, and multiple service locations efficiently.</p>
                        </div>

                        {/* Feature 5 */}
                        <div className="text-center p-6 rounded-2xl bg-gradient-to-br from-red-50 to-red-100 hover:shadow-lg transition-all">
                            <div className="w-16 h-16 bg-red-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                                <svg className="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                            </div>
                            <h3 className="text-xl font-semibold text-gray-900 mb-3">Visitor Management</h3>
                            <p className="text-gray-600">QR code-based visitor registration with photo capture and background verification.</p>
                        </div>

                        {/* Feature 6 */}
                        <div className="text-center p-6 rounded-2xl bg-gradient-to-br from-indigo-50 to-indigo-100 hover:shadow-lg transition-all">
                            <div className="w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                                <svg className="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <h3 className="text-xl font-semibold text-gray-900 mb-3">Reports & Analytics</h3>
                            <p className="text-gray-600">Comprehensive reporting with attendance analytics, payroll summaries, and client billing.</p>
                        </div>
                    </div>

                    <div className="text-center mt-12">
                        <Link href="/features" className="bg-blue-600 text-white px-8 py-4 rounded-lg font-semibold text-lg hover:bg-blue-700 transition-colors">
                            Explore All Features
                        </Link>
                    </div>
                </div>
            </section>

            {/* Social Proof / Stats */}
            <section className="py-16 bg-gray-50">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center mb-12">
                        <h2 className="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                            Trusted by Security Agencies Across India
                        </h2>
                        <p className="text-lg text-gray-600">
                            Join hundreds of agencies who have transformed their operations with SecureServe
                        </p>
                    </div>
                    
                    <div className="grid grid-cols-2 lg:grid-cols-4 gap-8">
                        <div className="text-center">
                            <div className="text-4xl font-bold text-blue-600 mb-2">500+</div>
                            <p className="text-gray-600">Security Agencies</p>
                        </div>
                        <div className="text-center">
                            <div className="text-4xl font-bold text-blue-600 mb-2">50K+</div>
                            <p className="text-gray-600">Guards Managed</p>
                        </div>
                        <div className="text-center">
                            <div className="text-4xl font-bold text-blue-600 mb-2">98.5%</div>
                            <p className="text-gray-600">Attendance Accuracy</p>
                        </div>
                        <div className="text-center">
                            <div className="text-4xl font-bold text-blue-600 mb-2">₹100Cr+</div>
                            <p className="text-gray-600">Payroll Processed</p>
                        </div>
                    </div>
                </div>
            </section>

            {/* CTA Section */}
            <section className="py-16 sm:py-20 bg-gradient-to-r from-blue-600 to-indigo-600">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h2 className="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-6">
                        Ready to Transform Your Security Operations?
                    </h2>
                    <p className="text-lg sm:text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
                        Join thousands of security agencies who trust SecureServe to manage their operations efficiently.
                    </p>
                    <div className="flex flex-col sm:flex-row gap-4 justify-center">
                        <Link href="/register" className="bg-white text-blue-600 px-8 py-4 rounded-lg font-semibold text-lg hover:bg-blue-50 transition-all">
                            Start Free Trial
                        </Link>
                        <Link href="/pricing" className="border border-white text-white px-8 py-4 rounded-lg font-semibold text-lg hover:bg-white hover:text-blue-600 transition-all">
                            View Pricing
                        </Link>
                    </div>
                </div>
            </section>
        </MarketingLayout>
    );
}