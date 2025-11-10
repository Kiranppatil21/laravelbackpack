import React, { useState } from 'react';
import { Link } from '@inertiajs/react';
import MarketingLayout from '@/Components/MarketingLayout.jsx';

export default function HelpCenter() {
    const [searchQuery, setSearchQuery] = useState('');
    const [selectedCategory, setSelectedCategory] = useState('all');

    const categories = [
        { id: 'all', name: 'All Topics', count: 28 },
        { id: 'getting-started', name: 'Getting Started', count: 8 },
        { id: 'employee-management', name: 'Employee Management', count: 6 },
        { id: 'attendance', name: 'Attendance & GPS', count: 5 },
        { id: 'payroll', name: 'Payroll & Compliance', count: 4 },
        { id: 'visitor-management', name: 'Visitor Management', count: 3 },
        { id: 'billing', name: 'Billing & Payments', count: 2 }
    ];

    const faqs = [
        {
            category: 'getting-started',
            question: 'How do I set up my security agency on SecureServe?',
            answer: 'Setting up your agency is simple! After registration, you\'ll be guided through a 5-step onboarding process: 1) Complete company profile, 2) Add agency locations, 3) Create employee profiles, 4) Set up client accounts, and 5) Configure attendance zones. Our setup wizard makes this process seamless.'
        },
        {
            category: 'getting-started',
            question: 'What documents do I need to get started?',
            answer: 'You\'ll need: Agency license, PAN card, GST registration certificate, bank account details, and employee documents (Aadhaar, PAN, bank details). All documents can be uploaded securely through our platform.'
        },
        {
            category: 'employee-management',
            question: 'How do I add new security guards to the system?',
            answer: 'Navigate to Employees > Add New. Fill in personal details, upload photos and documents, assign to locations, set salary details, and configure permissions. Guards will receive login credentials via SMS.'
        },
        {
            category: 'attendance',
            question: 'How does GPS attendance tracking work?',
            answer: 'Guards check in/out using the mobile app when they arrive at assigned locations. GPS coordinates are verified against predefined zones (50-meter radius). Real-time notifications are sent to managers for attendance events.'
        },
        {
            category: 'payroll',
            question: 'Is the payroll system compliant with Indian labor laws?',
            answer: 'Yes! Our payroll automatically calculates PF (12% employee + 12% employer), ESIC (0.75% employee + 3.25% employer), professional tax, and TDS as per current Indian regulations. All statutory reports can be generated with one click.'
        },
        {
            category: 'visitor-management',
            question: 'How does the visitor management system work?',
            answer: 'Visitors register at reception using QR codes or tablet interface. Photo capture, ID verification, and host notification happen automatically. Visitor badges are printed instantly, and all data is stored for security analytics.'
        },
        {
            category: 'billing',
            question: 'What payment methods do you accept?',
            answer: 'We accept all major payment methods: Credit/debit cards, UPI, net banking, and NEFT/RTGS. Payments are processed securely through Razorpay with 256-bit SSL encryption.'
        },
        {
            category: 'getting-started',
            question: 'Is there a mobile app for security guards?',
            answer: 'Yes! The SecureServe mobile app is available for both Android and iOS. Guards can check attendance, view schedules, report incidents, and communicate with managers. Download links are provided during onboarding.'
        }
    ];

    const filteredFaqs = selectedCategory === 'all' 
        ? faqs 
        : faqs.filter(faq => faq.category === selectedCategory);

    const searchFilteredFaqs = filteredFaqs.filter(faq => 
        faq.question.toLowerCase().includes(searchQuery.toLowerCase()) ||
        faq.answer.toLowerCase().includes(searchQuery.toLowerCase())
    );

    const jsonLd = {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": faqs.map(faq => ({
            "@type": "Question",
            "name": faq.question,
            "acceptedAnswer": {
                "@type": "Answer",
                "text": faq.answer
            }
        }))
    };

    return (
        <MarketingLayout 
            title="Help Center - Get Support for SecureServe | SecureServe"
            description="Find answers to frequently asked questions about SecureServe security management platform. Get help with setup, employee management, attendance tracking, and more."
            keywords="SecureServe help, security software support, FAQ, employee management help, attendance tracking support, payroll help"
            jsonLd={jsonLd}
        >
            {/* Hero Section */}
            <section className="bg-gradient-to-br from-blue-600 to-indigo-700 py-16">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h1 className="text-4xl md:text-5xl font-bold text-white mb-6">
                        How can we help you?
                    </h1>
                    <p className="text-xl text-blue-100 mb-8">
                        Find answers to common questions or get in touch with our support team
                    </p>
                    
                    {/* Search Bar */}
                    <div className="relative max-w-2xl mx-auto">
                        <input
                            type="text"
                            placeholder="Search for help articles..."
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                            className="w-full px-6 py-4 pr-12 text-lg rounded-xl border-0 focus:ring-2 focus:ring-blue-300"
                        />
                        <div className="absolute inset-y-0 right-0 flex items-center pr-4">
                            <svg className="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </section>

            {/* Quick Actions */}
            <section className="py-16 bg-gray-50">
                <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="grid md:grid-cols-3 gap-8">
                        <div className="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow text-center">
                            <div className="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg className="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <h3 className="text-xl font-semibold mb-2">Documentation</h3>
                            <p className="text-gray-600 mb-4">Comprehensive guides and tutorials</p>
                            <Link href="/documentation" className="text-blue-600 hover:text-blue-700 font-medium">
                                Browse Docs →
                            </Link>
                        </div>

                        <div className="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow text-center">
                            <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg className="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                            </div>
                            <h3 className="text-xl font-semibold mb-2">Contact Support</h3>
                            <p className="text-gray-600 mb-4">Get help from our expert team</p>
                            <a href="mailto:support@secureserve.com" className="text-green-600 hover:text-green-700 font-medium">
                                Email Support →
                            </a>
                        </div>

                        <div className="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow text-center">
                            <div className="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg className="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 className="text-xl font-semibold mb-2">Video Tutorials</h3>
                            <p className="text-gray-600 mb-4">Learn with step-by-step videos</p>
                            <a href="#" className="text-purple-600 hover:text-purple-700 font-medium">
                                Watch Videos →
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            {/* FAQ Section */}
            <section className="py-16">
                <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex flex-col lg:flex-row gap-8">
                        {/* Categories Sidebar */}
                        <div className="lg:w-1/4">
                            <h3 className="text-lg font-semibold mb-4">Categories</h3>
                            <div className="space-y-2">
                                {categories.map((category) => (
                                    <button
                                        key={category.id}
                                        onClick={() => setSelectedCategory(category.id)}
                                        className={`w-full text-left px-4 py-3 rounded-lg transition-colors ${
                                            selectedCategory === category.id
                                                ? 'bg-blue-600 text-white'
                                                : 'bg-gray-100 hover:bg-gray-200 text-gray-700'
                                        }`}
                                    >
                                        <div className="flex justify-between items-center">
                                            <span>{category.name}</span>
                                            <span className={`text-sm ${
                                                selectedCategory === category.id ? 'text-blue-200' : 'text-gray-500'
                                            }`}>
                                                {category.count}
                                            </span>
                                        </div>
                                    </button>
                                ))}
                            </div>
                        </div>

                        {/* FAQ Content */}
                        <div className="lg:w-3/4">
                            <h2 className="text-2xl font-bold mb-6">
                                Frequently Asked Questions
                                {selectedCategory !== 'all' && (
                                    <span className="text-blue-600 ml-2">
                                        - {categories.find(c => c.id === selectedCategory)?.name}
                                    </span>
                                )}
                            </h2>
                            
                            {searchFilteredFaqs.length === 0 ? (
                                <div className="text-center py-8">
                                    <p className="text-gray-500">No articles found matching your search.</p>
                                </div>
                            ) : (
                                <div className="space-y-6">
                                    {searchFilteredFaqs.map((faq, index) => (
                                        <div key={index} className="bg-white rounded-xl p-6 shadow-sm border">
                                            <h4 className="text-lg font-semibold mb-3 text-gray-900">
                                                {faq.question}
                                            </h4>
                                            <p className="text-gray-600 leading-relaxed">
                                                {faq.answer}
                                            </p>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </section>

            {/* Contact Section */}
            <section className="bg-blue-600 py-16">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h2 className="text-3xl font-bold text-white mb-4">
                        Still need help?
                    </h2>
                    <p className="text-blue-100 mb-8">
                        Our support team is here to help you succeed with SecureServe
                    </p>
                    <div className="flex flex-col sm:flex-row gap-4 justify-center">
                        <a 
                            href="mailto:support@secureserve.com"
                            className="bg-white text-blue-600 px-8 py-3 rounded-lg font-medium hover:bg-blue-50 transition-colors"
                        >
                            Email Support
                        </a>
                        <a 
                            href="tel:+91-8000-123-456"
                            className="border-2 border-white text-white px-8 py-3 rounded-lg font-medium hover:bg-white hover:text-blue-600 transition-colors"
                        >
                            Call Support
                        </a>
                    </div>
                </div>
            </section>
        </MarketingLayout>
    );
}