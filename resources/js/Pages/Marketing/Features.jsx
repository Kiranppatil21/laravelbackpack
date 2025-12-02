import React, { useState } from 'react';
import { Link } from '@inertiajs/react';
import MarketingLayout from '@/Components/MarketingLayout.jsx';

export default function Features() {
    const [activeTab, setActiveTab] = useState('employee');

    const jsonLd = {
        "@context": "https://schema.org",
        "@type": "WebPage",
        "name": "SecureServe Features - Complete Security Management Tools",
        "description": "Explore all features of SecureServe security management platform including employee management, GPS attendance, Indian payroll, client management, and visitor control.",
        "breadcrumb": {
            "@type": "BreadcrumbList",
            "itemListElement": [
                {
                    "@type": "ListItem",
                    "position": 1,
                    "name": "Home",
                    "item": "https://secureserve.com"
                },
                {
                    "@type": "ListItem",
                    "position": 2,
                    "name": "Features",
                    "item": "https://secureserve.com/features"
                }
            ]
        }
    };

    const features = {
        employee: {
            title: "Complete Employee Management",
            description: "Manage your entire security workforce with comprehensive profiles and tracking",
            items: [
                {
                    icon: "👤",
                    title: "Digital Identity Management",
                    description: "Store and verify identity documents, photos, and background checks digitally"
                },
                {
                    icon: "👨‍👩‍👧‍👦",
                    title: "Family Records",
                    description: "Maintain detailed family member information for emergency contacts and benefits"
                },
                {
                    icon: "🦺",
                    title: "Uniform & Equipment Tracking",
                    description: "Track issued uniforms, equipment, and return schedules automatically"
                },
                {
                    icon: "📊",
                    title: "Performance Analytics",
                    description: "Monitor guard performance with attendance, client feedback, and incident reports"
                },
                {
                    icon: "📱",
                    title: "Mobile Self-Service",
                    description: "Guards can update their profiles, view schedules, and submit requests via mobile"
                },
                {
                    icon: "🎓",
                    title: "Training & Certification",
                    description: "Track training completion, certifications, and renewal requirements"
                }
            ]
        },
        attendance: {
            title: "Smart Attendance System",
            description: "Real-time attendance tracking with GPS verification and automated reporting",
            items: [
                {
                    icon: "📍",
                    title: "GPS Location Verification",
                    description: "Ensure guards are at assigned locations with automatic GPS verification"
                },
                {
                    icon: "📱",
                    title: "QR Code Check-in",
                    description: "Quick and secure check-in using QR codes at client premises"
                },
                {
                    icon: "⏰",
                    title: "Real-time Monitoring",
                    description: "Live dashboard showing who's on duty, late arrivals, and early departures"
                },
                {
                    icon: "📋",
                    title: "Automated Timesheets",
                    description: "Generate accurate timesheets automatically from attendance data"
                },
                {
                    icon: "🚨",
                    title: "Smart Alerts",
                    description: "Instant notifications for no-shows, overtime, and schedule violations"
                },
                {
                    icon: "📈",
                    title: "Attendance Analytics",
                    description: "Detailed reports on attendance patterns, overtime trends, and productivity"
                }
            ]
        },
        payroll: {
            title: "Indian Payroll Compliance",
            description: "Automated payroll processing with full Indian tax law compliance",
            items: [
                {
                    icon: "💰",
                    title: "Auto Tax Calculations",
                    description: "Support for both old and new tax regimes with automatic calculations"
                },
                {
                    icon: "🏛️",
                    title: "EPF/ESIC Integration",
                    description: "Automatic EPF and ESIC calculations with statutory compliance"
                },
                {
                    icon: "📄",
                    title: "Professional Tax",
                    description: "State-wise professional tax calculations and deductions"
                },
                {
                    icon: "📊",
                    title: "TDS Management",
                    description: "Automatic TDS calculations and certificate generation"
                },
                {
                    icon: "📋",
                    title: "PDF Payslips",
                    description: "Professional payslips with company branding and detailed breakdowns"
                },
                {
                    icon: "💳",
                    title: "Bank Integration",
                    description: "Direct bank transfer files and payment reconciliation"
                }
            ]
        },
        client: {
            title: "Client Relationship Management",
            description: "Complete client management with contracts, billing, and service tracking",
            items: [
                {
                    icon: "🏢",
                    title: "Contract Management",
                    description: "Digital contract storage with renewal alerts and amendment tracking"
                },
                {
                    icon: "📍",
                    title: "Multi-location Support",
                    description: "Manage clients with multiple service locations and site-specific requirements"
                },
                {
                    icon: "👮",
                    title: "Guard Assignment",
                    description: "Assign guards to specific clients with skill matching and availability"
                },
                {
                    icon: "💰",
                    title: "Service Billing",
                    description: "Automated billing based on guard hours and service rates"
                },
                {
                    icon: "📞",
                    title: "Communication Hub",
                    description: "Centralized communication with clients including reports and updates"
                },
                {
                    icon: "⭐",
                    title: "Service Quality Tracking",
                    description: "Client feedback collection and service quality monitoring"
                }
            ]
        },
        visitor: {
            title: "Advanced Visitor Management",
            description: "Enterprise-grade visitor tracking with security screening and mobile integration",
            items: [
                {
                    icon: "📱",
                    title: "QR Code Generation",
                    description: "Dynamic QR codes for contactless visitor check-in with security features"
                },
                {
                    icon: "🔍",
                    title: "Background Verification",
                    description: "Automated background checks and watchlist screening for visitors"
                },
                {
                    icon: "📷",
                    title: "Photo Capture",
                    description: "Automatic photo capture during registration for security records"
                },
                {
                    icon: "🤖",
                    title: "IoT Integration",
                    description: "Connect with kiosks, tablets, and access control systems"
                },
                {
                    icon: "📊",
                    title: "Real-time Analytics",
                    description: "Live visitor counts, capacity monitoring, and security alerts"
                },
                {
                    icon: "🔔",
                    title: "Host Notifications",
                    description: "Instant notifications to hosts when their visitors arrive"
                }
            ]
        },
        finance: {
            title: "Financial Management & Compliance",
            description: "Complete financial management with Indian statutory compliance",
            items: [
                {
                    icon: "📄",
                    title: "Automated Invoicing",
                    description: "Generate professional invoices with GST calculations and compliance"
                },
                {
                    icon: "💳",
                    title: "Payment Tracking",
                    description: "Track payments, pending amounts, and payment reconciliation"
                },
                {
                    icon: "📊",
                    title: "GST Compliance",
                    description: "GST return preparation and filing with detailed transaction records"
                },
                {
                    icon: "💼",
                    title: "Vendor Management",
                    description: "Manage vendor payments with TDS calculations and compliance"
                },
                {
                    icon: "📈",
                    title: "Profitability Analysis",
                    description: "Detailed P&L reports, margin analysis, and financial forecasting"
                },
                {
                    icon: "🏛️",
                    title: "Statutory Reports",
                    description: "Generate PF, ESIC, and other statutory reports for compliance"
                }
            ]
        }
    };

    return (
        <MarketingLayout 
            title="Features - Complete Security Management Tools | SecureServe"
            description="Explore comprehensive features of SecureServe: Employee management, GPS attendance tracking, Indian payroll compliance, client management, visitor control, and detailed reporting."
            keywords="security software features, employee management system, GPS attendance tracking, Indian payroll software, visitor management, security analytics"
            jsonLd={jsonLd}
        >


            {/* Hero Section */}
            <section className="bg-gradient-to-br from-blue-600 to-indigo-700 py-20">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h1 className="text-4xl md:text-5xl font-bold text-white mb-6">
                        Complete Security Management Platform
                    </h1>
                    <p className="text-xl text-blue-100 max-w-3xl mx-auto">
                        Discover all the powerful features that make SecureServe the most comprehensive 
                        security agency management solution
                    </p>
                </div>
            </section>

            {/* Feature Tabs */}
            <section className="py-20">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {/* Tab Navigation */}
                    <div className="mb-12">
                        <div className="flex flex-wrap justify-center gap-4 mb-8">
                            {Object.entries(features).map(([key, feature]) => (
                                <button
                                    key={key}
                                    onClick={() => setActiveTab(key)}
                                    className={`px-6 py-3 rounded-lg font-semibold transition-all ${
                                        activeTab === key
                                            ? 'bg-blue-600 text-white shadow-lg'
                                            : 'bg-white text-gray-700 hover:bg-blue-50 border border-gray-200'
                                    }`}
                                >
                                    {feature.title.split(' ')[0]} {feature.title.split(' ')[1]}
                                </button>
                            ))}
                        </div>
                    </div>

                    {/* Active Tab Content */}
                    <div className="bg-white rounded-2xl shadow-xl overflow-hidden">
                        <div className="p-8 bg-gradient-to-r from-blue-600 to-indigo-600">
                            <h2 className="text-3xl font-bold text-white mb-4">
                                {features[activeTab].title}
                            </h2>
                            <p className="text-xl text-blue-100">
                                {features[activeTab].description}
                            </p>
                        </div>
                        
                        <div className="p-8">
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                                {features[activeTab].items.map((item, index) => (
                                    <div key={index} className="flex items-start space-x-4">
                                        <div className="text-3xl">{item.icon}</div>
                                        <div>
                                            <h3 className="text-lg font-semibold text-gray-900 mb-2">
                                                {item.title}
                                            </h3>
                                            <p className="text-gray-600">
                                                {item.description}
                                            </p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* Integration Section */}
            <section className="py-20 bg-white">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center mb-16">
                        <h2 className="text-3xl font-bold text-gray-900 mb-4">
                            Seamless Integrations
                        </h2>
                        <p className="text-xl text-gray-600 max-w-2xl mx-auto">
                            Connect with your existing tools and services for a complete business solution
                        </p>
                    </div>
                    
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-8">
                        <div className="text-center p-6 bg-gray-50 rounded-lg">
                            <div className="text-4xl mb-4">🏦</div>
                            <h3 className="font-semibold mb-2">Banking</h3>
                            <p className="text-sm text-gray-600">Direct bank transfers and reconciliation</p>
                        </div>
                        <div className="text-center p-6 bg-gray-50 rounded-lg">
                            <div className="text-4xl mb-4">💳</div>
                            <h3 className="font-semibold mb-2">Payment Gateways</h3>
                            <p className="text-sm text-gray-600">Razorpay, Stripe integration</p>
                        </div>
                        <div className="text-center p-6 bg-gray-50 rounded-lg">
                            <div className="text-4xl mb-4">📱</div>
                            <h3 className="font-semibold mb-2">Mobile Apps</h3>
                            <p className="text-sm text-gray-600">iOS and Android applications</p>
                        </div>
                        <div className="text-center p-6 bg-gray-50 rounded-lg">
                            <div className="text-4xl mb-4">🤖</div>
                            <h3 className="font-semibold mb-2">IoT Devices</h3>
                            <p className="text-sm text-gray-600">Kiosks and access control systems</p>
                        </div>
                    </div>
                </div>
            </section>

            {/* Benefits Section */}
            <section className="py-20 bg-gradient-to-br from-gray-900 to-gray-800">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center mb-16">
                        <h2 className="text-3xl font-bold text-white mb-4">
                            Why Choose SecureServe?
                        </h2>
                        <p className="text-xl text-gray-300">
                            The benefits that set us apart from the competition
                        </p>
                    </div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div className="text-center">
                            <div className="bg-blue-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg className="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <h3 className="text-xl font-bold text-white mb-4">90% Time Savings</h3>
                            <p className="text-gray-300">
                                Automate manual processes and reduce administrative overhead significantly
                            </p>
                        </div>
                        
                        <div className="text-center">
                            <div className="bg-green-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg className="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 className="text-xl font-bold text-white mb-4">100% Compliance</h3>
                            <p className="text-gray-300">
                                Stay compliant with all Indian labor laws, tax regulations, and statutory requirements
                            </p>
                        </div>
                        
                        <div className="text-center">
                            <div className="bg-purple-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg className="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                </svg>
                            </div>
                            <h3 className="text-xl font-bold text-white mb-4">Real-time Insights</h3>
                            <p className="text-gray-300">
                                Make data-driven decisions with comprehensive analytics and reporting
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {/* CTA Section */}
            <section className="py-20 bg-blue-600">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h2 className="text-3xl font-bold text-white mb-6">
                        Ready to Experience These Features?
                    </h2>
                    <p className="text-xl text-blue-100 mb-8">
                        Start your free trial and see how SecureServe can transform your security operations
                    </p>
                    <div className="flex flex-col sm:flex-row gap-4 justify-center">
                        <Link href="/register" className="bg-white text-blue-600 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-gray-100 transition-all transform hover:scale-105">
                            Start Free Trial
                        </Link>
                        <Link href="/demo" className="border-2 border-white text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-white hover:text-blue-600 transition-colors">
                            Watch Demo
                        </Link>
                    </div>
                </div>
            </section>
        </MarketingLayout>
    );
}