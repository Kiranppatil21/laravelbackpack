import React from 'react';
import { Link } from '@inertiajs/react';

export default function Success({ tenant, login_url }) {
    return (
        <div className="min-h-screen bg-gray-50">
            {/* Navigation */}
            <nav className="bg-white shadow-sm border-b">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16">
                        <div className="flex items-center">
                            <div className="flex items-center">
                                <div className="h-8 w-8 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
                                    <span className="text-white font-bold text-sm">SS</span>
                                </div>
                                <span className="ml-2 text-xl font-bold text-gray-900">SecureServe</span>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            {/* Success Section */}
            <section className="py-16">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    {/* Success Icon */}
                    <div className="mx-auto mb-8 w-24 h-24 bg-green-100 rounded-full flex items-center justify-center">
                        <svg className="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>

                    <h1 className="text-4xl font-bold text-gray-900 mb-4">
                        Welcome to SecureServe!
                    </h1>
                    
                    <p className="text-xl text-gray-600 mb-8">
                        Your payment was successful and your account is now active.
                    </p>

                    {/* Account Details */}
                    <div className="bg-white rounded-lg shadow-lg p-8 mb-8 text-left max-w-2xl mx-auto">
                        <h2 className="text-2xl font-bold text-gray-900 mb-6 text-center">Account Details</h2>
                        
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 className="text-lg font-semibold text-gray-900 mb-2">Company Information</h3>
                                <div className="space-y-2 text-gray-600">
                                    <p><strong>Company:</strong> {tenant.name}</p>
                                    <p><strong>Email:</strong> {tenant.email}</p>
                                    <p><strong>Phone:</strong> {tenant.phone}</p>
                                </div>
                            </div>
                            
                            <div>
                                <h3 className="text-lg font-semibold text-gray-900 mb-2">Subscription Details</h3>
                                <div className="space-y-2 text-gray-600">
                                    <p><strong>Plan:</strong> {tenant.plan} Plan</p>
                                    <p><strong>Status:</strong> <span className="text-green-600 font-medium">Active</span></p>
                                    <p><strong>Trial:</strong> 14 days remaining</p>
                                </div>
                            </div>
                        </div>
                        
                        <hr className="my-6" />
                        
                        <div className="text-center">
                            <h3 className="text-lg font-semibold text-gray-900 mb-2">Your Dashboard URL</h3>
                            <p className="text-blue-600 font-mono text-sm bg-blue-50 px-4 py-2 rounded-lg">
                                {login_url}
                            </p>
                        </div>
                    </div>

                    {/* Next Steps */}
                    <div className="bg-blue-50 rounded-lg p-8 mb-8 text-left max-w-2xl mx-auto">
                        <h2 className="text-2xl font-bold text-gray-900 mb-6 text-center">Next Steps</h2>
                        
                        <div className="space-y-4">
                            <div className="flex items-start">
                                <div className="flex-shrink-0 w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm mr-4">1</div>
                                <div>
                                    <h3 className="font-semibold text-gray-900">Access Your Dashboard</h3>
                                    <p className="text-gray-600">Log in to your personalized dashboard and explore the features.</p>
                                </div>
                            </div>
                            
                            <div className="flex items-start">
                                <div className="flex-shrink-0 w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm mr-4">2</div>
                                <div>
                                    <h3 className="font-semibold text-gray-900">Set Up Your Company Profile</h3>
                                    <p className="text-gray-600">Complete your company information and upload your logo.</p>
                                </div>
                            </div>
                            
                            <div className="flex items-start">
                                <div className="flex-shrink-0 w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm mr-4">3</div>
                                <div>
                                    <h3 className="font-semibold text-gray-900">Add Your Employees</h3>
                                    <p className="text-gray-600">Start by adding your security guards and staff members.</p>
                                </div>
                            </div>
                            
                            <div className="flex items-start">
                                <div className="flex-shrink-0 w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm mr-4">4</div>
                                <div>
                                    <h3 className="font-semibold text-gray-900">Configure Client Locations</h3>
                                    <p className="text-gray-600">Set up your client sites and assign security guards.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Action Buttons */}
                    <div className="flex flex-col sm:flex-row gap-4 justify-center">
                        <a
                            href={login_url}
                            className="bg-blue-600 text-white px-8 py-4 rounded-lg font-semibold text-lg hover:bg-blue-700 transition-colors"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Access Your Dashboard
                        </a>
                        
                        <Link
                            href="/demo"
                            className="border border-gray-300 text-gray-700 px-8 py-4 rounded-lg font-semibold text-lg hover:bg-gray-50 transition-colors"
                        >
                            View Platform Demo
                        </Link>
                    </div>

                    <div className="mt-8 text-center">
                        <p className="text-gray-600">
                            Need help getting started? 
                            <a href="mailto:support@secureserve.com" className="text-blue-600 hover:underline ml-1">
                                Contact our support team
                            </a>
                        </p>
                    </div>
                </div>
            </section>

            {/* Support Section */}
            <section className="py-16 bg-white">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                    <h2 className="text-3xl font-bold text-gray-900 text-center mb-12">
                        Get Support & Resources
                    </h2>
                    
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div className="text-center p-6 bg-gray-50 rounded-lg">
                            <div className="w-12 h-12 bg-blue-600 rounded-lg mx-auto mb-4 flex items-center justify-center">
                                <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <h3 className="text-lg font-semibold text-gray-900 mb-2">Documentation</h3>
                            <p className="text-gray-600 mb-4">Comprehensive guides and tutorials to help you get the most out of SecureServe.</p>
                            <a href="#" className="text-blue-600 hover:underline">Browse Docs →</a>
                        </div>
                        
                        <div className="text-center p-6 bg-gray-50 rounded-lg">
                            <div className="w-12 h-12 bg-blue-600 rounded-lg mx-auto mb-4 flex items-center justify-center">
                                <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <h3 className="text-lg font-semibold text-gray-900 mb-2">24/7 Support</h3>
                            <p className="text-gray-600 mb-4">Our dedicated support team is available around the clock to assist you.</p>
                            <a href="mailto:support@secureserve.com" className="text-blue-600 hover:underline">Get Support →</a>
                        </div>
                        
                        <div className="text-center p-6 bg-gray-50 rounded-lg">
                            <div className="w-12 h-12 bg-blue-600 rounded-lg mx-auto mb-4 flex items-center justify-center">
                                <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 className="text-lg font-semibold text-gray-900 mb-2">Video Tutorials</h3>
                            <p className="text-gray-600 mb-4">Step-by-step video guides to help you master every feature quickly.</p>
                            <a href="#" className="text-blue-600 hover:underline">Watch Videos →</a>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    );
}