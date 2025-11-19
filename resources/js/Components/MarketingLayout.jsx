import React from 'react';
import { Head, Link } from '@inertiajs/react';

export default function MarketingLayout({ 
    children, 
    title = "SecureServe - Complete Security Management Platform",
    description = "Streamline your security operations with SecureServe. Comprehensive employee management, GPS attendance tracking, Indian payroll compliance, and visitor management system.",
    keywords = "security management, employee tracking, payroll software, visitor management, GPS attendance, security guards, India",
    canonical = null,
    ogImage = "/images/og-image.jpg",
    ogType = "website",
    twitterCard = "summary_large_image",
    jsonLd = null
}) {
    const fullTitle = title.includes('SecureServe') ? title : `${title} | SecureServe`;
    const currentUrl = canonical || (typeof window !== 'undefined' ? window.location.href : 'https://secureserve.com');

    const defaultJsonLd = {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "SecureServe",
        "description": description,
        "url": "https://secureserve.com",
        "applicationCategory": "SecurityApplication",
        "operatingSystem": "Web, iOS, Android",
        "offers": {
            "@type": "Offer",
            "price": "2999",
            "priceCurrency": "INR",
            "priceValidUntil": "2026-12-31"
        },
        "provider": {
            "@type": "Organization",
            "name": "SecureServe",
            "url": "https://secureserve.com"
        },
        "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "4.8",
            "ratingCount": "150"
        }
    };

    return (
        <>
            <Head>
                <title>{fullTitle}</title>
                <meta name="description" content={description} />
                <meta name="keywords" content={keywords} />
                <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                <meta name="robots" content="index, follow" />
                <meta name="author" content="SecureServe" />
                <meta name="language" content="en" />
                <meta name="geo.region" content="IN" />
                <meta name="geo.country" content="India" />
                
                {/* Canonical URL */}
                <link rel="canonical" href={currentUrl} />
                
                {/* Open Graph */}
                <meta property="og:title" content={fullTitle} />
                <meta property="og:description" content={description} />
                <meta property="og:type" content={ogType} />
                <meta property="og:url" content={currentUrl} />
                <meta property="og:image" content={ogImage} />
                <meta property="og:image:width" content="1200" />
                <meta property="og:image:height" content="630" />
                <meta property="og:site_name" content="SecureServe" />
                <meta property="og:locale" content="en_IN" />
                
                {/* Twitter Card */}
                <meta name="twitter:card" content={twitterCard} />
                <meta name="twitter:title" content={fullTitle} />
                <meta name="twitter:description" content={description} />
                <meta name="twitter:image" content={ogImage} />
                <meta name="twitter:site" content="@secureserve" />
                <meta name="twitter:creator" content="@secureserve" />
                
                {/* Favicon */}
                <link rel="icon" type="image/x-icon" href="/favicon.ico" />
                <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
                <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png" />
                <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png" />
                <link rel="manifest" href="/site.webmanifest" />
                
                {/* Preconnect for performance */}
                <link rel="preconnect" href="https://fonts.googleapis.com" />
                <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="" />
                
                {/* JSON-LD Structured Data */}
                <script type="application/ld+json">
                    {JSON.stringify(jsonLd || defaultJsonLd)}
                </script>
                
                {/* Additional meta tags for Indian market */}
                <meta name="msapplication-TileColor" content="#2563eb" />
                <meta name="theme-color" content="#2563eb" />
            </Head>

            <div className="min-h-screen bg-white">
                {/* Navigation */}
                <nav className="bg-white shadow-sm border-b sticky top-0 z-50" role="navigation" aria-label="Main navigation">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="flex justify-between h-16">
                            <div className="flex items-center">
                                <Link href="/" className="flex items-center" aria-label="SecureServe Home">
                                    <div className="h-8 w-8 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
                                        <span className="text-white font-bold text-sm" aria-hidden="true">SS</span>
                                    </div>
                                    <span className="ml-2 text-xl font-bold text-gray-900">SecureServe</span>
                                </Link>
                            </div>
                            
                            {/* Desktop Navigation */}
                            <div className="hidden md:flex items-center space-x-8">
                                <Link href="/" className="text-gray-600 hover:text-blue-600 transition-colors">Home</Link>
                                <Link href="/features" className="text-gray-600 hover:text-blue-600 transition-colors">Features</Link>
                                <Link href="/pricing" className="text-gray-600 hover:text-blue-600 transition-colors">Pricing</Link>
                                <Link href="/careers" className="text-gray-600 hover:text-blue-600 transition-colors">Careers</Link>
                                <Link href="/demo" className="text-gray-600 hover:text-blue-600 transition-colors">Demo</Link>
                                <Link href="/admin" className="text-gray-600 hover:text-blue-600 transition-colors">Login</Link>
                                <Link href="/register" className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                    Start Free Trial
                                </Link>
                            </div>

                            {/* Mobile menu button */}
                            <div className="md:hidden flex items-center">
                                <button 
                                    type="button" 
                                    className="text-gray-600 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 p-2"
                                    aria-label="Toggle mobile menu"
                                    onClick={() => {
                                        const menu = document.getElementById('mobile-menu');
                                        menu.classList.toggle('hidden');
                                    }}
                                >
                                    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    {/* Mobile Navigation */}
                    <div id="mobile-menu" className="hidden md:hidden bg-white border-t">
                        <div className="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                            <Link href="/" className="block px-3 py-2 text-gray-600 hover:text-blue-600 transition-colors">Home</Link>
                            <Link href="/features" className="block px-3 py-2 text-gray-600 hover:text-blue-600 transition-colors">Features</Link>
                            <Link href="/pricing" className="block px-3 py-2 text-gray-600 hover:text-blue-600 transition-colors">Pricing</Link>
                            <Link href="/careers" className="block px-3 py-2 text-gray-600 hover:text-blue-600 transition-colors">Careers</Link>
                            <Link href="/demo" className="block px-3 py-2 text-gray-600 hover:text-blue-600 transition-colors">Demo</Link>
                            <Link href="/admin" className="block px-3 py-2 text-gray-600 hover:text-blue-600 transition-colors">Login</Link>
                            <Link href="/register" className="block mx-3 mt-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-center">
                                Start Free Trial
                            </Link>
                        </div>
                    </div>
                </nav>

                {/* Main Content */}
                <main role="main">
                    {children}
                </main>

                {/* Footer */}
                <footer className="bg-gray-900 text-white" role="contentinfo">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                            {/* Company Info */}
                            <div className="lg:col-span-1">
                                <div className="flex items-center mb-4">
                                    <div className="h-8 w-8 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
                                        <span className="text-white font-bold text-sm">SS</span>
                                    </div>
                                    <span className="ml-2 text-xl font-bold">SecureServe</span>
                                </div>
                                <p className="text-gray-300 mb-4">
                                    Complete security management platform for modern businesses in India.
                                </p>
                                <div className="flex space-x-4">
                                    <a href="#" aria-label="Facebook" className="text-gray-400 hover:text-white transition-colors">
                                        <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fillRule="evenodd" d="M20 10C20 4.477 15.523 0 10 0S0 4.477 0 10c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V10h2.54V7.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V10h2.773l-.443 2.89h-2.33v6.988C16.343 19.128 20 14.991 20 10z" clipRule="evenodd" />
                                        </svg>
                                    </a>
                                    <a href="#" aria-label="Twitter" className="text-gray-400 hover:text-white transition-colors">
                                        <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M6.29 18.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0020 3.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.073 4.073 0 01.8 7.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 010 16.407a11.616 11.616 0 006.29 1.84" />
                                        </svg>
                                    </a>
                                    <a href="#" aria-label="LinkedIn" className="text-gray-400 hover:text-white transition-colors">
                                        <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fillRule="evenodd" d="M16.338 16.338H13.67V12.16c0-.995-.017-2.277-1.387-2.277-1.39 0-1.601 1.086-1.601 2.207v4.248H8.014v-8.59h2.559v1.174h.037c.356-.675 1.227-1.387 2.526-1.387 2.703 0 3.203 1.778 3.203 4.092v4.711zM5.005 6.575a1.548 1.548 0 11-.003-3.096 1.548 1.548 0 01.003 3.096zm-1.337 9.763H6.34v-8.59H3.667v8.59zM17.668 1H2.328C1.595 1 1 1.581 1 2.298v15.403C1 18.418 1.595 19 2.328 19h15.34c.734 0 1.332-.582 1.332-1.299V2.298C19 1.581 18.402 1 17.668 1z" clipRule="evenodd" />
                                        </svg>
                                    </a>
                                </div>
                            </div>

                            {/* Product */}
                            <div>
                                <h3 className="text-lg font-semibold mb-4">Product</h3>
                                <ul className="space-y-2">
                                    <li><Link href="/features" className="text-gray-300 hover:text-white transition-colors">Features</Link></li>
                                    <li><Link href="/pricing" className="text-gray-300 hover:text-white transition-colors">Pricing</Link></li>
                                    <li><Link href="/demo" className="text-gray-300 hover:text-white transition-colors">Demo</Link></li>
                                    <li><a href="#" className="text-gray-300 hover:text-white transition-colors">API</a></li>
                                </ul>
                            </div>

                            {/* Company */}
                            <div>
                                <h3 className="text-lg font-semibold mb-4">Company</h3>
                                <ul className="space-y-2">
                                    <li><Link href="/about-us" className="text-gray-300 hover:text-white transition-colors">About Us</Link></li>
                                    <li><Link href="/careers" className="text-gray-300 hover:text-white transition-colors">Careers</Link></li>
                                    <li><a href="#" className="text-gray-300 hover:text-white transition-colors">Blog</a></li>
                                    <li><a href="#" className="text-gray-300 hover:text-white transition-colors">Press</a></li>
                                </ul>
                            </div>

                            {/* Support */}
                            <div>
                                <h3 className="text-lg font-semibold mb-4">Support</h3>
                                <ul className="space-y-2">
                                    <li><Link href="/help-center" className="text-gray-300 hover:text-white transition-colors">Help Center</Link></li>
                                    <li><Link href="/documentation" className="text-gray-300 hover:text-white transition-colors">Documentation</Link></li>
                                    <li><a href="mailto:support@secureserve.com" className="text-gray-300 hover:text-white transition-colors">Contact Support</a></li>
                                    <li><Link href="/privacy-policy" className="text-gray-300 hover:text-white transition-colors">Privacy Policy</Link></li>
                                    <li><Link href="/terms-of-service" className="text-gray-300 hover:text-white transition-colors">Terms of Service</Link></li>
                                </ul>
                            </div>
                        </div>

                        <hr className="my-8 border-gray-700" />
                        
                        <div className="flex flex-col md:flex-row justify-between items-center">
                            <p className="text-gray-300 text-sm">
                                © 2025 SecureServe. All rights reserved.
                            </p>
                            <div className="flex space-x-6 mt-4 md:mt-0">
                                <span className="text-gray-400 text-sm">Made in India 🇮🇳</span>
                                <span className="text-gray-400 text-sm">GST Compliant</span>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}