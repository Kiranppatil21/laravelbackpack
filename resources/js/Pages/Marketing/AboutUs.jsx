import React from 'react';
import { Head } from '@inertiajs/react';
import MarketingLayout from '@/Components/MarketingLayout.jsx';

export default function AboutUs() {
    const jsonLd = {
        "@context": "https://schema.org",
        "@type": "AboutPage",
        "name": "About SecureServe - Leading Security Management Platform",
        "description": "Learn about SecureServe's mission to revolutionize security workforce management in India with innovative technology solutions.",
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
                    "name": "About Us",
                    "item": "https://secureserve.com/about-us"
                }
            ]
        }
    };

    const teamMembers = [
        {
            name: "Rajesh Sharma",
            position: "Founder & CEO",
            image: "👨‍💼",
            description: "15+ years in security industry transformation"
        },
        {
            name: "Priya Patel",
            position: "CTO",
            image: "👩‍💻",
            description: "Expert in scalable enterprise solutions"
        },
        {
            name: "Amit Kumar",
            position: "Head of Operations",
            image: "👨‍💼",
            description: "Specialist in security workforce management"
        }
    ];

    const values = [
        {
            icon: "🎯",
            title: "Innovation First",
            description: "We leverage cutting-edge technology to solve real-world security management challenges."
        },
        {
            icon: "🤝",
            title: "Trust & Reliability", 
            description: "Building long-term relationships through transparent and dependable services."
        },
        {
            icon: "🇮🇳",
            title: "Made for India",
            description: "Designed specifically for Indian regulations, compliance, and business practices."
        },
        {
            icon: "🚀",
            title: "Continuous Growth",
            description: "Constantly evolving our platform to meet changing security industry needs."
        }
    ];

    const milestones = [
        {
            year: "2020",
            title: "Company Founded",
            description: "Started with a vision to digitize security management in India"
        },
        {
            year: "2021", 
            title: "First 100 Clients",
            description: "Reached our first major milestone of 100+ satisfied security agencies"
        },
        {
            year: "2023",
            title: "GST Compliance",
            description: "Achieved full GST compliance and statutory reporting capabilities"
        },
        {
            year: "2024",
            title: "AI Integration",
            description: "Launched AI-powered attendance and workforce analytics features"
        }
    ];

    return (
        <MarketingLayout>
            <Head>
                <title>About Us - SecureServe Security Management Platform</title>
                <meta name="description" content="Learn about SecureServe's mission to revolutionize security workforce management in India with innovative technology solutions for agencies and enterprises." />
                <meta name="keywords" content="about SecureServe, security management company, Indian security technology, workforce management" />
                
                {/* Open Graph */}
                <meta property="og:title" content="About SecureServe - Leading Security Management Platform" />
                <meta property="og:description" content="Discover how SecureServe is transforming security workforce management in India with cutting-edge technology solutions." />
                <meta property="og:type" content="website" />
                <meta property="og:url" content="https://secureserve.com/about-us" />
                
                {/* Twitter Card */}
                <meta name="twitter:card" content="summary_large_image" />
                <meta name="twitter:title" content="About SecureServe - Security Management Innovation" />
                <meta name="twitter:description" content="Learn about our mission to revolutionize security workforce management in India." />
                
                {/* JSON-LD */}
                <script type="application/ld+json">
                    {JSON.stringify(jsonLd)}
                </script>
            </Head>

            {/* Hero Section */}
            <section className="bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 text-white py-20">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center">
                        <h1 className="text-4xl md:text-6xl font-bold mb-6">
                            About <span className="text-blue-300">SecureServe</span>
                        </h1>
                        <p className="text-xl md:text-2xl text-blue-100 mb-8 max-w-3xl mx-auto">
                            Transforming India's security industry through innovative workforce management technology
                        </p>
                        <div className="flex justify-center items-center space-x-8 text-blue-200">
                            <div className="text-center">
                                <div className="text-3xl font-bold">1000+</div>
                                <div className="text-sm">Agencies</div>
                            </div>
                            <div className="text-center">
                                <div className="text-3xl font-bold">50K+</div>
                                <div className="text-sm">Guards Managed</div>
                            </div>
                            <div className="text-center">
                                <div className="text-3xl font-bold">99.9%</div>
                                <div className="text-sm">Uptime</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* Mission & Vision Section */}
            <section className="py-20 bg-white">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                        <div>
                            <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                                Our Mission
                            </h2>
                            <p className="text-lg text-gray-700 mb-6 leading-relaxed">
                                To revolutionize India's security industry by providing comprehensive, 
                                technology-driven workforce management solutions that enhance operational 
                                efficiency, ensure compliance, and improve the working conditions of security personnel.
                            </p>
                            <p className="text-lg text-gray-700 leading-relaxed">
                                We believe that every security guard deserves proper management, fair compensation, 
                                and professional recognition. Through SecureServe, we're making this vision a reality.
                            </p>
                        </div>
                        <div className="bg-gradient-to-br from-blue-50 to-indigo-100 p-8 rounded-xl">
                            <h3 className="text-2xl font-bold text-gray-900 mb-4">Our Vision</h3>
                            <p className="text-gray-700 leading-relaxed">
                                To become India's leading platform for security workforce management, 
                                setting new standards for technology adoption, compliance management, 
                                and employee welfare in the security industry.
                            </p>
                            <div className="mt-6 flex items-center space-x-4">
                                <span className="text-4xl">🎯</span>
                                <div>
                                    <div className="font-semibold text-gray-900">Goal 2025</div>
                                    <div className="text-gray-600">Manage 100,000+ security personnel across India</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* Values Section */}
            <section className="py-20 bg-gray-50">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center mb-16">
                        <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                            Our Core Values
                        </h2>
                        <p className="text-xl text-gray-600 max-w-2xl mx-auto">
                            The principles that guide everything we do at SecureServe
                        </p>
                    </div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                        {values.map((value, index) => (
                            <div key={index} className="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow">
                                <div className="text-4xl mb-4">{value.icon}</div>
                                <h3 className="text-xl font-bold text-gray-900 mb-3">{value.title}</h3>
                                <p className="text-gray-600 leading-relaxed">{value.description}</p>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Story/Journey Section */}
            <section className="py-20 bg-white">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center mb-16">
                        <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                            Our Journey
                        </h2>
                        <p className="text-xl text-gray-600 max-w-2xl mx-auto">
                            From a vision to reality - building India's premier security management platform
                        </p>
                    </div>
                    
                    <div className="relative">
                        {/* Timeline line */}
                        <div className="absolute left-1/2 transform -translate-x-px h-full w-0.5 bg-blue-200"></div>
                        
                        <div className="space-y-12">
                            {milestones.map((milestone, index) => (
                                <div key={index} className={`relative flex items-center ${index % 2 === 0 ? 'justify-start' : 'justify-end'}`}>
                                    {/* Timeline dot */}
                                    <div className="absolute left-1/2 transform -translate-x-1/2 w-4 h-4 bg-blue-600 rounded-full border-4 border-white shadow-lg"></div>
                                    
                                    <div className={`w-5/12 ${index % 2 === 0 ? 'pr-8 text-right' : 'pl-8 text-left'}`}>
                                        <div className="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
                                            <div className="text-2xl font-bold text-blue-600 mb-2">{milestone.year}</div>
                                            <h3 className="text-xl font-bold text-gray-900 mb-3">{milestone.title}</h3>
                                            <p className="text-gray-600">{milestone.description}</p>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </section>

            {/* Team Section */}
            <section className="py-20 bg-gray-50">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center mb-16">
                        <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                            Leadership Team
                        </h2>
                        <p className="text-xl text-gray-600 max-w-2xl mx-auto">
                            Meet the experts driving innovation in security workforce management
                        </p>
                    </div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                        {teamMembers.map((member, index) => (
                            <div key={index} className="bg-white p-6 rounded-xl shadow-lg text-center hover:shadow-xl transition-shadow">
                                <div className="text-6xl mb-4">{member.image}</div>
                                <h3 className="text-xl font-bold text-gray-900 mb-2">{member.name}</h3>
                                <div className="text-blue-600 font-semibold mb-3">{member.position}</div>
                                <p className="text-gray-600">{member.description}</p>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Technology & Innovation Section */}
            <section className="py-20 bg-white">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                        <div>
                            <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                                Built for India's Future
                            </h2>
                            <p className="text-lg text-gray-700 mb-6 leading-relaxed">
                                SecureServe is designed from the ground up to address the unique challenges 
                                of India's security industry. From complex statutory compliance to diverse 
                                linguistic requirements, we understand what it takes to succeed in this market.
                            </p>
                            <div className="space-y-4">
                                <div className="flex items-center space-x-3">
                                    <span className="text-green-600 text-xl">✓</span>
                                    <span className="text-gray-700">Full GST & Labour Law Compliance</span>
                                </div>
                                <div className="flex items-center space-x-3">
                                    <span className="text-green-600 text-xl">✓</span>
                                    <span className="text-gray-700">Multi-language Support (Hindi, English, Regional)</span>
                                </div>
                                <div className="flex items-center space-x-3">
                                    <span className="text-green-600 text-xl">✓</span>
                                    <span className="text-gray-700">Mobile-first Design for Field Operations</span>
                                </div>
                                <div className="flex items-center space-x-3">
                                    <span className="text-green-600 text-xl">✓</span>
                                    <span className="text-gray-700">AI-powered Analytics & Insights</span>
                                </div>
                            </div>
                        </div>
                        <div className="bg-gradient-to-br from-blue-900 to-indigo-900 text-white p-8 rounded-xl">
                            <h3 className="text-2xl font-bold mb-6">Why Choose SecureServe?</h3>
                            <div className="space-y-4">
                                <div className="flex items-start space-x-3">
                                    <span className="text-blue-300 text-xl">🏆</span>
                                    <div>
                                        <div className="font-semibold">Industry Expertise</div>
                                        <div className="text-blue-200 text-sm">Deep understanding of security operations</div>
                                    </div>
                                </div>
                                <div className="flex items-start space-x-3">
                                    <span className="text-blue-300 text-xl">🔒</span>
                                    <div>
                                        <div className="font-semibold">Data Security</div>
                                        <div className="text-blue-200 text-sm">Bank-grade security for all your data</div>
                                    </div>
                                </div>
                                <div className="flex items-start space-x-3">
                                    <span className="text-blue-300 text-xl">📱</span>
                                    <div>
                                        <div className="font-semibold">Modern Technology</div>
                                        <div className="text-blue-200 text-sm">Cloud-based, scalable, and reliable</div>
                                    </div>
                                </div>
                                <div className="flex items-start space-x-3">
                                    <span className="text-blue-300 text-xl">🤝</span>
                                    <div>
                                        <div className="font-semibold">24/7 Support</div>
                                        <div className="text-blue-200 text-sm">Always here when you need us</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* CTA Section */}
            <section className="py-20 bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
                <div className="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
                    <h2 className="text-3xl md:text-4xl font-bold mb-6">
                        Ready to Transform Your Security Operations?
                    </h2>
                    <p className="text-xl text-blue-100 mb-8">
                        Join thousands of security professionals who trust SecureServe for their workforce management needs.
                    </p>
                    <div className="space-y-4 sm:space-y-0 sm:space-x-4 sm:flex sm:justify-center">
                        <a
                            href="/demo"
                            className="inline-block bg-white text-blue-600 font-semibold px-8 py-3 rounded-lg hover:bg-gray-100 transition-colors shadow-lg"
                        >
                            Schedule Demo
                        </a>
                        <a
                            href="/pricing"
                            className="inline-block border-2 border-white text-white font-semibold px-8 py-3 rounded-lg hover:bg-white hover:text-blue-600 transition-colors"
                        >
                            View Pricing
                        </a>
                    </div>
                </div>
            </section>
        </MarketingLayout>
    );
}