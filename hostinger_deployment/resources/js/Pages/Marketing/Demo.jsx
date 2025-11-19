import React, { useState } from 'react';
import { Link } from '@inertiajs/react';
import MarketingLayout from '@/Components/MarketingLayout.jsx';

export default function Demo() {
    const [activeDemo, setActiveDemo] = useState('dashboard');

    const demoScreenshots = {
        dashboard: {
            title: "Dashboard Overview",
            description: "Get a complete overview of your security operations with real-time metrics and insights.",
            image: "/images/demo/dashboard.png",
            features: [
                "Real-time guard status",
                "Active client locations", 
                "Today's attendance summary",
                "Revenue analytics",
                "Quick action buttons",
                "Notification center"
            ]
        },
        employees: {
            title: "Employee Management",
            description: "Comprehensive guard profiles with all necessary documentation and tracking.",
            image: "/images/demo/employees.png", 
            features: [
                "Complete guard profiles",
                "Identity document storage",
                "Family member records",
                "Uniform allocation tracking",
                "Performance metrics",
                "Training certifications"
            ]
        },
        attendance: {
            title: "Smart Attendance",
            description: "GPS-verified attendance tracking with QR codes and real-time monitoring.",
            image: "/images/demo/attendance.png",
            features: [
                "GPS location verification",
                "QR code check-in",
                "Real-time status updates",
                "Overtime calculations",
                "Shift pattern management",
                "Automated timesheets"
            ]
        },
        payroll: {
            title: "Indian Payroll System",
            description: "Complete payroll processing with Indian tax compliance and statutory deductions.",
            image: "/images/demo/payroll.png",
            features: [
                "Auto tax calculations",
                "EPF/ESIC compliance",
                "Professional tax by state",
                "TDS management",
                "PDF payslip generation",
                "Bank transfer files"
            ]
        },
        clients: {
            title: "Client Management", 
            description: "Manage client relationships, contracts, and service locations efficiently.",
            image: "/images/demo/clients.png",
            features: [
                "Client profile management",
                "Contract tracking",
                "Multiple service locations",
                "Guard assignments",
                "Service billing",
                "Communication history"
            ]
        },
        visitors: {
            title: "Visitor Management",
            description: "Enterprise-grade visitor tracking with QR codes and security screening.",
            image: "/images/demo/visitors.png",
            features: [
                "QR code generation",
                "Photo capture",
                "Background verification",
                "Watchlist screening",
                "Host notifications",
                "Analytics dashboard"
            ]
        }
    };

    const jsonLd = {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "SecureServe Demo",
        "applicationCategory": "SecurityManagement",
        "description": "Interactive demo of SecureServe security management platform featuring dashboard overview, employee management, and visitor control systems.",
        "url": window.location.href,
        "operatingSystem": "Web Browser",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "INR",
            "description": "Free interactive demo"
        },
        "provider": {
            "@type": "Organization",
            "name": "SecureServe",
            "description": "Complete security management platform for Indian agencies"
        }
    };

    return (
        <MarketingLayout 
            title="Interactive Demo - See SecureServe in Action | SecureServe"
            description="Experience SecureServe's powerful features with our interactive demo. Explore dashboard, employee management, attendance tracking, and visitor control systems."
            keywords="security software demo, interactive demo, employee management demo, visitor management demo, security platform preview"
            jsonLd={jsonLd}
        >
            {/* Hero Section */}
            <section className="bg-gradient-to-br from-blue-600 to-indigo-700 py-16">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h1 className="text-4xl md:text-5xl font-bold text-white mb-6">
                        See SecureServe in Action
                    </h1>
                    <p className="text-xl text-blue-100 max-w-3xl mx-auto">
                        Explore our comprehensive security management platform through interactive demos and screenshots
                    </p>
                </div>
            </section>

            {/* Demo Navigation */}
            <section className="py-8 bg-white border-b">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex flex-wrap justify-center gap-4">
                        {Object.entries(demoScreenshots).map(([key, demo]) => (
                            <button
                                key={key}
                                onClick={() => setActiveDemo(key)}
                                className={`px-6 py-3 rounded-lg font-semibold transition-all ${
                                    activeDemo === key
                                        ? 'bg-blue-600 text-white shadow-lg'
                                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                }`}
                            >
                                {demo.title}
                            </button>
                        ))}
                    </div>
                </div>
            </section>

            {/* Demo Content */}
            <section className="py-16">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                        {/* Demo Description */}
                        <div>
                            <h2 className="text-3xl font-bold text-gray-900 mb-4">
                                {demoScreenshots[activeDemo].title}
                            </h2>
                            <p className="text-xl text-gray-600 mb-8">
                                {demoScreenshots[activeDemo].description}
                            </p>
                            
                            <div className="space-y-4">
                                <h3 className="text-lg font-semibold text-gray-900">Key Features:</h3>
                                <ul className="space-y-3">
                                    {demoScreenshots[activeDemo].features.map((feature, index) => (
                                        <li key={index} className="flex items-start">
                                            <svg className="w-5 h-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                                            </svg>
                                            <span className="text-gray-700">{feature}</span>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                            
                            <div className="mt-8 flex flex-col sm:flex-row gap-4">
                                <Link href="/register" className="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-all transform hover:scale-105 text-center">
                                    Start Free Trial
                                </Link>
                                <Link href="/admin" className="border border-gray-300 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-50 transition-colors text-center">
                                    Try Live Demo
                                </Link>
                            </div>
                        </div>
                        
                        {/* Demo Screenshot */}
                        <div>
                            <div className="bg-white rounded-xl shadow-2xl overflow-hidden border">
                                <div className="bg-gray-100 px-4 py-2 flex items-center space-x-2">
                                    <div className="w-3 h-3 bg-red-400 rounded-full"></div>
                                    <div className="w-3 h-3 bg-yellow-400 rounded-full"></div>
                                    <div className="w-3 h-3 bg-green-400 rounded-full"></div>
                                    <div className="ml-4 text-sm text-gray-600">{demoScreenshots[activeDemo].title}</div>
                                </div>
                                <div className="p-8 bg-gradient-to-br from-blue-50 to-indigo-100 min-h-96 flex items-center justify-center">
                                    <div className="text-center">
                                        <div className="w-24 h-24 bg-blue-600 rounded-full mx-auto mb-4 flex items-center justify-center">
                                            {activeDemo === 'dashboard' && (
                                                <svg className="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                                </svg>
                                            )}
                                            {activeDemo === 'employees' && (
                                                <svg className="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                                </svg>
                                            )}
                                            {activeDemo === 'attendance' && (
                                                <svg className="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            )}
                                            {activeDemo === 'payroll' && (
                                                <svg className="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                                </svg>
                                            )}
                                            {activeDemo === 'clients' && (
                                                <svg className="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                </svg>
                                            )}
                                            {activeDemo === 'visitors' && (
                                                <svg className="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                                </svg>
                                            )}
                                        </div>
                                        <h3 className="text-2xl font-bold text-gray-800 mb-2">{demoScreenshots[activeDemo].title}</h3>
                                        <p className="text-gray-600">Interactive demo screenshot</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* Video Demo Section */}
            <section className="py-16 bg-white">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h2 className="text-3xl font-bold text-gray-900 mb-6">
                        Watch SecureServe in Action
                    </h2>
                    <p className="text-xl text-gray-600 mb-8">
                        See how our platform streamlines security operations in this 3-minute overview
                    </p>
                    
                    <div className="bg-black rounded-xl overflow-hidden shadow-2xl">
                        <div className="aspect-w-16 aspect-h-9 bg-gradient-to-br from-gray-800 to-gray-900 flex items-center justify-center min-h-96">
                            <div className="text-center text-white">
                                <svg className="w-16 h-16 mx-auto mb-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clipRule="evenodd" />
                                </svg>
                                <h3 className="text-xl font-semibold mb-2">Video Demo</h3>
                                <p className="text-gray-300">3-minute platform overview</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* Live Demo CTA */}
            <section className="py-16 bg-gradient-to-r from-blue-600 to-indigo-600">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h2 className="text-3xl font-bold text-white mb-6">
                        Ready to Try the Real Platform?
                    </h2>
                    <p className="text-xl text-blue-100 mb-8">
                        Experience SecureServe with real data and full functionality
                    </p>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div className="bg-white bg-opacity-10 rounded-lg p-6 text-left">
                            <h3 className="text-xl font-semibold text-white mb-3">Live Demo Access</h3>
                            <p className="text-blue-100 mb-4">
                                Try our fully functional demo with sample data. No registration required.
                            </p>
                            <Link href="/admin" className="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors inline-block">
                                Access Live Demo
                            </Link>
                        </div>
                        
                        <div className="bg-white bg-opacity-10 rounded-lg p-6 text-left">
                            <h3 className="text-xl font-semibold text-white mb-3">Free Trial</h3>
                            <p className="text-blue-100 mb-4">
                                Set up your own instance with 14 days of full access. No credit card needed.
                            </p>
                            <Link href="/register" className="bg-blue-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-600 transition-colors inline-block">
                                Start Free Trial
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            {/* Demo Credentials */}
            <section className="py-12 bg-gray-100">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center mb-8">
                        <h3 className="text-2xl font-bold text-gray-900 mb-4">Demo Login Credentials</h3>
                        <p className="text-gray-600">Use these credentials to explore different user roles in our live demo</p>
                    </div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div className="bg-white rounded-lg p-6 border">
                            <h4 className="font-semibold text-gray-900 mb-2">Super Admin</h4>
                            <p className="text-sm text-gray-600 mb-3">Full system access</p>
                            <div className="text-sm">
                                <p><strong>Email:</strong> super_admin@example.test</p>
                                <p><strong>Password:</strong> password</p>
                            </div>
                        </div>
                        
                        <div className="bg-white rounded-lg p-6 border">
                            <h4 className="font-semibold text-gray-900 mb-2">Agency Owner</h4>
                            <p className="text-sm text-gray-600 mb-3">Business management</p>
                            <div className="text-sm">
                                <p><strong>Email:</strong> agency_owner@example.test</p>
                                <p><strong>Password:</strong> password</p>
                            </div>
                        </div>
                        
                        <div className="bg-white rounded-lg p-6 border">
                            <h4 className="font-semibold text-gray-900 mb-2">HR Manager</h4>
                            <p className="text-sm text-gray-600 mb-3">Employee management</p>
                            <div className="text-sm">
                                <p><strong>Email:</strong> hr@example.test</p>
                                <p><strong>Password:</strong> password</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </MarketingLayout>
    );
}